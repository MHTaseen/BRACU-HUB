<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Course;
use App\Models\User;

class SectionController extends Controller
{
    public function create(Request $request)
    {
        $course_id = $request->query('course_id');
        $course = Course::findOrFail($course_id);
        return view('academic.sections.create', compact('course'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'section_number' => 'required|integer|min:1',
            'room' => 'required|string|max:50',
            'schedule' => 'required|string'
        ]);

        $section = Section::create([
            'course_id' => $validated['course_id'],
            'faculty_id' => auth()->id(),
            'section_number' => $validated['section_number'],
            'room' => $validated['room'],
            'schedule' => $validated['schedule']
        ]);

        return redirect()->route('sections.manage', $section->id)->with('success', 'Section opened successfully.');
    }

    public function manage(Section $section)
    {
        // Security logic: Ensure faculty owns the section
        if ($section->faculty_id !== auth()->id()) {
            abort(403);
        }

        $section->load('students');
        // Load all students for the dropdown datalist
        $allStudents = User::where('role', 'student')->get();

        return view('academic.sections.manage', compact('section', 'allStudents'));
    }

    public function addStudent(Request $request, Section $section)
    {
        // Security logic: Ensure faculty owns the section
        if ($section->faculty_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $student = User::where('id', $validated['student_id'])->where('role', 'student')->firstOrFail();

        // Check if student is already enrolled
        if ($section->students()->where('student_id', $student->id)->exists()) {
            return back()->withErrors(['student_id' => 'Student is already enrolled in this section.']);
        }

        $section->students()->attach($student->id, ['status' => 'active']);

        return back()->with('success', $student->name . ' enrolled successfully!');
    }
}
