<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run --only-db')->dailyAt('00:05')->name('backup-midnight')->withoutOverlapping();

Schedule::command('fees:apply-one-time-update')
    ->dailyAt('23:55')
    ->when(fn (): bool => now()->toDateString() === '2026-10-31')
    ->name('fees-one-time-update')
    ->withoutOverlapping();
