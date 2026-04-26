<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\AcademicEvent;

class AcademicAssistantController extends Controller
{
    public function index()
    {
        return view('academic.assistant.index');
    }

    public function ask(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $query = strtolower($request->input('query'));
        $user = auth()->user();

        // Performance Analysis Helper
        $performanceInsights = "";
        
        // Fetch graded submissions for the user
        $gradedSubmissions = \App\Models\AssignmentSubmission::where('student_id', $user->id)
            ->whereNotNull('marks')
            ->with('assignment.section.course')
            ->get();
            
        if ($gradedSubmissions->isNotEmpty()) {
            $performanceInsights .= "\n\n💡 **AI Performance Analysis:**\n";
            $weakTopics = [];
            $strongTopics = [];
            
            foreach ($gradedSubmissions as $sub) {
                if ($sub->assignment->weight > 0) {
                    $percentage = ($sub->marks / $sub->assignment->weight) * 100;
                    $topicName = $sub->assignment->section->course->code . " (" . $sub->assignment->title . ")";
                    if ($percentage < 60) {
                        $weakTopics[] = $topicName . " (" . round($percentage) . "%)";
                    } elseif ($percentage >= 85) {
                        $strongTopics[] = $topicName . " (" . round($percentage) . "%)";
                    }
                }
            }
            
            if (!empty($weakTopics)) {
                $performanceInsights .= "I noticed you've been struggling recently in: " . implode(', ', $weakTopics) . ". ";
                $performanceInsights .= "I highly recommend dedicating extra time to these areas before your next assessment. Let me know if you need study materials for them! ";
            } 
            if (!empty($strongTopics)) {
                $performanceInsights .= "Great job on your high scores in: " . implode(', ', $strongTopics) . "! Keep up the excellent work. ";
            }
            if (empty($weakTopics) && empty($strongTopics)) {
                $performanceInsights .= "Your recent grades are steady and average. Consistent review will help you push those scores higher!";
            }
        }

        // Assistant Logic
        if (str_contains($query, 'priority') || str_contains($query, 'next')) {
            $assignments = Assignment::whereHas('section', function ($q) use ($user) {
                $q->whereHas('students', function ($studentQuery) use ($user) {
                    $studentQuery->where('users.id', $user->id);
                });
            })->where('due_date', '>=', now())
              ->orderBy('due_date', 'asc')
              ->get();

            if ($assignments->isEmpty()) {
                $response = "You have no upcoming assignments. You're all caught up!";
            } else {
                $urgent = $assignments->first();
                $response = "Your highest priority is **{$urgent->title}** for section {$urgent->section->course->code}. It is due on {$urgent->due_date->format('M d, Y h:i A')}.";
                $response .= $performanceInsights; // Blend insights
            }
        } elseif (str_contains($query, 'summary') || str_contains($query, 'workload')) {
            $endDate = now()->addDays(7);
            
            $assignments = Assignment::whereHas('section', function ($q) use ($user) {
                $q->whereHas('students', function ($studentQuery) use ($user) {
                    $studentQuery->where('users.id', $user->id);
                });
            })->whereBetween('due_date', [now(), $endDate])->get();

            $events = AcademicEvent::whereBetween('start_date', [now(), $endDate])->get();

            $response = "Here is your workload summary for the next 7 days:\n\n";
            $response .= "**Assignments (" . $assignments->count() . "):**\n";
            foreach ($assignments as $assignment) {
                $response .= "- {$assignment->title} (Due: {$assignment->due_date->format('M d')})\n";
            }

            $response .= "\n**Events (" . $events->count() . "):**\n";
            foreach ($events as $event) {
                $response .= "- {$event->title} ({$event->start_date->format('M d')})\n";
            }
            
            $response .= $performanceInsights; // Blend insights
            
        } elseif (str_contains($query, 'performance') || str_contains($query, 'marks') || str_contains($query, 'grade') || str_contains($query, 'score')) {
            if ($gradedSubmissions->isNotEmpty()) {
                $response = "Here are your recent grades:\n";
                foreach ($gradedSubmissions as $sub) {
                    $response .= "- **" . $sub->assignment->title . "** (" . $sub->assignment->section->course->code . "): " . $sub->marks . " / " . $sub->assignment->weight . "\n";
                }
                $response .= $performanceInsights;
            } else {
                $response = "You don't have any graded assignments or quizzes yet. Keep an eye out for updates from your teachers!";
            }
        } else {
            $response = "I couldn't quite understand that. Try asking about your **workload summary**, your **study priority**, or your **performance**.";
        }

        return response()->json([
            'response' => nl2br(e($response)) 
        ]);
    }
}
