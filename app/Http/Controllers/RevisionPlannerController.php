<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Tag;

class RevisionPlannerController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        // Fetch enrolled sections with their related course and course tags
        $enrolledSections = $student->enrolledSections()->with('course.tags')->get();

        $revisionPlan = [];

        foreach ($enrolledSections as $section) {
            $course = $section->course;
            if (!isset($revisionPlan[$course->id])) {
                $revisionPlan[$course->id] = [
                    'course' => $course,
                    'tags' => $course->tags
                ];
            }
        }

        return view('academic.student.revision', compact('revisionPlan'));
    }
}
