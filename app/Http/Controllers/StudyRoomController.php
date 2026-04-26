<?php

namespace App\Http\Controllers;

use App\Models\StudyRoom;
use App\Models\StudyRoomParticipant;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyRoomController extends Controller
{
    // -------------------------------------------------------------------------
    // Index: list active + archived rooms for the current user's sections
    // -------------------------------------------------------------------------
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $sections = $user->enrolledSections;
        } else {
            $sections = $user->sectionsTaught;
        }

        $sectionIds = $sections->pluck('id');

        $activeRooms = StudyRoom::whereIn('section_id', $sectionIds)
            ->where('is_active', true)
            ->with(['course', 'section', 'creator', 'activeParticipants.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $archivedRooms = StudyRoom::whereIn('section_id', $sectionIds)
            ->where('is_active', false)
            ->with(['course', 'section', 'creator'])
            ->orderBy('archived_at', 'desc')
            ->get();

        return view('academic.study-rooms.index', compact('activeRooms', 'archivedRooms', 'sections'));
    }

    // -------------------------------------------------------------------------
    // Create form
    // -------------------------------------------------------------------------
    public function create()
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $sections = $user->enrolledSections()->with('course')->get();
        } else {
            $sections = $user->sectionsTaught()->with('course')->get();
        }

        return view('academic.study-rooms.create', compact('sections'));
    }

    // -------------------------------------------------------------------------
    // Store new room
    // -------------------------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'section_id'  => 'required|exists:sections,id',
            'description' => 'nullable|string|max:500',
        ]);

        $section = Section::findOrFail($request->section_id);
        $user    = Auth::user();

        if ($user->role === 'student') {
            $isAuthorized = $user->enrolledSections()->where('section_id', $section->id)->exists();
        } else {
            $isAuthorized = $user->sectionsTaught()->where('id', $section->id)->exists();
        }

        if (!$isAuthorized) {
            abort(403);
        }

        $studyRoom = StudyRoom::create([
            'name'        => $request->name,
            'description' => $request->description,
            'course_id'   => $section->course_id,
            'section_id'  => $section->id,
            'created_by'  => $user->id,
        ]);

        return redirect()->route('study-rooms.show', $studyRoom);
    }

    // -------------------------------------------------------------------------
    // Show room — mark user as active participant
    // -------------------------------------------------------------------------
    public function show(StudyRoom $studyRoom)
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $isAuthorized = $user->enrolledSections()->where('section_id', $studyRoom->section_id)->exists();
        } else {
            $isAuthorized = $user->sectionsTaught()->where('id', $studyRoom->section_id)->exists();
        }

        if (!$isAuthorized) {
            abort(403);
        }

        // Mark user as (re-)joined — create a fresh active record if none exists
        StudyRoomParticipant::firstOrCreate(
            ['study_room_id' => $studyRoom->id, 'user_id' => $user->id, 'left_at' => null],
            ['joined_at' => now()]
        );

        $studyRoom->load(['course', 'section', 'creator', 'activeParticipants.user']);

        return view('academic.study-rooms.show', compact('studyRoom'));
    }

    // -------------------------------------------------------------------------
    // Leave room
    // -------------------------------------------------------------------------
    public function leave(StudyRoom $studyRoom)
    {
        $user = Auth::user();

        StudyRoomParticipant::where('study_room_id', $studyRoom->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        return redirect()->route('study-rooms.index');
    }

    // -------------------------------------------------------------------------
    // Archive room (creator only)
    // -------------------------------------------------------------------------
    public function archive(StudyRoom $studyRoom)
    {
        if ($studyRoom->created_by !== Auth::id()) {
            abort(403);
        }

        $studyRoom->update([
            'is_active'   => false,
            'archived_at' => now(),
        ]);

        StudyRoomParticipant::where('study_room_id', $studyRoom->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        return redirect()->route('study-rooms.index');
    }

    // -------------------------------------------------------------------------
    // API: Save collaborative notes
    // -------------------------------------------------------------------------
    public function updateNotes(Request $request, StudyRoom $studyRoom)
    {
        $this->authorizeRoomAccess($studyRoom);

        $request->validate(['notes' => 'present|nullable|string']);

        $studyRoom->update(['notes_data' => $request->notes]);

        return response()->json(['success' => true, 'updated_at' => now()->timestamp]);
    }

    // -------------------------------------------------------------------------
    // API: Save whiteboard (base64 image string)
    // -------------------------------------------------------------------------
    public function updateWhiteboard(Request $request, StudyRoom $studyRoom)
    {
        $this->authorizeRoomAccess($studyRoom);

        $request->validate(['whiteboard' => 'present|nullable|string']);

        $studyRoom->update(['whiteboard_data' => $request->whiteboard]);

        return response()->json(['success' => true, 'updated_at' => now()->timestamp]);
    }

    // -------------------------------------------------------------------------
    // API: Polling endpoint — returns latest room state
    // -------------------------------------------------------------------------
    public function getUpdates(StudyRoom $studyRoom)
    {
        $this->authorizeRoomAccess($studyRoom);

        // Heartbeat: touch the participant record so we know user is still online
        StudyRoomParticipant::where('study_room_id', $studyRoom->id)
            ->where('user_id', Auth::id())
            ->whereNull('left_at')
            ->update(['updated_at' => now()]);

        // Mark participants who haven't pinged in 15 seconds as left
        StudyRoomParticipant::where('study_room_id', $studyRoom->id)
            ->whereNull('left_at')
            ->where('updated_at', '<', now()->subSeconds(15))
            ->update(['left_at' => now()]);

        $studyRoom->refresh();
        $studyRoom->load('activeParticipants.user');

        $participants = $studyRoom->activeParticipants->map(fn($p) => [
            'id'        => $p->user->id,
            'name'      => $p->user->name,
            'initial'   => strtoupper(substr($p->user->name, 0, 1)),
            'joined_at' => $p->joined_at?->format('H:i'),
        ]);

        return response()->json([
            'notes'           => $studyRoom->notes_data ?? '',
            'whiteboard'      => $studyRoom->whiteboard_data ?? '',
            'chat'            => $studyRoom->chat_messages ?? [],
            'participants'    => $participants,
            'notes_updated'   => $studyRoom->updated_at->timestamp,
            'is_active'       => $studyRoom->is_active,
        ]);
    }

    // -------------------------------------------------------------------------
    // API: Send a chat message
    // -------------------------------------------------------------------------
    public function sendMessage(Request $request, StudyRoom $studyRoom)
    {
        $this->authorizeRoomAccess($studyRoom);

        $request->validate(['message' => 'required|string|max:1000']);

        $user     = Auth::user();
        $messages = $studyRoom->chat_messages ?? [];

        $messages[] = [
            'user_id'  => $user->id,
            'name'     => $user->name,
            'initial'  => strtoupper(substr($user->name, 0, 1)),
            'text'     => $request->message,
            'time'     => now()->format('H:i'),
            'ts'       => now()->timestamp,
        ];

        // Keep only the last 200 messages to avoid bloating the column
        if (count($messages) > 200) {
            $messages = array_slice($messages, -200);
        }

        $studyRoom->update(['chat_messages' => $messages]);

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // Helper: ensure current user is authorized to access the room
    // -------------------------------------------------------------------------
    private function authorizeRoomAccess(StudyRoom $studyRoom): void
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $ok = $user->enrolledSections()->where('section_id', $studyRoom->section_id)->exists();
        } else {
            $ok = $user->sectionsTaught()->where('id', $studyRoom->section_id)->exists();
        }

        if (!$ok) {
            abort(403);
        }
    }
}
