<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Tag;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::withCount('sections')->get();
        return view('academic.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('academic.courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code|max:20',
            'title' => 'required|string|max:255',
            'credits' => 'required|numeric|min:1|max:6',
            'description' => 'required|string',
            'course_contents' => 'nullable|string' // comma separated topics
        ]);

        $course = Course::create([
            'code' => $validated['code'],
            'title' => $validated['title'],
            'credits' => $validated['credits'],
            'description' => $validated['description'],
        ]);

        if (!empty($validated['course_contents'])) {
            $topicNames = array_map('trim', explode(',', $validated['course_contents']));
            $tagIds = [];
            foreach ($topicNames as $name) {
                if ($name !== '') {
                    $tag = Tag::firstOrCreate(['name' => $name]);
                    $tagIds[] = $tag->id;
                }
            }
            $course->tags()->sync($tagIds);
        }

        return redirect()->route('courses.index')->with('success', 'Course added successfully to the university directory.');
    }
}
