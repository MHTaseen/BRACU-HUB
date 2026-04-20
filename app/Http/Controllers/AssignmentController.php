<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Section;
use App\Services\ConflictDetectionService;
use App\Notifications\NewAssignmentNotification;
use App\Notifications\TeacherManualReminder;


class AssignmentController extends Controller
{
    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $facultyId = auth()->id();
        $sections = Section::where('faculty_id', $facultyId)->with('course')->get();
        // Fetch assignments deployed by this faculty
        $recentAssignments = Assignment::whereIn('section_id', $sections->pluck('id'))
            ->with('section.course', 'submissions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('academic.assignments.create', compact('sections', 'recentAssignments'));
    }

    /**
     * Check for conflicts before storing the assignment.
     * This is an AJAX endpoint for the frontend.
     */
    public function checkConflict(Request $request, ConflictDetectionService $conflictService)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'due_date' => 'required|date|after:now'
        ]);

        // Security check
        Section::where('id', $request->section_id)->where('faculty_id', auth()->id())->firstOrFail();

        $result = $conflictService->detectConflicts($request->section_id, $request->due_date);

        return response()->json($result);
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'type' => 'required|in:Assignment,Quiz,Midterm,Final',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'max_marks' => 'required|numeric|min:1|max:1000',
            'due_date' => 'required|date|after:now',
        ], [
            'due_date.after' => 'The assignment due date must be in the future.',
        ]);

        // Security check
        $section = Section::where('id', $validated['section_id'])->where('faculty_id', auth()->id())->firstOrFail();

        $assignment = Assignment::create($validated);

        foreach ($section->students as $student) {
            $student->notify(new NewAssignmentNotification($assignment));
        }

        return redirect()->route('assignments.create')
                         ->with('success', 'Assignment created successfully.');
    }

    /**
     * Teacher manually sends a reminder to all students for a specific assignment.
     * Can be called as many times as the teacher wants.
     */
    public function sendManualReminder(Request $request, Assignment $assignment)
    {
        // Security: only the teacher who owns this assignment's section may send
        Section::where('id', $assignment->section_id)
               ->where('faculty_id', auth()->id())
               ->firstOrFail();

        $customMessage = $request->input('message', '');
        $teacher       = auth()->user();
        $count         = 0;

        foreach ($assignment->section->students as $student) {
            $student->notify(new TeacherManualReminder($assignment, $teacher, $customMessage));
            $count++;
        }

        return response()->json([
            'success' => true,
            'count'   => $count,
            'message' => "✅ Reminder sent to {$count} student(s) successfully!",
        ]);
    }
}
