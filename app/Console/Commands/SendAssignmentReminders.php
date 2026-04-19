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
    protected $signature = 'app:send-assignment-reminders {--days=} {--minutes=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminders to students for upcoming assignments/quizzes. Use --days=N for day-based or --minutes=N for minute-based (demo) reminders.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');

        // --- Minute-based mode (for demo purposes) ---
        if ($minutes !== null) {
            $minutes = (int) $minutes;

            // Send reminders for ALL upcoming assignments (not yet due)
            $assignments = Assignment::where('due_date', '>', now())
                ->with('section.students')
                ->get();

            $count = 0;
            foreach ($assignments as $assignment) {
                $minutesLeft = (int) now()->diffInMinutes($assignment->due_date, false);
                if ($minutesLeft <= 0) {
                    continue;
                }
                foreach ($assignment->section->students as $student) {
                    // Reuse AssignmentDeadlineReminder but pass a descriptive label
                    $student->notify(new AssignmentDeadlineReminder($assignment, $minutesLeft, 'minutes'));
                    $count++;
                }
            }

            $this->info("[DEMO] Sent {$count} minute-countdown reminders.");
            return;
        }

        // --- Day-based mode (original behaviour) ---
        $days = (int) ($this->option('days') ?? 1);
        $hours = $days * 24;

        $start = now()->addHours($hours)->startOfHour();
        $end   = now()->addHours($hours)->endOfHour();

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

        $this->info("Sent {$count} deadline reminders for tasks due in {$days} day(s).");
    }
}
