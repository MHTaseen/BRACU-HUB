<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizGradeController extends Controller
{
    // -------------------------------------------------------------------------
    // Faculty: list all quiz submissions in their sections, grouped by section
    // -------------------------------------------------------------------------
    public function index()
    {
        $faculty = Auth::user();

        $sections = $faculty->sectionsTaught()->with(['course'])->get();

        // For each section, fetch quiz-type assignments with their submissions
        $gradingData = [];

        foreach ($sections as $section) {
            $quizzes = Assignment::where('section_id', $section->id)
                ->where('type', 'Quiz')
                ->with(['submissions.student'])
                ->orderBy('due_date')
                ->get();

            if ($quizzes->isEmpty()) continue;

            // Build enrolled students list for this section
            $enrolledStudents = $section->students;

            $quizRows = [];

            foreach ($quizzes as $quiz) {
                $rows = [];

                foreach ($enrolledStudents as $student) {
                    $submission = $quiz->submissions
                        ->firstWhere('student_id', $student->id);

                    $rows[] = [
                        'student'    => $student,
                        'submission' => $submission,
                        'graded'     => $submission && $submission->marks_obtained !== null,
                        'pct'        => $submission ? $submission->performancePercent() : null,
                    ];
                }

                $quizRows[] = [
                    'quiz'       => $quiz,
                    'rows'       => $rows,
                    'total_marks' => $quiz->submissions->first()?->total_marks ?? 100,
                ];
            }

            if (!empty($quizRows)) {
                $gradingData[] = [
                    'section'    => $section,
                    'quiz_rows'  => $quizRows,
                ];
            }
        }

        return view('academic.assignments.quiz-grades', compact('gradingData'));
    }

    // -------------------------------------------------------------------------
    // Faculty: save marks for a single submission (AJAX)
    // -------------------------------------------------------------------------
    public function store(Request $request, Assignment $assignment)
    {
        // Authorization: faculty must own this section
        if ($assignment->section->faculty_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'student_id'     => 'required|exists:users,id',
            'marks_obtained' => 'required|numeric|min:0',
            'total_marks'    => 'required|numeric|min:1',
        ]);

        // Validate marks don't exceed total
        if ($request->marks_obtained > $request->total_marks) {
            return response()->json([
                'success' => false,
                'message' => 'Marks obtained cannot exceed total marks.',
            ], 422);
        }

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id'    => $request->student_id,
            ],
            [
                'marks_obtained' => $request->marks_obtained,
                'total_marks'    => $request->total_marks,
            ]
        );

        $pct = $submission->performancePercent();

        return response()->json([
            'success'     => true,
            'pct'         => $pct,
            'badge_label' => $this->badgeLabel($pct),
            'badge_color' => $this->badgeColor($pct),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function badgeLabel(?float $pct): string
    {
        if ($pct === null) return 'Not Graded';
        if ($pct >= 80)   return 'Excellent';
        if ($pct >= 60)   return 'Passed';
        if ($pct >= 40)   return 'Needs Work';
        return 'Critical';
    }

    private function badgeColor(?float $pct): string
    {
        if ($pct === null) return '#64748b';
        if ($pct >= 80)   return '#4ade80';
        if ($pct >= 60)   return '#22d3ee';
        if ($pct >= 40)   return '#fb923c';
        return '#f87171';
    }
}
