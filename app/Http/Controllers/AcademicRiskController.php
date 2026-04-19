<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;

class AcademicRiskController extends Controller
{
    public function getRiskPrediction($student_id)
    {
        $student = User::findOrFail($student_id);

        // 1. Calculate Attendance Percentage
        $attendanceRecords = AttendanceRecord::where('student_id', $student_id)->get();
        $totalClasses = $attendanceRecords->count();
        $attendedClasses = $attendanceRecords->whereIn('status', ['present', 'late'])->count();
        
        $attendancePercentage = $totalClasses > 0 ? round(($attendedClasses / $totalClasses) * 100) : 100;

        // Initial Risk based on Attendance
        $riskLevel = 'Low';
        if ($attendancePercentage < 60) {
            $riskLevel = 'High';
        } elseif ($attendancePercentage >= 60 && $attendancePercentage <= 75) {
            $riskLevel = 'Medium';
        }

        // 2. Calculate Late Submission Percentage
        $submissions = AssignmentSubmission::with('assignment')->where('student_id', $student_id)->get();
        $totalSubmissions = $submissions->count();
        $lateSubmissions = 0;

        foreach ($submissions as $submission) {
            if ($submission->assignment && $submission->created_at > $submission->assignment->due_date) {
                $lateSubmissions++;
            }
        }

        $lateSubmissionPercentage = $totalSubmissions > 0 ? round(($lateSubmissions / $totalSubmissions) * 100) : 0;
        
        $lateSubmissionFlag = false;

        // Apply Late Submission Rules
        if ($lateSubmissionPercentage > 50) {
            // Increase risk by one level
            if ($riskLevel === 'Low') {
                $riskLevel = 'Medium';
            } elseif ($riskLevel === 'Medium') {
                $riskLevel = 'High';
            }
        } elseif ($lateSubmissionPercentage >= 30 && $lateSubmissionPercentage <= 50) {
            // Keep risk level same but flag it
            $lateSubmissionFlag = true;
        }

        return response()->json([
            'student_id' => $student->id,
            'student_name' => $student->name,
            'metrics' => [
                'attendance_percentage' => $attendancePercentage,
                'late_submission_percentage' => $lateSubmissionPercentage,
                'grade_trend' => 'N/A'
            ],
            'flags' => [
                'late_submission_warning' => $lateSubmissionFlag
            ],
            'risk_level' => $riskLevel
        ]);
    }
}
