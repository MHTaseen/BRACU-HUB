<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;

class AssignmentSubmissionController extends Controller
{
    public function create(Assignment $assignment)
    {
        // View for submitting the assignment
        return view('academic.student.submit', compact('assignment'));
    }

    public function store(Request $request, Assignment $assignment)
    {
        $request->validate([
            'content' => 'nullable|string',
            'submission_file' => 'nullable|file|max:10240' // 10MB max
        ]);

        if (!$request->filled('content') && !$request->hasFile('submission_file')) {
            return back()->withErrors(['content' => 'You must either provide text content or attach a file.']);
        }

        $filePath = null;
        if ($request->hasFile('submission_file')) {
            $filePath = $request->file('submission_file')->store('submissions', 'public');
        }

        // Create or update the submission securely for the logged in student
        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => auth()->id()],
            [
                'content' => $request->input('content'),
                'file_path' => $filePath
            ]
        );

        return redirect()->route('student.tracker')->with('success', 'Assignment submitted successfully. Time recorded!');
    }

    public function show(AssignmentSubmission $submission)
    {
        // Security logic: Ensure the user is the student
        if ($submission->student_id !== auth()->id()) {
            abort(403);
        }

        return view('academic.student.submit-show', compact('submission'));
    }

    public function indexForFaculty(Assignment $assignment)
    {
        // Security logic: Ensure faculty owns the section
        if ($assignment->section->faculty_id !== auth()->id()) {
            abort(403);
        }

        $assignment->load(['submissions.student', 'section.students']);

        return view('academic.assignments.submissions', compact('assignment'));
    }

    /**
     * Faculty grades a specific student submission.
     */
    public function grade(Request $request, AssignmentSubmission $submission)
    {
        // Security: Ensure the logged-in faculty owns the section this submission belongs to
        if ($submission->assignment->section->faculty_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'marks_obtained' => [
                'required',
                'numeric',
                'min:0',
                'max:' . $submission->assignment->max_marks,
            ],
        ], [
            'marks_obtained.max' => 'Marks cannot exceed the maximum of ' . $submission->assignment->max_marks . '.',
        ]);

        $submission->update(['marks_obtained' => $request->marks_obtained]);

        return back()->with('grade_success', 'Marks saved for ' . $submission->student->name . '!');
    }
}
