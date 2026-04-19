<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ResumeController extends Controller
{
    public function download($student_id)
    {
        $student = User::findOrFail($student_id);

        // Fetch Enrollments (Courses) with their tags for skills
        $enrollments = Enrollment::with(['section.course.tags'])
            ->where('student_id', $student_id)
            ->get();

        // Fetch Submitted Assignments
        $submissions = AssignmentSubmission::with('assignment')
            ->where('student_id', $student_id)
            ->get();

        // Derive skills from course tags
        $skillsSet = [];
        $coursesList = [];
        
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->section->course ?? null;
            if ($course) {
                // Add to courses list
                // We show all enrolled courses since "completed" status might not be universally used,
                // but we label it properly in the resume.
                $coursesList[] = [
                    'code' => $course->code,
                    'title' => $course->title,
                    'status' => ucfirst($enrollment->status),
                    'grade' => 'N/A' // Gracefully handle missing grade
                ];

                // Add tags to skills set
                foreach ($course->tags as $tag) {
                    $skillsSet[$tag->name] = true;
                }
            }
        }

        $skills = array_keys($skillsSet);

        // Render HTML for the PDF directly to avoid touching /resources
        $html = $this->generateHtml($student, $coursesList, $submissions, $skills);

        $pdf = Pdf::loadHTML($html);

        return $pdf->download("resume_{$student_id}.pdf");
    }

    private function generateHtml($student, $courses, $submissions, $skills)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . htmlspecialchars($student->name) . ' - Resume</title>
            <style>
                body { font-family: sans-serif; color: #333; line-height: 1.6; margin: 20px; }
                h1 { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 30px; }
                h2 { color: #555; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 30px; }
                ul { padding-left: 20px; }
                li { margin-bottom: 8px; }
                .course-title { font-weight: bold; }
                .course-meta { color: #666; font-size: 0.9em; }
                .skills-container { display: flex; flex-wrap: wrap; gap: 10px; }
                .skill-badge { background-color: #eee; padding: 5px 10px; border-radius: 5px; border: 1px solid #ccc; display: inline-block; margin-right: 5px; margin-bottom: 5px; }
            </style>
        </head>
        <body>
            <h1>' . htmlspecialchars($student->name) . '</h1>
        ';

        // Skills Section
        $html .= '<h2>Derived Skills & Technologies</h2>';
        if (count($skills) > 0) {
            $html .= '<div class="skills-container">';
            foreach ($skills as $skill) {
                $html .= '<span class="skill-badge">' . htmlspecialchars($skill) . '</span>';
            }
            $html .= '</div>';
        } else {
            $html .= '<p>No specific skills derived yet.</p>';
        }

        // Education / Courses Section
        $html .= '<h2>Completed Courses</h2>';
        if (count($courses) > 0) {
            $html .= '<ul>';
            foreach ($courses as $course) {
                $html .= '<li>';
                $html .= '<span class="course-title">' . htmlspecialchars($course['code'] . ' - ' . $course['title']) . '</span><br>';
                $html .= '<span class="course-meta">Status: ' . htmlspecialchars($course['status']) . ' | Grade: ' . htmlspecialchars($course['grade']) . '</span>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p>No courses found.</p>';
        }

        // Projects / Assignments Section
        $html .= '<h2>Submitted Projects & Assignments</h2>';
        if (count($submissions) > 0) {
            $html .= '<ul>';
            foreach ($submissions as $submission) {
                $assignmentTitle = $submission->assignment ? $submission->assignment->title : 'Unknown Assignment';
                $date = $submission->created_at ? $submission->created_at->format('M d, Y') : 'Unknown Date';
                $html .= '<li>';
                $html .= '<span class="course-title">' . htmlspecialchars($assignmentTitle) . '</span><br>';
                $html .= '<span class="course-meta">Submitted on: ' . htmlspecialchars($date) . '</span>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p>No submitted projects found.</p>';
        }

        $html .= '
        </body>
        </html>
        ';

        return $html;
    }
}
