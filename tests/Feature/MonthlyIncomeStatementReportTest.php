<?php

use App\Models\Chit;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;

test('user without monthly income permission cannot access monthly income statement report', function () {
    Permission::findOrCreate('view reports', 'sanctum');
    Permission::findOrCreate('view monthly income reports', 'sanctum');

    $user = User::factory()->create();
    $user->givePermissionTo('view reports');

    $response = $this->actingAs($user)->get(route('reports.ipd.monthly-income-statement'));

    $response->assertForbidden();
});

test('authorized user can view monthly income statement report with monthly totals', function () {
    Permission::findOrCreate('view reports', 'sanctum');
    Permission::findOrCreate('view monthly income reports', 'sanctum');

    $user = User::factory()->create();
    $user->givePermissionTo('view reports');
    $user->givePermissionTo('view monthly income reports');

    $patient = Patient::factory()->create();

    Invoice::query()->create([
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'total_amount' => 900,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => 900,
        'government_non_government' => 0,
    ])->forceFill([
        'created_at' => Carbon::create(2025, 7, 15, 10, 0, 0),
        'updated_at' => Carbon::create(2025, 7, 15, 10, 0, 0),
    ])->save();

    Chit::factory()->create([
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'department_id' => Department::factory()->create()->id,
        'issued_date' => Carbon::create(2025, 7, 20, 9, 0, 0),
        'amount' => 100,
        'amount_hif' => 0,
    ]);

    Invoice::query()->create([
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'total_amount' => 500,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => 500,
        'government_non_government' => 0,
    ])->forceFill([
        'created_at' => Carbon::create(2025, 8, 5, 11, 0, 0),
        'updated_at' => Carbon::create(2025, 8, 5, 11, 0, 0),
    ])->save();

    $response = $this->actingAs($user)->get(route('reports.ipd.monthly-income-statement', ['year' => 2025]));

    $response->assertSuccessful()
        ->assertSee('Income Statement for Year 2025-26')
        ->assertSee('Jul-25')
        ->assertSee('Aug-25')
        ->assertSee('1000')
        ->assertSee('500')
        ->assertSee('1500');
});
