<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $studentId = auth()->id();
        $student = auth()->user();

        // 1. Fetch Enrolled Sections and Attendance
        $enrolledSections = $student->enrolledSections()
            ->with(['course', 'attendances.records' => function ($query) use ($studentId) {
                // Only load records for this specific student
                $query->where('student_id', $studentId);
            }])
            ->get();

        $attendanceData = [];
        $globalTotalClasses = 0;
        $globalAttendedClasses = 0;

        foreach ($enrolledSections as $section) {
            $totalClasses = $section->attendances->count();
            $presentClasses = 0;
            $lateClasses = 0;
            $absentClasses = 0;

            foreach ($section->attendances as $attendance) {
                $record = $attendance->records->first();
                if ($record) {
                    if ($record->status === 'present') $presentClasses++;
                    if ($record->status === 'late') $lateClasses++;
                    if ($record->status === 'absent') $absentClasses++;
                }
            }

            $attendedClasses = $presentClasses + $lateClasses;
            $percentage = $totalClasses > 0 ? round(($attendedClasses / $totalClasses) * 100) : 0;

            $globalTotalClasses += $totalClasses;
            $globalAttendedClasses += $attendedClasses;

            $attendanceData[] = [
                'section' => $section,
                'total_classes' => $totalClasses,
                'present' => $presentClasses,
                'late' => $lateClasses,
                'absent' => $absentClasses,
                'percentage' => $percentage
            ];
        }

        $globalHealth = $globalTotalClasses > 0 ? round(($globalAttendedClasses / $globalTotalClasses) * 100) : 100; // default 100%

        // 2. Fetch Upcoming Deadlines & Past Tasks
        $sectionIds = $enrolledSections->pluck('id');
        // Fetch all assignments so we can show submitted status even if passed
        $deadlines = Assignment::whereIn('section_id', $sectionIds)
            ->with(['submissions' => function($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->orderBy('due_date', 'desc')
            ->get();

        return view('academic.student.dashboard', compact('student', 'attendanceData', 'globalHealth', 'deadlines'));
    }
}
