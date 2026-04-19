<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Original day-based reminders (runs every hour, checks for assignments due in 2 or 1 day)
Schedule::command('app:send-assignment-reminders --days=2')->hourly();
Schedule::command('app:send-assignment-reminders --days=1')->hourly();

// DEMO: Sends a countdown reminder to students every 2 minutes for all active assignments
Schedule::command('app:send-assignment-reminders --minutes=2')->everyTwoMinutes();
