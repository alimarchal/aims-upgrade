<?php

use App\Models\Admission;
use App\Models\AdmissionWard;
use App\Models\Chit;
use App\Models\Department;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Super-Admin', 'sanctum');

    $this->user = User::factory()->withPersonalTeam()->create();

    $this->user->assignRole('Super-Admin');

    DB::table('government_departments')->insertOrIgnore(['id' => 1, 'name' => 'Army', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('government_departments')->insertOrIgnore(['id' => 95, 'name' => 'SEHAT SAHULAT PROGRAM', 'created_at' => now(), 'updated_at' => now()]);

    $this->department = Department::create([
        'name' => 'Specialist ENT',
        'daily_patient_limit' => 100,
        'category' => 'OPD',
    ]);

    $this->opdCategory = FeeCategory::create([
        'name' => 'OPD (Out Door Patient)',
        'type' => 'OPD',
    ]);

    $this->labCategory = FeeCategory::create([
        'name' => 'Pathology',
        'type' => 'Pathology',
    ]);

    $this->feeType = FeeType::create([
        'fee_category_id' => $this->opdCategory->id,
        'type' => 'Specialist ENT',
        'amount' => 50.00,
        'hif' => 20.00,
        'status' => 'Normal',
    ]);

    $this->labFeeType = FeeType::create([
        'fee_category_id' => $this->labCategory->id,
        'type' => 'CBC Test',
        'amount' => 200.00,
        'hif' => 100.00,
        'status' => 'Normal',
    ]);

    AdmissionWard::firstOrCreate(['name' => 'Surgical Ward']);

    $this->sspPatient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mr',
        'first_name' => 'ALI',
        'last_name' => 'SSP',
        'father_husband_name' => 'Father SSP',
        'relationship_title' => 'S/O',
        'sex' => 1,
        'age' => 30,
        'years_months' => 'Years',
        'registration_date' => now(),
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    $this->nonSspPatient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mrs',
        'first_name' => 'SARA',
        'last_name' => 'ARMY',
        'father_husband_name' => 'Father Army',
        'relationship_title' => 'D/O',
        'sex' => 0,
        'age' => 25,
        'years_months' => 'Years',
        'registration_date' => now(),
        'government_non_gov' => 1,
        'government_department_id' => 1,
    ]);
});

it('loads the ssp claims report page', function () {
    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims'));

    $response->assertSuccessful();
    $response->assertViewIs('reports.ssp.claims');
    $response->assertSee('Sehat Sahulat Program');
});

it('returns correct data for ssp opd chits', function () {
    $sspChit = Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'amount_hif' => 0,
        'govt_amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    // Non-SSP chit should not appear
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->nonSspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 30,
        'amount_hif' => 10,
        'govt_amount' => 20,
        'actual_amount' => 0,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 1,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

    $response->assertSuccessful();
    $response->assertViewHas('opdChits', function ($chits) {
        return $chits->count() === 1
            && $chits->first()->government_department_id === 95;
    });
    $response->assertViewHas('summary', function ($summary) {
        return $summary['opd_total_chits'] === 1
            && (float) $summary['opd_actual_amount'] === 50.00;
    });
});

it('returns correct data for ssp ipd invoices', function () {
    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => 200.00,
    ]);

    PatientTest::create([
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->labFeeType->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => 200.00,
        'status' => 'Normal',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

    $response->assertSuccessful();
    $response->assertViewHas('ipdInvoices', function ($invoices) {
        return $invoices->count() === 1
            && (float) $invoices->first()->actual_total_amount === 200.00;
    });
    $response->assertViewHas('summary', function ($summary) {
        return $summary['ipd_total_invoices'] === 1
            && (float) $summary['ipd_actual_amount'] === 200.00;
    });
});

it('filters by report type opd only', function () {
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'actual_total_amount' => 200.00,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'report_type' => 'opd',
        ]));

    $response->assertSuccessful();
    $response->assertViewHas('opdChits', fn ($c) => $c->count() === 1);
    $response->assertViewHas('ipdInvoices', fn ($i) => $i->isEmpty());
});

it('filters by report type ipd only', function () {
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'actual_total_amount' => 200.00,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'report_type' => 'ipd',
        ]));

    $response->assertSuccessful();
    $response->assertViewHas('opdChits', fn ($c) => $c->isEmpty());
    $response->assertViewHas('ipdInvoices', fn ($i) => $i->count() === 1);
});

