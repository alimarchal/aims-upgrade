<?php

use App\Models\User;

it('renames the developer account to SeeChange Innovative and can be rolled back', function () {
    $developer = User::factory()->create(['name' => 'Ali Raza Marchal (SA)', 'email' => 'kh.marchal@gmail.com']);
    $otherUser = User::factory()->create(['name' => 'Sardar Asim Zaib']);

    $migration = require database_path('migrations/2026_09_24_045700_rename_super_admin_user_to_seechange_innovative.php');

    $migration->up();

    expect($developer->fresh()->name)->toBe('SeeChange Innovative')
        ->and($otherUser->fresh()->name)->toBe('Sardar Asim Zaib');

    $migration->down();

    expect($developer->fresh()->name)->toBe('Ali Raza Marchal (SA)');
});
