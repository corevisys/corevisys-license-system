<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('license:renew-subscriptions')->dailyAt('00:00');
Schedule::command('license:notify-expiring')->dailyAt('01:00');
Schedule::command('license:cleanup-expired')->monthly();
