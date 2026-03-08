<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Assignment;
use App\Models\AcademicEvent;
use Carbon\Carbon;

class ConflictDetectionService
{
    /**
     * Check if a proposed assignment due date causes excessive workload
     * for any students enrolled in the given section.
     * 
     * @param int $sectionId
     * @param \Carbon\Carbon|string $dueDate
     * @return array
     */
    public function detectConflicts($sectionId, $dueDate)
    {
        $dueDate = Carbon::parse($dueDate)->startOfDay();
        $section = Section::with('students')->findOrFail($sectionId);
        $studentIds = $section->students->pluck('id');

        if ($studentIds->isEmpty()) {
            return [
                'has_conflict' => false,
                'conflict_count' => 0,
                'conflicts' => [],
                'message' => 'No students enrolled in this section.'
            ];
        }

        $conflicts = [];

        // 1. Check for overlapping Major Exams from Academic Calendar
        $examsOnSameDay = AcademicEvent::where('type', 'exam')
            ->whereDate('start_date', '<=', $dueDate)
            ->whereDate('end_date', '>=', $dueDate)
            ->get();

        foreach ($examsOnSameDay as $exam) {
            $conflicts[] = [
                'title' => $exam->title,
                'type' => 'Project/Exam/Holiday'
            ];
        }

        // 2. Check for overlapping assignments for the enrolled students
        // 2. Check for overlapping assignments for the enrolled students
        $conflictingAssignments = Assignment::whereDate('due_date', $dueDate)
            ->whereHas('section.students', function ($query) use ($studentIds) {
                $query->whereIn('users.id', $studentIds);
            })
            ->with(['section.course', 'section.students'])
            ->get();

        foreach ($conflictingAssignments as $assignment) {
            $conflicts[] = [
                'title' => ($assignment->section->course->code ?? 'N/A') . ' - ' . $assignment->title,
                'type' => 'Assignment'
            ];
        }

        if (count($conflicts) > 0) {
            $affectedStudentIds = [];
            
            // For assignments, calculate precisely which students from OUR section are affected
            foreach ($section->students as $student) {
                foreach ($conflictingAssignments as $assignment) {
                    if ($assignment->section->students->contains('id', $student->id)) {
                        $affectedStudentIds[] = $student->id;
                        break; // No need to check other assignments for this student
                    }
                }
            }

            // For exams/holidays/events, assume university-wide impact
            if ($examsOnSameDay->isNotEmpty()) {
                foreach ($studentIds as $id) {
                    $affectedStudentIds[] = $id;
                }
            }

            $affectedCount = count(array_unique($affectedStudentIds));

            return [
                'has_conflict' => true,
                'conflict_count' => $affectedCount,
                'conflicts' => $conflicts,
                'message' => "Warning: $affectedCount student(s) have conflicting tasks."
            ];
        }

        return [
            'has_conflict' => false,
            'conflict_count' => 0,
            'conflicts' => [],
            'message' => 'No conflicts detected.'
        ];
    }
}
