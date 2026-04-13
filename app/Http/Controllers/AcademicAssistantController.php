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
        } else {
            $response = "I couldn't quite understand that. Try asking about your **workload summary** or your **study priority**.";
        }

        return response()->json([
            'response' => nl2br(e($response)) 
        ]);
    }
}
