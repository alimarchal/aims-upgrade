<?php

use App\Models\Chit;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\User;
use Spatie\Permission\Models\Permission;

function departmentWiseStatsViewer(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::findOrCreate('view reports', 'sanctum'),
        Permission::findOrCreate('view department wise stats reports', 'sanctum'),
    ]);

    return $user;
}

it('shows combined patient counts net of returns with filters', function () {
    $patient = Patient::factory()->create();
    $invoice = Invoice::forceCreate([
        'user_id' => User::factory()->create()->id, 'patient_id' => $patient->id, 'total_amount' => 700,
        'hif_amount' => 0, 'government_non_government' => true, 'created_at' => '2025-07-15 10:00:00',
    ]);
    $category = FeeCategory::factory()->create();
    $feeType = FeeType::factory()->create(['fee_category_id' => $category->id, 'status' => 'Normal']);

    foreach ([[true, 'Normal'], [true, 'Normal'], [true, 'Return'], [false, 'Normal'], [false, 'Normal'], [false, 'Normal'], [false, 'Return']] as [$entitled, $status]) {
        PatientTest::forceCreate([
            'patient_id' => $patient->id, 'invoice_id' => $invoice->id, 'fee_type_id' => $feeType->id, 'government_non_gov' => $entitled,
            'total_amount' => 100, 'hif_amount' => 0, 'status' => $status, 'created_at' => '2025-07-15 10:00:00',
        ]);
    }

    $this->actingAs(departmentWiseStatsViewer())
        ->get(route('reports.misc.department-wise-stats', ['start_date' => '2025-07-01', 'end_date' => '2025-07-31']))
        ->assertSuccessful()
        ->assertSee('Patient Count')
        ->assertViewHas('categories', fn (array $categories): bool => $categories[$category->id][$feeType->id]['Patient Count'] === 3);

    $url = ['start_date' => '2025-07-01', 'end_date' => '2025-07-31'];
    $viewer = departmentWiseStatsViewer();

    $this->actingAs($viewer)->get(route('reports.misc.department-wise-stats', $url + ['filter' => ['fee_type_id' => '999999']]))
        ->assertViewHas('categories', []);
});

it('counts chit based fee types without returns', function () {
    $chitFeeType = FeeType::factory()->create([
        'fee_category_id' => FeeCategory::factory()->create(['id' => 13])->id,
        'status' => 'Normal',
    ]);
    Chit::factory()->create(['fee_type_id' => $chitFeeType->id, 'issued_date' => '2025-07-10 10:00:00', 'government_non_gov' => true]);
    Chit::factory()->create(['fee_type_id' => $chitFeeType->id, 'issued_date' => '2025-07-11 10:00:00', 'government_non_gov' => false]);

    $this->actingAs(departmentWiseStatsViewer())
        ->get(route('reports.misc.department-wise-stats', ['start_date' => '2025-07-01', 'end_date' => '2025-07-31']))
        ->assertViewHas('categories', fn (array $categories): bool => $categories[13][$chitFeeType->id]['Patient Count'] === 2);
});

it('only shows the card and report to users with the permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('view reports', 'sanctum'));

    $this->actingAs($user)->get(route('reports.misc.department-wise-stats'))->assertForbidden();
    $this->actingAs($user)->get(route('reports.index'))->assertDontSee(route('reports.misc.department-wise-stats'));

    $this->actingAs(departmentWiseStatsViewer())
        ->get(route('reports.index'))
        ->assertSee(route('reports.misc.department-wise-stats'))
        ->assertSee('Wise Stats');
});
