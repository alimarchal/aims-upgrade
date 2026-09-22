<?php

use Illuminate\Console\Scheduling\Schedule;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Tests\TestCase;

uses(TestCase::class);

it('schedules the database backup only at 00:01', function () {
    $backupEvents = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains($event->command ?? '', 'backup:run --only-db'))
        ->values();

    expect($backupEvents)->toHaveCount(1)
        ->and($backupEvents->first()->expression)->toBe('1 0 * * *')
        ->and($backupEvents->first()->withoutOverlapping)->toBeTrue();
});

it('backs up the default database connection to the local and google disks', function () {
    expect(config('backup.backup.source.databases'))->toBe([config('database.default')])
        ->and(config('backup.backup.destination.disks'))->toBe(['local', 'google'])
        ->and(config('backup.backup.database_dump_compressor'))->toBe(GzipCompressor::class);
});

it('configures the google drive disk for backups', function () {
    expect(config('filesystems.disks.google.driver'))->toBe('google')
        ->and(config('filesystems.disks.google.backup_name'))->toBe(config('backup.backup.name'));
});

it('reads the pg_dump binary path from the environment', function () {
    expect(config('database.connections.pgsql.dump'))->toHaveKey('dump_binary_path');
});
