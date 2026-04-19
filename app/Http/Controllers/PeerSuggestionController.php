<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\StudyRoomParticipant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeerSuggestionController extends Controller
{
    // -------------------------------------------------------------------------
    // Tuning constants
    // -------------------------------------------------------------------------
    private const MAX_SHARED_SCORE    = 40;
    private const MAX_WORKLOAD_SCORE  = 30;
    private const MAX_ATT_SCORE       = 15;
    private const MAX_SUB_SCORE       = 15;
    private const STUDY_ROOM_BONUS    = 5;
    private const MIN_SCORE_THRESHOLD = 35;   // candidates below this are hidden

    // -------------------------------------------------------------------------
    // Entry point
    // -------------------------------------------------------------------------
    public function index()
    {
        $me = Auth::user();

        // 1. My enrolled section IDs
        $mySectionIds = $me->enrolledSections()->pluck('sections.id')->toArray();

        if (empty($mySectionIds)) {
            return view('academic.peer-suggestions.index', [
                'peers'      => collect(),
                'mysections'  => collect(),
                'filterSection' => null,
            ]);
        }

        // 2. My sections with course info (for the filter bar)
        $mySections = $me->enrolledSections()->with('course')->get();

        // 3. Compute my own signals once
        $myPending        = $this->pendingAssignments($mySectionIds);
        $myAttendance     = $this->attendancePercent($me->id, $mySectionIds);
        $mySubmissionRate = $this->submissionRate($me->id, $mySectionIds);
        $myRoomIds        = $this->roomIds($me->id);

        // 4. Find candidate peers: students sharing at least 1 section with me
        $candidateIds = Enrollment::whereIn('section_id', $mySectionIds)
            ->where('student_id', '!=', $me->id)
            ->pluck('student_id')
            ->unique()
            ->values();

        if ($candidateIds->isEmpty()) {
            return view('academic.peer-suggestions.index', [
                'peers'         => collect(),
                'mySections'    => $mySections,
                'filterSection' => null,
            ]);
        }

        // Eager-load each candidate with their enrollments + section + course
        $candidates = User::whereIn('id', $candidateIds)
            ->with(['enrolledSections.course'])
            ->get();

        // 5. Score every candidate
        $scored = [];

        foreach ($candidates as $peer) {
            $peerSectionIds = $peer->enrolledSections->pluck('id')->toArray();

            // ── Signal 1: Shared sections ────────────────────────────────────
            $sharedSections = array_intersect($mySectionIds, $peerSectionIds);
            $sharedCount    = count($sharedSections);

            if ($sharedCount === 0) continue;  // safety guard

            $sharedScore = min(
                self::MAX_SHARED_SCORE,
                ($sharedCount / max(1, count($mySectionIds))) * self::MAX_SHARED_SCORE
            );

            // ── Signal 2: Workload similarity ────────────────────────────────
            $peerPending   = $this->pendingAssignments($peerSectionIds);
            $workloadDiff  = abs($myPending - $peerPending);
            $workloadScore = max(0, self::MAX_WORKLOAD_SCORE - ($workloadDiff * 5));

            // ── Signal 3a: Attendance similarity ────────────────────────────
            $peerAttendance = $this->attendancePercent($peer->id, $peerSectionIds);
            $attDiff        = abs($myAttendance - $peerAttendance);
            $attScore       = max(0, self::MAX_ATT_SCORE - ($attDiff * 0.15));

            // ── Signal 3b: Submission rate similarity ────────────────────────
            $peerSubRate = $this->submissionRate($peer->id, $peerSectionIds);
            $subDiff     = abs($mySubmissionRate - $peerSubRate);
            $subScore    = max(0, self::MAX_SUB_SCORE - ($subDiff * self::MAX_SUB_SCORE));

            // ── Bonus: Study room co-presence ────────────────────────────────
            $peerRoomIds  = $this->roomIds($peer->id);
            $sharedRooms  = array_intersect($myRoomIds, $peerRoomIds);
            $roomBonus    = count($sharedRooms) > 0 ? self::STUDY_ROOM_BONUS : 0;

            // ── Total ────────────────────────────────────────────────────────
            $total = min(100, round($sharedScore + $workloadScore + $attScore + $subScore + $roomBonus));

            if ($total < self::MIN_SCORE_THRESHOLD) continue;

            // ── Shared section details for display ───────────────────────────
            $sharedSectionDetails = $peer->enrolledSections
                ->whereIn('id', $sharedSections)
                ->map(fn($s) => [
                    'label'    => $s->course->code . ' — Sec ' . $s->section_number,
                    'schedule' => $s->schedule,
                ]);

            // ── Match label ──────────────────────────────────────────────────
            [$matchLabel, $matchIcon, $matchColor] = $this->matchLabel($total);

            $scored[] = [
                'id'             => $peer->id,
                'name'           => $peer->name,
                'initial'        => strtoupper(substr($peer->name, 0, 1)),
                'total'          => $total,
                'match_label'    => $matchLabel,
                'match_icon'     => $matchIcon,
                'match_color'    => $matchColor,
                'shared_count'   => $sharedCount,
                'shared_rooms'   => count($sharedRooms),
                'breakdown'      => [
                    'shared'    => round($sharedScore),
                    'workload'  => round($workloadScore),
                    'att'       => round($attScore),
                    'sub'       => round($subScore),
                    'room'      => $roomBonus,
                ],
                'shared_sections' => $sharedSectionDetails->values(),
                // For "Invite" link: first shared section id
                'first_shared_section_id' => array_values($sharedSections)[0] ?? null,
            ];
        }

        // 6. Sort descending by score
        usort($scored, fn($a, $b) => $b['total'] <=> $a['total']);

        return view('academic.peer-suggestions.index', [
            'peers'         => collect($scored),
            'mySections'    => $mySections,
            'filterSection' => null,
        ]);
    }

    // =========================================================================
    // Helper: count pending assignments for a set of section IDs
    // =========================================================================
    private function pendingAssignments(array $sectionIds): int
    {
        if (empty($sectionIds)) return 0;

        return Assignment::whereIn('section_id', $sectionIds)
            ->where('due_date', '>=', now())
            ->count();
    }

    // =========================================================================
    // Helper: attendance percentage for a student across their sections
    // Returns 0–100 float. Defaults to 100 if no classes recorded yet.
    // =========================================================================
    private function attendancePercent(int $studentId, array $sectionIds): float
    {
        if (empty($sectionIds)) return 100.0;

        // Total class sessions across those sections
        $totalClasses = DB::table('attendances')
            ->whereIn('section_id', $sectionIds)
            ->count();

        if ($totalClasses === 0) return 100.0;

        // Sessions where this student was present or late
        $attended = AttendanceRecord::whereIn(
                'attendance_id',
                DB::table('attendances')->whereIn('section_id', $sectionIds)->pluck('id')
            )
            ->where('student_id', $studentId)
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($attended / $totalClasses) * 100, 2);
    }

    // =========================================================================
    // Helper: submission rate for a student (0.0 – 1.0)
    // Defaults to 0 if no assignments exist.
    // =========================================================================
    private function submissionRate(int $studentId, array $sectionIds): float
    {
        if (empty($sectionIds)) return 0.0;

        $assignmentIds = Assignment::whereIn('section_id', $sectionIds)->pluck('id');

        if ($assignmentIds->isEmpty()) return 0.0;

        $submitted = DB::table('assignment_submissions')
            ->whereIn('assignment_id', $assignmentIds)
            ->where('student_id', $studentId)
            ->count();

        return round($submitted / $assignmentIds->count(), 4);
    }

    // =========================================================================
    // Helper: study room IDs a student has visited
    // =========================================================================
    private function roomIds(int $userId): array
    {
        return StudyRoomParticipant::where('user_id', $userId)
            ->pluck('study_room_id')
            ->toArray();
    }

    // =========================================================================
    // Helper: resolve match tier label
    // =========================================================================
    private function matchLabel(int $score): array
    {
        if ($score >= 80) return ['Excellent Match', '🔥', '#f97316'];
        if ($score >= 60) return ['Great Match',     '⭐', '#22d3ee'];
        if ($score >= 40) return ['Good Match',      '👍', '#a78bfa'];
        return             ['Potential Match',        '🤝', '#94a3b8'];
    }
}
