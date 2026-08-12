<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

it('creates each role once for the default guard', function () {
    config(['auth.defaults.guard' => 'web']);

    $seeder = new RolesAndPermissionsSeeder();
    $seeder->run();
    $seeder->run();

    $administratorRoles = Role::where('name', 'Administrator')->get();

    expect($administratorRoles)
        ->toHaveCount(1)
        ->and($administratorRoles->first()->guard_name)->toBe('web');

    expect(Role::where('guard_name', 'sanctum')->count())->toBe(0);
});
