<?php

use App\Models\Chit;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Front Desk/Receptionist', 'sanctum');

    $this->viewer = User::factory()->create();
    $this->viewer->givePermissionTo([
        Permission::findOrCreate('view reports', 'sanctum'),
        Permission::findOrCreate('view govt user wise reports', 'sanctum'),
        Permission::findOrCreate('view department wise audit reports', 'sanctum'),
    ]);
});

function seedGovtUserWiseReportData(User $receptionist): void
{
    $patient = Patient::factory()->create();
    $chitFeeType = FeeType::factory()->create([
        'fee_category_id' => FeeCategory::factory()->create(['id' => 13])->id,
        'status' => 'Normal',
    ]);
    $labCategory = FeeCategory::factory()->create();
    $labFeeType = FeeType::factory()->create(['fee_category_id' => $labCategory->id, 'status' => 'Normal']);
    $excludedFeeType = FeeType::factory()->create(['fee_category_id' => $labCategory->id, 'type' => 'Room Charges', 'status' => 'Normal']);

    Chit::factory()->for($receptionist)->create([
        'fee_type_id' => $chitFeeType->id, 'issued_date' => '2025-07-10 10:00:00',
        'amount' => 100, 'amount_hif' => 20, 'government_non_gov' => true,
    ]);
    Chit::factory()->for($receptionist)->create([
        'fee_type_id' => $chitFeeType->id, 'issued_date' => '2025-07-11 10:00:00',
        'amount' => 50, 'amount_hif' => 0, 'government_non_gov' => false,
    ]);
    Chit::factory()->for($receptionist)->create([
        'fee_type_id' => $chitFeeType->id, 'issued_date' => '2025-08-01 10:00:00',
        'amount' => 999, 'amount_hif' => 0, 'government_non_gov' => false,
    ]);

    $invoice = Invoice::forceCreate([
        'user_id' => $receptionist->id, 'patient_id' => $patient->id, 'total_amount' => 700,
        'hif_amount' => 100, 'government_non_government' => true, 'created_at' => '2025-07-15 10:00:00',
    ]);

    foreach ([
        [$labFeeType->id, true, 300, 60, 'Normal'],
        [$labFeeType->id, false, 200, 40, 'Normal'],
        [$excludedFeeType->id, false, 200, 0, 'Normal'],
        [$labFeeType->id, false, -100, 0, 'Return'],
    ] as [$feeTypeId, $isEntitled, $total, $hif, $status]) {
        PatientTest::forceCreate([
            'patient_id' => $patient->id, 'fee_type_id' => $feeTypeId, 'invoice_id' => $invoice->id,
            'government_non_gov' => $isEntitled, 'total_amount' => $total, 'hif_amount' => $hif,
            'status' => $status, 'created_at' => '2025-07-15 10:00:00',
        ]);
    }
}

it('shows user wise govt revenue without hif', function () {
    $receptionist = User::factory()->create(['name' => 'Receptionist One']);
    seedGovtUserWiseReportData($receptionist);

    $this->actingAs($this->viewer)
        ->get(route('reports.ipd.reportDailyUserWiseGovt', ['start_date' => '2025-07-01', 'end_date' => '2025-07-31']))
        ->assertSuccessful()
        ->assertDontSee('HIF')
        ->assertViewHas('data', function (array $data) use ($receptionist): bool {
            return $data[$receptionist->id] === [
                'Name' => 'Receptionist One',
                'Invoices Entitled' => 1,
                'Invoices Non Entitled' => 1,
                'Invoices Returns' => 1,
                'Invoices Returns Amount' => -100.0,
                'Invoices' => 300.0,
                'Chit Entitled' => 1,
                'Chit Non Entitled' => 1,
                'Chits' => 130.0,
            ];
        });
});

it('matches the department wise audit report totals', function () {
    seedGovtUserWiseReportData(User::factory()->create());
    $range = ['start_date' => '2025-07-01', 'end_date' => '2025-07-31'];

    $userWise = collect($this->actingAs($this->viewer)->get(route('reports.ipd.reportDailyUserWiseGovt', $range))->viewData('data'));
    $audit = collect($this->actingAs($this->viewer)->get(route('reports.misc.department-wise-audit', $range))->viewData('categories'))->flatten(1);

    expect($userWise->sum(fn (array $row) => $row['Invoices Entitled'] + $row['Chit Entitled']))->toBe($audit->sum('Entitled'))
        ->and($userWise->sum(fn (array $row) => $row['Invoices Non Entitled'] + $row['Chit Non Entitled']))->toBe($audit->sum('Non Entitled'))
        ->and((float) $userWise->sum(fn (array $row) => $row['Invoices'] + $row['Chits']))->toBe((float) $audit->sum('GOVT'));
});

it('reconciles the audit total with the monthly income statement total', function () {
    seedGovtUserWiseReportData(User::factory()->create());

    $this->actingAs($this->viewer)
        ->get(route('reports.ipd.reportDailyUserWiseGovt', ['start_date' => '2025-07-01', 'end_date' => '2025-07-31']))
        ->assertSuccessful()
        ->assertViewHas('reconciliation', [
            'audit_total' => 430.0,
            'items' => ['Room Charges' => 200.0, 'Other differences (date / unsynced invoices)' => -30.0],
            'income_statement_total' => 600.0,
        ])
        ->assertSeeInOrder(['Total as per Department Wise Audit Report (above)', '430.00', 'Room Charges', '200.00', 'Total as per Monthly Income Statement', '600.00'])
        ->assertSee('Note: 1 returned test(s) in this period.')
        ->assertSeeInOrder(['New tests: 400.00', 'Refund of 1 test(s): -100.00', '= 300.00'])
        ->assertDontSee('<th class="border-black border px-4 py-2 text-center">Returns</th>', false);
});

it('forbids users without the govt user wise reports permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('view reports', 'sanctum'));

    $this->actingAs($user)
        ->get(route('reports.ipd.reportDailyUserWiseGovt'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertDontSee(route('reports.ipd.reportDailyUserWiseGovt'));
});

it('shows the govt user wise card on the reports page', function () {
    $this->actingAs($this->viewer)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertSee(route('reports.ipd.reportDailyUserWiseGovt'))
        ->assertSeeInOrder(['Govt', 'User Wise Report']);
});
