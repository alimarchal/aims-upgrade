<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $permissions = [
        'create admissions',
        'create chits',
        'create invoices',
        'create patients',
        'view chits',
        'view admissions',
        'view dashboard',
        'view invoices',
        'view opd reports',
        'view patients',
        'view reports',
        'view departments',
        'view users',
        'view roles',
        'manage permissions',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'sanctum');
    }

    $role = Role::findOrCreate('Front Desk/Receptionist', 'sanctum');
    $role->syncPermissions([
        'create admissions',
        'create chits',
        'create invoices',
        'create patients',
        'view chits',
        'view admissions',
        'view dashboard',
        'view invoices',
        'view patients',
    ]);
});

test('front desk user has all default permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    expect($user->hasPermissionTo('view dashboard'))->toBeTrue()
        ->and($user->hasPermissionTo('view patients'))->toBeTrue()
        ->and($user->hasPermissionTo('create patients'))->toBeTrue()
        ->and($user->hasPermissionTo('view chits'))->toBeTrue()
        ->and($user->hasPermissionTo('create chits'))->toBeTrue()
        ->and($user->hasPermissionTo('view invoices'))->toBeTrue()
        ->and($user->hasPermissionTo('create invoices'))->toBeTrue()
        ->and($user->hasPermissionTo('view admissions'))->toBeTrue()
        ->and($user->hasPermissionTo('create admissions'))->toBeTrue();
});

test('front desk user cannot access restricted areas', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    expect($user->hasPermissionTo('view departments'))->toBeFalse()
        ->and($user->hasPermissionTo('view users'))->toBeFalse()
        ->and($user->hasPermissionTo('view roles'))->toBeFalse()
        ->and($user->hasPermissionTo('manage permissions'))->toBeFalse()
        ->and($user->hasPermissionTo('view reports'))->toBeFalse()
        ->and($user->hasPermissionTo('view opd reports'))->toBeFalse();
});

test('front desk user can access dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful();
});

test('front desk user can access patients page', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get(route('patient.index'))
        ->assertSuccessful();
});

test('front desk user cannot access departments page', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get(route('department.index'))
        ->assertForbidden();
});

test('front desk user cannot access opd reports', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get(route('reports.opd'))
        ->assertForbidden();
});

test('front desk user cannot access opd user-wise report', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get(route('reports.opd.user-wise'))
        ->assertForbidden();
});

test('front desk user cannot access opd specialist-fees report', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get(route('reports.opd.specialist-fees'))
        ->assertForbidden();
});

test('front desk user cannot access users page', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('front desk user cannot access roles page', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertForbidden();
});

test('front desk permissions are removable', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');

    expect($user->hasPermissionTo('view patients'))->toBeTrue();

    $role = Role::findByName('Front Desk/Receptionist', 'sanctum');
    $role->revokePermissionTo('view patients');

    $user = $user->fresh();
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    expect($user->hasPermissionTo('view patients'))->toBeFalse();
});

test('teams feature is disabled', function () {
    expect(\Laravel\Jetstream\Features::hasTeamFeatures())->toBeFalse();
});
