<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (['view dashboard', 'view admin dashboard', 'view front desk dashboard'] as $permission) {
        Permission::findOrCreate($permission, 'sanctum');
    }

    Role::findOrCreate('Administrator', 'sanctum')->syncPermissions(['view dashboard', 'view admin dashboard']);
    Role::findOrCreate('Front Desk/Receptionist', 'sanctum')->syncPermissions(['view front desk dashboard']);
    Role::findOrCreate('Auditor', 'sanctum')->syncPermissions(['view dashboard']);
});

test('administrator sees the management dashboard without a pdf option', function () {
    $user = User::factory()->create()->assignRole('Administrator');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Highlights')
        ->assertDontSee('Download PDF')
        ->assertDontSee('downloadDashboardPdf');
});

test('administrator can review a past period', function () {
    $user = User::factory()->create()->assignRole('Administrator');

    $this->actingAs($user)->get(route('dashboard', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]))
        ->assertOk()
        ->assertDontSee('Download PDF');

    $this->actingAs($user)->get(route('dashboard', ['from' => 'not a date']))->assertOk();
});

test('front desk user with dashboard access sees their own dashboard', function () {
    $user = User::factory()->create()->assignRole('Front Desk/Receptionist');
    $user->givePermissionTo('view dashboard');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('your own work today')
        ->assertDontSee('Download PDF');
});

test('other roles keep the classic dashboard', function () {
    $user = User::factory()->create()->assignRole('Auditor');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Highlights')
        ->assertDontSee('your own work today');
});

test('dashboard permissions migration grants the right roles and is repeatable', function () {
    $migration = require database_path('migrations/2026_10_15_094403_add_dashboard_permissions.php');

    $migration->up();
    $migration->up();

    expect(Role::findByName('Administrator', 'sanctum')->hasPermissionTo('view admin dashboard'))->toBeTrue()
        ->and(Role::findByName('Front Desk/Receptionist', 'sanctum')->hasPermissionTo('view front desk dashboard'))->toBeTrue()
        ->and(Role::findByName('Auditor', 'sanctum')->hasPermissionTo('view admin dashboard'))->toBeFalse()
        ->and(Permission::where('name', 'view admin dashboard')->where('guard_name', 'sanctum')->count())->toBe(1);
});
