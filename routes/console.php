<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run --only-db')->dailyAt('00:05')->name('backup-midnight')->withoutOverlapping();

Schedule::command('fees:apply-one-time-update')
    ->dailyAt('00:00')
    ->when(fn (): bool => now()->toDateString() === '2026-11-01')
    ->name('fees-one-time-update')
    ->withoutOverlapping();
