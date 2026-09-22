<?php

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

uses(TestCase::class);

it('schedules the database backup only at 00:05', function () {
    $backupEvents = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains($event->command ?? '', 'backup:run --only-db'))
        ->values();

    expect($backupEvents)->toHaveCount(1)
        ->and($backupEvents->first()->expression)->toBe('5 0 * * *')
        ->and($backupEvents->first()->withoutOverlapping)->toBeTrue();
});

it('backs up the default database connection to the local and google disks', function () {
    expect(config('backup.backup.source.databases'))->toBe([config('database.default')])
        ->and(config('backup.backup.destination.disks'))->toBe(['local', 'google']);
});

it('does not pipe the dump through gzip, which is missing on the windows server', function () {
    expect(config('backup.backup.database_dump_compressor'))->toBeNull();
});

it('configures the google drive disk for backups', function () {
    expect(config('filesystems.disks.google.driver'))->toBe('google')
        ->and(config('filesystems.disks.google.backup_name'))->toBe(config('backup.backup.name'));
});

it('dumps postgres as a compressed custom-format .backup file stored uncompressed in the zip', function () {
    expect(config('database.connections.pgsql.dump.add_extra_option'))->toBe('--format=custom --compress=9')
        ->and(config('backup.backup.database_dump_file_extension'))->toBe('backup')
        ->and(config('backup.backup.destination.compression_method'))->toBe(ZipArchive::CM_STORE);
});

it('reads the pg_dump binary path from the environment', function () {
    expect(config('database.connections.pgsql.dump'))->toHaveKey('dump_binary_path');
});
