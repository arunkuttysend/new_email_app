<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule sequence processing every 5 minutes
Schedule::command('sequences:process')->everyFiveMinutes();

// Process scheduled campaigns every minute (backup for delayed jobs)
Schedule::command('campaigns:process-scheduled')->everyMinute();

// Check for replies every 5 minutes
Schedule::command('replies:check')->everyFiveMinutes();
