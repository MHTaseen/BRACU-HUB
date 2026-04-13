<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;
use App\Models\Course;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'Programming', 'Algorithms', 'Data Structures', 'Machine Learning',
            'Artificial Intelligence', 'Mathematics', 'Software Engineering',
            'Web Development', 'Systems', 'Databases', 'Networking'
        ];

        $createdTags = [];
        foreach ($tags as $tagName) {
            $createdTags[] = Tag::firstOrCreate(['name' => $tagName]);
        }

        // Attach random tags to courses
        $courses = Course::all();
        
        foreach ($courses as $course) {
            // Assign 2 to 4 random tags to each course to show overlap
            $randomTags = collect($createdTags)->random(rand(2, 4))->pluck('id');
            $course->tags()->syncWithoutDetaching($randomTags);
        }
    }
}
