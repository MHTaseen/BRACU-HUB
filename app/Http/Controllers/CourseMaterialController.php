<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseMaterial;
use App\Models\Section;
use Illuminate\Support\Facades\Storage;

class CourseMaterialController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Section $section)
    {
        // Security check
        if ($section->faculty_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:lecture,assignment_brief,supplementary',
            'description' => 'nullable|string',
            'material_file' => 'required|file|max:20480', // 20 MB max
        ]);

        $filePath = $request->file('material_file')->store('materials', 'public');

        CourseMaterial::create([
            'section_id' => $section->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'file_path' => $filePath,
        ]);

        return back()->with('success', 'Material uploaded successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseMaterial $material)
    {
        // Security check
        if ($material->section->faculty_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }
        
        $material->delete();

        return back()->with('success', 'Material deleted successfully!');
    }

    public function download(CourseMaterial $material)
    {
        // Security check - either faculty owner or enrolled student
        $user = auth()->user();
        $isFaculty = $user->role === 'faculty' && $material->section->faculty_id === $user->id;
        $isStudent = $user->role === 'student' && $material->section->students()->where('student_id', $user->id)->exists();

        if (!$isFaculty && !$isStudent) {
            abort(403, 'Unauthorized action.');
        }

        if (!Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($material->file_path, $material->title . '.' . pathinfo($material->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Student: View repository of all enrolled course materials.
     */
    public function repository()
    {
        if (auth()->user()->role !== 'student') {
            abort(403);
        }

        $student = auth()->user();
        
        // Fetch enrolled sections with materials
        $sections = $student->enrolledSections()
            ->with(['course', 'materials' => function($query) {
                // order materials by newest first
                $query->latest();
            }])
            ->get();

        return view('academic.student.repository', compact('sections'));
    }
}
