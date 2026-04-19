<?php
$student = App\Models\User::where('email', 'test@example.com')->first();
$course = App\Models\Course::first();
if (!$course) { echo 'No course found'; exit; }
$section = App\Models\Section::where('course_id', $course->id)->first() ?? App\Models\Section::factory()->create(['course_id' => $course->id]);

if (!$section->students()->where('enrollments.student_id', $student->id)->exists()) {
    $section->students()->attach($student->id);
}

$assignment1 = App\Models\Assignment::create([
    'section_id' => $section->id,
    'title' => 'Midterm',
    'description' => 'Midterm Exam',
    'due_date' => now()->addDays(7),
    'weight' => 30,
    'max_marks' => 30,
    'type' => 'exam'
]);

$assignment2 = App\Models\Assignment::create([
    'section_id' => $section->id,
    'title' => 'Final',
    'description' => 'Final Exam',
    'due_date' => now()->addDays(14),
    'weight' => 40,
    'max_marks' => 100,
    'type' => 'exam'
]);

App\Models\AssignmentSubmission::create([
    'assignment_id' => $assignment1->id,
    'student_id' => $student->id,
    'content' => 'My midterm submission',
    'marks_obtained' => 25,
]);

App\Models\AssignmentSubmission::create([
    'assignment_id' => $assignment2->id,
    'student_id' => $student->id,
    'content' => 'My final submission',
    'marks_obtained' => 85,
]);

echo 'Data created successfully';
