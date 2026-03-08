<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Show the form for marking attendance.
     */
    public function create(Request $request)
    {
        $facultyId = auth()->id();
        $sections = Section::where('faculty_id', $facultyId)->with('course')->get();
        
        $selectedSection = null;
        $students = [];
        
        if ($request->has('section_id')) {
            $selectedSection = Section::where('faculty_id', $facultyId)
                ->with('students')
                ->findOrFail($request->section_id);
            $students = $selectedSection->students;
        }

        return view('academic.attendance.mark', compact('sections', 'selectedSection', 'students'));
    }

    /**
     * Store attendance records.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'in:present,absent,late'
        ]);

        $classDate = Carbon::parse($validated['class_date'])->toDateString();

        // Security: Ensure the faculty owns this section
        Section::where('id', $validated['section_id'])->where('faculty_id', auth()->id())->firstOrFail();

        // Find or create the master attendance record for this class session
        $attendance = Attendance::firstOrCreate([
            'section_id' => $validated['section_id'],
            'class_date' => $classDate
        ]);

        // Bulk upsert attendance records
        foreach ($validated['attendance'] as $studentId => $status) {
            AttendanceRecord::updateOrCreate(
                ['attendance_id' => $attendance->id, 'student_id' => $studentId],
                ['status' => $status]
            );
        }

        return redirect()->route('attendance.create', ['section_id' => $validated['section_id']])
                         ->with('success', 'Attendance recorded successfully for ' . $classDate);
    }

    /**
     * Display the student's personal attendance trend dashboard.
     */
    public function showStudent(Request $request)
    {
        $studentId = auth()->id();
        $student = auth()->user();

        $enrolledSections = $student->enrolledSections()
            ->with(['course', 'attendances.records' => function ($query) use ($studentId) {
                // Only load records for this specific student
                $query->where('student_id', $studentId);
            }])
            ->get();

        $attendanceData = [];

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

            // Late might count as partial or full present depending on policy.
            // Let's count present + late as 'attended' for percentage purposes.
            $attendedClasses = $presentClasses + $lateClasses;
            $percentage = $totalClasses > 0 ? round(($attendedClasses / $totalClasses) * 100) : 0;

            $attendanceData[] = [
                'section' => $section,
                'total_classes' => $totalClasses,
                'present' => $presentClasses,
                'late' => $lateClasses,
                'absent' => $absentClasses,
                'percentage' => $percentage
            ];
        }

        return view('academic.attendance.student', compact('attendanceData', 'student'));
    }
}
