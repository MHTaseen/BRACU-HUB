<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\AcademicEvent;
use Carbon\Carbon;

class AcademicTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add roles
        $faculty = User::factory()->create([
            'name' => 'Dr. Smith',
            'email' => 'smith@bracu.ac.bd',
            'role' => 'faculty',
        ]);

        $student1 = User::factory()->create([
            'name' => 'Alice Rahman',
            'email' => 'alice@g.bracu.ac.bd',
            'role' => 'student',
        ]);
        
        $student2 = User::factory()->create([
            'name' => 'Bob Karim',
            'email' => 'bob@g.bracu.ac.bd',
            'role' => 'student',
        ]);

        // Courses
        $course1 = Course::create([
            'code' => 'CSE110', 
            'title' => 'Programming Language I', 
            'credits' => 3
        ]);
        
        $course2 = Course::create([
            'code' => 'MAT110', 
            'title' => 'Math I', 
            'credits' => 3
        ]);

        // Sections
        $sec1 = Section::create([
            'course_id' => $course1->id,
            'faculty_id' => $faculty->id,
            'section_number' => '1',
            'room' => 'UB20202',
            'schedule' => 'ST 08:00 AM - 09:20 AM'
        ]);

        $sec2 = Section::create([
            'course_id' => $course2->id,
            'faculty_id' => $faculty->id,
            'section_number' => '3',
            'room' => 'UB20303',
            'schedule' => 'MW 09:30 AM - 10:50 AM'
        ]);

        // Enrollments - Alice and Bob share CSE110. Only Alice takes MAT110.
        Enrollment::create(['student_id' => $student1->id, 'section_id' => $sec1->id]);
        Enrollment::create(['student_id' => $student2->id, 'section_id' => $sec1->id]);
        
        Enrollment::create(['student_id' => $student1->id, 'section_id' => $sec2->id]);

        // Academic Events (Calendar)
        AcademicEvent::create([
            'title' => 'Spring Break',
            'description' => 'University closed for spring holidays.',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'type' => 'holiday'
        ]);
        
        AcademicEvent::create([
            'title' => 'Midterm Week Starts',
            'description' => 'All undergraduate midterms commence.',
            'start_date' => Carbon::parse('2026-06-15 08:00:00'),
            'type' => 'event'
        ]);
        
        // Conflict trigger - a major university-wide exam
        AcademicEvent::create([
            'title' => 'University General Science Assessment (Major Exam)',
            'description' => 'Mandatory assessment for all first-year students.',
            'start_date' => Carbon::now()->addDays(2),
            'end_date' => Carbon::now()->addDays(2)->endOfDay(),
            'type' => 'exam'
        ]);
    }
}
