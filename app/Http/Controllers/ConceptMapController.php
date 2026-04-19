<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Tag;

class ConceptMapController extends Controller
{
    public function index()
    {
        // Load all courses with their tags
        $courses = Course::with('tags')->get();
        $tagsList = Tag::all();

        // Prepare nodes and edges for vis.js
        $nodes = [];
        $edges = [];
        $tagNodesAdded = [];

        // Add Course Nodes
        foreach ($courses as $course) {
            $isEnrolled = false;
            if (auth()->check() && auth()->user()->role === 'student') {
                // Determine if student is enrolled in this course
                $isEnrolled = $course->sections()->whereHas('students', function($query) {
                    $query->where('student_id', auth()->id());
                })->exists();
            }

            $nodes[] = [
                'id' => 'course_' . $course->id,
                'label' => $course->code . "\n" . wordwrap($course->title, 20, "\n"),
                'group' => 'course',
                'value' => $isEnrolled ? 40 : 25,
                'font' => ['color' => $isEnrolled ? '#fff' : '#aaa'],
                'color' => [
                    'background' => $isEnrolled ? '#3b82f6' : 'rgba(59, 130, 246, 0.2)', // bright blue if enrolled, else dim
                    'border' => '#60a5fa'
                ]
            ];

            foreach ($course->tags as $tag) {
                if (!in_array($tag->id, $tagNodesAdded)) {
                    $nodes[] = [
                        'id' => 'tag_' . $tag->id,
                        'label' => $tag->name,
                        'group' => 'tag',
                        'value' => 15, // Size
                        'color' => [
                            'background' => '#a855f7', // purple for topics
                            'border' => '#c084fc'
                        ]
                    ];
                    $tagNodesAdded[] = $tag->id;
                }

                $edges[] = [
                    'from' => 'course_' . $course->id,
                    'to' => 'tag_' . $tag->id,
                    'color' => ['color' => 'rgba(168, 85, 247, 0.3)'] // Dim edge color
                ];
            }
        }

        return view('academic.concept_map.index', compact('nodes', 'edges'));
    }
}
