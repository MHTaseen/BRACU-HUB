<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Section;
use App\Services\ConflictDetectionService;

class AssignmentController extends Controller
{
    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $sections = Section::where('faculty_id', auth()->id())->with('course')->get();
        return view('academic.assignments.create', compact('sections'));
    }

    /**
     * Check for conflicts before storing the assignment.
     * This is an AJAX endpoint for the frontend.
     */
    public function checkConflict(Request $request, ConflictDetectionService $conflictService)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'due_date' => 'required|date|after:today'
        ]);

        // Security check
        Section::where('id', $request->section_id)->where('faculty_id', auth()->id())->firstOrFail();

        $result = $conflictService->detectConflicts($request->section_id, $request->due_date);

        return response()->json($result);
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'due_date' => 'required|date|after:today',
        ], [
            'due_date.after' => 'The assignment due date must be a date after today.',
        ]);

        // Security check
        Section::where('id', $validated['section_id'])->where('faculty_id', auth()->id())->firstOrFail();

        Assignment::create($validated);

        return redirect()->route('assignments.create')
                         ->with('success', 'Assignment created successfully.');
    }
}
