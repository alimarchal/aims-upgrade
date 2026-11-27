<?php

use App\Models\Patient;
use App\Models\User;
use App\Support\DashboardPermissions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $base = ['view dashboard', 'view admin dashboard', 'view front desk dashboard', 'view patients', 'create patients', 'view chits', 'create chits', 'view invoices', 'create invoices', 'view reports'];
    foreach ([...$base, ...DashboardPermissions::all()] as $permission) {
        Permission::findOrCreate($permission, 'sanctum');
    }

    Role::findOrCreate('Administrator', 'sanctum')->syncPermissions(['view dashboard', 'view admin dashboard', 'view patients', 'view reports', ...DashboardPermissions::all()]);
    Role::findOrCreate('Front Desk/Receptionist', 'sanctum')->syncPermissions(['view front desk dashboard', 'view patients', 'create patients', 'view chits', 'create chits', 'view invoices', 'create invoices']);
});

/** Re-runs the viewer migration now that the permissions exist (RefreshDatabase ran it before they did). */
function createDashboardViewer(): User
{
    $migration = require database_path('migrations/2026_11_27_214543_create_dashboard_viewer_user.php');
    $migration->down();
    $migration->up();

    return User::where('email', 'dashboard@aims.com')->firstOrFail();
}

test('the viewer migration creates a view-only user once', function () {
    $viewer = createDashboardViewer();
    $migration = require database_path('migrations/2026_11_27_214543_create_dashboard_viewer_user.php');
    $migration->up();

    expect(User::where('email', 'dashboard@aims.com')->count())->toBe(1)
        ->and($viewer->hasRole('Front Desk Viewer'))->toBeTrue()
        ->and(Hash::check('aims@2026', $viewer->password))->toBeTrue()
        ->and($viewer->getDirectPermissions()->pluck('name')->all())->toContain('view dashboard', 'view admin dashboard', 'view patients', 'view chits', 'view invoices', 'view dashboard daily chits chart');

    foreach (['view dashboard year on year revenue chart', 'view dashboard month projection', DashboardPermissions::FILTERS, 'create chits', 'create invoices', 'create patients', 'view reports'] as $denied) {
        expect($viewer->can($denied))->toBeFalse("viewer should not have [$denied]");
    }
});

test('the dashboard viewer sees today only, without the year on year chart and the month projection', function () {
    $viewer = createDashboardViewer();

    $this->actingAs($viewer)->get(route('dashboard', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]))
        ->assertOk()
        ->assertViewHas('data', fn (array $data) => $data['from'] === today()->toDateString())
        ->assertSee('Highlights')
        ->assertSee('id="trend-chart"', false)
        ->assertDontSee('id="yoy-chart"', false)
        ->assertDontSee('Month projection')
        ->assertDontSee('Quick periods')
        ->assertSee('Chits history')
        ->assertSee('Invoices history')
        ->assertSee(route('patient.index'), false)
        ->assertDontSee('All reports');
});

test('administrator still sees every chart and can pick a period', function () {
    $admin = User::factory()->create()->assignRole('Administrator');
    $from = now()->subDays(30)->toDateString();

    $this->actingAs($admin)->get(route('dashboard', ['from' => $from, 'to' => now()->toDateString()]))
        ->assertOk()
        ->assertViewHas('data', fn (array $data) => $data['from'] === $from)
        ->assertSee('Quick periods')
        ->assertSee('id="yoy-chart"', false)
        ->assertSee('Month projection');
});

test('chart and filter permissions can be changed per user', function () {
    $viewer = createDashboardViewer();
    $viewer->revokePermissionTo('view dashboard heat map chart');
    $viewer->givePermissionTo(DashboardPermissions::FILTERS);

    $this->actingAs($viewer->fresh())->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('id="heat-chart"', false)
        ->assertSee('id="hours-chart"', false)
        ->assertSee('Quick periods');
});

test('the dashboard viewer can open patients and the chits and invoices history but not reports', function () {
    $viewer = createDashboardViewer();

    $this->actingAs($viewer)->get(route('patient.index'))->assertOk();
    $this->actingAs($viewer)->get(route('chits.issued-today'))->assertOk();
    $this->actingAs($viewer)->get(route('invoice.issued-today'))->assertOk();
    $this->actingAs($viewer)->get(route('reports.index'))->assertForbidden();
});

test('a user who can only view patients cannot create patients, chits or invoices', function () {
    $viewer = createDashboardViewer();
    $patient = Patient::factory()->create();

    $this->actingAs($viewer)->get(route('patient.index'))->assertOk()->assertDontSee('Issue Chit OPD');
    $this->actingAs($viewer)->get(route('patient.create-opd'))->assertForbidden();
    $this->actingAs($viewer)->post(route('patient.store-opd'))->assertForbidden();
    $this->actingAs($viewer)->get(route('patient.issue-new-chit', $patient))->assertForbidden();
    $this->actingAs($viewer)->get(route('patient.proceed', $patient))->assertForbidden();
    $this->actingAs($viewer)->get(route('patient.emergency_treatment', $patient))->assertForbidden();
});

test('front desk users can still register patients', function () {
    $frontDesk = User::factory()->create()->assignRole('Front Desk/Receptionist');

    $this->actingAs($frontDesk)->get(route('patient.index'))->assertOk()->assertSee('Issue Chit OPD');
    $this->actingAs($frontDesk)->get(route('patient.create-opd'))->assertOk();
});

test('chart permissions migration gives administrators every chart and is repeatable', function () {
    Role::findByName('Administrator', 'sanctum')->syncPermissions(['view dashboard']);
    $migration = require database_path('migrations/2026_11_27_214542_add_dashboard_chart_and_filter_permissions.php');

    $migration->up();
    $migration->up();

    $admin = Role::findByName('Administrator', 'sanctum');
    foreach (DashboardPermissions::all() as $permission) {
        expect($admin->hasPermissionTo($permission))->toBeTrue("Administrator should have [$permission]");
    }
    expect(Role::findByName('Front Desk/Receptionist', 'sanctum')->hasPermissionTo('view dashboard month projection'))->toBeFalse()
        ->and(Permission::where('name', DashboardPermissions::FILTERS)->where('guard_name', 'sanctum')->count())->toBe(1);
});
