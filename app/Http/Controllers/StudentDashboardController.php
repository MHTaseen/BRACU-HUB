<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    /**
     * BRACU Grade Boundary Table (upper-inclusive).
     * [ [minScore, maxScore, letter], ... ] ordered DESC by minScore
     */
    private const GRADE_BOUNDARIES = [
        [97, 100, 'A+'],
        [90, 96,  'A'],
        [85, 89,  'A-'],
        [80, 84,  'B+'],
        [75, 79,  'B'],
        [70, 74,  'B-'],
        [65, 69,  'C+'],
        [60, 64,  'C'],
        [57, 59,  'C-'],
        [55, 56,  'D+'],
        [52, 54,  'D'],
        [50, 51,  'D-'],
        [0,  49,  'F'],
    ];

    /** Map a numeric percentage (0-100) to a grade letter */
    private function getLetter(float $pct): string
    {
        foreach (self::GRADE_BOUNDARIES as [$min, $max, $letter]) {
            if ($pct >= $min) return $letter;
        }
        return 'F';
    }

    /**
     * Get the NEXT grade boundary above the current percentage.
     * Returns ['letter' => 'A', 'min' => 90] or null if already A+.
     */
    private function getNextBoundary(float $pct): ?array
    {
        $boundaries = array_reverse(self::GRADE_BOUNDARIES); // ascending order
        foreach ($boundaries as [$min, $max, $letter]) {
            if ($min > $pct) {
                return ['letter' => $letter, 'min' => $min];
            }
        }
        return null; // already at A+
    }

    public function index(Request $request)
    {
        $studentId = auth()->id();
        $student   = auth()->user();

        // ── 1. Enrolled Sections + Attendance ────────────────────────────────
        $enrolledSections = $student->enrolledSections()
            ->with(['course', 'attendances.records' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->get();

        $attendanceData       = [];
        $globalTotalClasses   = 0;
        $globalAttendedClasses = 0;

        foreach ($enrolledSections as $section) {
            $totalClasses  = $section->attendances->count();
            $presentClasses = 0;
            $lateClasses    = 0;
            $absentClasses  = 0;

            foreach ($section->attendances as $attendance) {
                $record = $attendance->records->first();
                if ($record) {
                    if ($record->status === 'present') $presentClasses++;
                    if ($record->status === 'late')    $lateClasses++;
                    if ($record->status === 'absent')  $absentClasses++;
                }
            }

            $attendedClasses = $presentClasses + $lateClasses;
            $percentage      = $totalClasses > 0
                ? round(($attendedClasses / $totalClasses) * 100)
                : 0;

            $globalTotalClasses    += $totalClasses;
            $globalAttendedClasses += $attendedClasses;

            $attendanceData[] = [
                'section'       => $section,
                'total_classes' => $totalClasses,
                'present'       => $presentClasses,
                'late'          => $lateClasses,
                'absent'        => $absentClasses,
                'percentage'    => $percentage,
            ];
        }

        $globalHealth = $globalTotalClasses > 0
            ? round(($globalAttendedClasses / $globalTotalClasses) * 100)
            : 100;

        // ── 2. Upcoming Deadlines & Past Tasks ───────────────────────────────
        $sectionIds = $enrolledSections->pluck('id');
        $deadlines  = Assignment::whereIn('section_id', $sectionIds)
            ->with(['submissions' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->orderBy('due_date', 'desc')
            ->get();

        // ── 3. Grade Progress Tracker — per-course weighted average ──────────
        $gradeData = [];

        foreach ($enrolledSections as $section) {
            $assignments = Assignment::where('section_id', $section->id)
                ->with(['submissions' => function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                }])
                ->get();

            if ($assignments->isEmpty()) continue;

            $totalWeight   = $assignments->sum('weight'); // sum of all component weights
            $gradedWeight  = 0;  // sum of weights where marks were awarded
            $weightedScore = 0;  // sum of (marks_obtained / max_marks * weight)
            $breakdown     = [];

            foreach ($assignments as $assignment) {
                $submission    = $assignment->submissions->first();
                $marksObtained = $submission?->marks_obtained;
                $maxMarks      = $assignment->max_marks ?: 100;
                $weight        = $assignment->weight ?: 0;

                $pctContrib = null;
                if ($marksObtained !== null) {
                    $pctContrib    = ($marksObtained / $maxMarks) * 100;
                    $weightedScore += ($marksObtained / $maxMarks) * $weight;
                    $gradedWeight  += $weight;
                }

                $breakdown[] = [
                    'title'          => $assignment->title,
                    'type'           => $assignment->type,
                    'weight'         => $weight,
                    'max_marks'      => $maxMarks,
                    'marks_obtained' => $marksObtained,
                    'pct_contrib'    => $pctContrib,
                    'submitted'      => $submission !== null,
                ];
            }

            // Predicted % = weighted score / graded weight × 100
            $predictedPct = $gradedWeight > 0
                ? round(($weightedScore / $gradedWeight) * 100, 2)
                : null;

            $predictedGrade = $predictedPct !== null ? $this->getLetter($predictedPct)     : null;
            $nextBoundary   = $predictedPct !== null ? $this->getNextBoundary($predictedPct) : null;

            $ungradedWeight = $totalWeight - $gradedWeight;

            // Average score needed on remaining assessments to reach next grade boundary
            // Formula: (nextMin/100 × totalWeight - currentWeightedScore) / ungradedWeight × 100
            $avgNeededPct = null;
            if ($nextBoundary && $ungradedWeight > 0 && $totalWeight > 0) {
                $targetWeightedScore = ($nextBoundary['min'] / 100) * $totalWeight;
                $extraNeeded         = $targetWeightedScore - $weightedScore;
                $avgNeededPct = $extraNeeded > 0
                    ? round(($extraNeeded / $ungradedWeight) * 100, 1)
                    : 0; // already on track
            }

            $gradeData[] = [
                'section'         => $section,
                'breakdown'       => $breakdown,
                'graded_weight'   => $gradedWeight,
                'total_weight'    => $totalWeight,
                'ungraded_weight' => $ungradedWeight,
                'predicted_pct'   => $predictedPct,
                'predicted_grade' => $predictedGrade,
                'next_boundary'   => $nextBoundary,
                'avg_needed_pct'  => $avgNeededPct,
            ];
        }

        return view('academic.student.dashboard', compact(
            'student',
            'attendanceData',
            'globalHealth',
            'deadlines',
            'gradeData',
        ));
    }
}
