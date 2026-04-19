<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Notifications\AssignmentDeadlineReminder;

class SendAssignmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-assignment-reminders {--days=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminders to students for upcoming assignments/quizzes in days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $hours = $days * 24;

        $start = now()->addHours($hours)->startOfHour();
        $end = now()->addHours($hours)->endOfHour();

        $assignments = Assignment::whereBetween('due_date', [$start, $end])
            ->with('section.students')
            ->get();

        $count = 0;
        foreach ($assignments as $assignment) {
            foreach ($assignment->section->students as $student) {
                $student->notify(new AssignmentDeadlineReminder($assignment, $days));
                $count++;
            }
        }

        $this->info("Sent {$count} deadline reminders for tasks due in {$days} days.");
    }
}