it('filters by date range correctly', function () {
    // Chit from yesterday
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now()->subDay(),
        'amount' => 0,
        'actual_amount' => 30.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    // Chit from today
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    // Only today's date
    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

    $response->assertViewHas('opdChits', fn ($c) => $c->count() === 1);

    // Both days
    $response2 = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

    $response2->assertViewHas('opdChits', fn ($c) => $c->count() === 2);
});

it('filters by department', function () {
    $otherDept = Department::create([
        'name' => 'Specialist Eye',
        'daily_patient_limit' => 100,
        'category' => 'OPD',
    ]);

    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $otherDept->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 40.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
        ]));

    $response->assertViewHas('opdChits', fn ($c) => $c->count() === 1);
});

it('filters by patient id', function () {
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    $anotherSspPatient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mr',
        'first_name' => 'ANOTHER',
        'last_name' => 'SSP',
        'sex' => 1,
        'age' => 40,
        'years_months' => 'Years',
        'registration_date' => now(),
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $anotherSspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 60.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'patient_id' => $this->sspPatient->id,
        ]));

    $response->assertViewHas('opdChits', fn ($c) => $c->count() === 1);
});

it('filters by patient name', function () {
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'patient_name' => 'ALI',
        ]));

    $response->assertViewHas('opdChits', fn ($c) => $c->count() === 1);

    // Non-matching name
    $response2 = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'patient_name' => 'NONEXISTENT',
        ]));

    $response2->assertViewHas('opdChits', fn ($c) => $c->isEmpty());
});

it('filters by sex', function () {
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    // Male filter — sspPatient is male (sex=1)
    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'sex' => '1',
        ]));

    $response->assertViewHas('opdChits', fn ($c) => $c->count() === 1);

    // Female filter — should not find this chit
    $response2 = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'sex' => '0',
        ]));

    $response2->assertViewHas('opdChits', fn ($c) => $c->isEmpty());
});

it('filters by fee category', function () {
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    // Filter by OPD category — should match
    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'fee_category_id' => $this->opdCategory->id,
        ]));

    $response->assertViewHas('opdChits', fn ($c) => $c->count() === 1);

    // Filter by lab category — should not match chit
    $response2 = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'fee_category_id' => $this->labCategory->id,
        ]));

    $response2->assertViewHas('opdChits', fn ($c) => $c->isEmpty());
});

it('calculates grand summary correctly', function () {
    Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'actual_amount' => 50.00,
        'ipd_opd' => 1,
        'government_non_gov' => 1,
        'government_department_id' => 95,
    ]);

    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'actual_total_amount' => 200.00,
    ]);

    PatientTest::create([
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->labFeeType->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'actual_total_amount' => 200.00,
        'status' => 'Normal',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

    $response->assertViewHas('summary', function ($summary) {
        return (float) $summary['grand_actual_amount'] === 250.00
            && (float) $summary['grand_charged_amount'] === 0.00
            && (float) $summary['grand_claimable_amount'] === 250.00;
    });
});

it('filters ipd by ward', function () {
    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'actual_total_amount' => 200.00,
    ]);

    Admission::create([
        'user_id' => $this->user->id,
        'invoice_id' => $invoice->id,
        'patient_id' => $this->sspPatient->id,
        'government_department_id' => 95,
        'actual_total_amount' => 200.00,
        'unit_ward' => 'Surgical Ward',
        'status' => 'No',
    ]);

    PatientTest::create([
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->labFeeType->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'actual_total_amount' => 200.00,
        'status' => 'Normal',
    ]);

    // Filter by Surgical Ward — should match
    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'unit_ward' => 'Surgical Ward',
        ]));

    $response->assertViewHas('ipdInvoices', fn ($i) => $i->count() === 1);

    // Filter by non-matching ward
    $response2 = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'unit_ward' => 'Medical Ward',
        ]));

    $response2->assertViewHas('ipdInvoices', fn ($i) => $i->isEmpty());
});

it('provides filter dropdown data', function () {
    $response = $this->actingAs($this->user)
        ->get(route('reports.ssp.claims'));

    $response->assertSuccessful();
    $response->assertViewHas('departments');
    $response->assertViewHas('feeCategories');
    $response->assertViewHas('users');
    $response->assertViewHas('admissionWards');
});
