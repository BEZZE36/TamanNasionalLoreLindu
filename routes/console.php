<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto backup scheduler - runs every minute to check if backup is needed
Schedule::command('backup:auto')->everyMinute()->withoutOverlapping();

// Publish scheduled articles - runs every minute
Schedule::command('articles:publish-scheduled')->everyMinute()->withoutOverlapping();

// Expire old tickets and bookings - runs daily at midnight
Schedule::command('tickets:expire')->daily()->withoutOverlapping();
