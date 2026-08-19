<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('queue:work database --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('school:notify-expulsions')
    ->dailyAt('00:01');


Schedule::command('counselor:generate-tomorrow-appointments')
    ->dailyAt('00:00');


Schedule::command('counselor:complete-expired-appointments')
    ->everyMinute();


Schedule::command('finance:check-installments')
    ->dailyAt('08:00');
