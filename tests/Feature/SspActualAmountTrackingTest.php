<?php

use App\Models\Admission;
use App\Models\Chit;
use App\Models\Department;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test-ssp@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create government departments (SSP is ID 95, regular govt is ID 1)
    DB::table('government_departments')->insertOrIgnore(['id' => 1, 'name' => 'Army', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('government_departments')->insertOrIgnore(['id' => 95, 'name' => 'SEHAT SAHULAT PROGRAM', 'created_at' => now(), 'updated_at' => now()]);

    $this->opdCategory = FeeCategory::create([
        'name' => 'OPD (Out Door Patient)',
        'type' => 'OPD',
    ]);

    $this->pathologyCategory = FeeCategory::create([
        'name' => 'Pathology',
        'type' => 'Pathology',
    ]);

    $this->department = Department::create([
        'name' => 'Specialist ENT',
        'daily_patient_limit' => 100,
        'category' => 'OPD',
    ]);

    $this->feeType = FeeType::create([
        'fee_category_id' => $this->opdCategory->id,
        'type' => 'Specialist ENT',
        'amount' => 50.00,
        'hif' => 20.00,
        'status' => 'Normal',
    ]);

    $this->labFeeType = FeeType::create([
        'fee_category_id' => $this->pathologyCategory->id,
        'type' => 'CBC',
        'amount' => 200.00,
        'hif' => 80.00,
        'status' => 'Normal',
    ]);

    $this->labFeeType2 = FeeType::create([
        'fee_category_id' => $this->pathologyCategory->id,
        'type' => 'Urine DR',
        'amount' => 150.00,
        'hif' => 60.00,
        'status' => 'Normal',
    ]);

    // SSP patient (government_department_id = 95)
    $this->sspPatient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mr.',
        'first_name' => 'SSP',
        'last_name' => 'Patient',
        'father_husband_name' => 'Father',
        'age' => 40,
        'years_months' => 'Year(s)',
        'dob' => '1986-01-01',
        'sex' => true,
        'address' => 'Test Address',
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'sehat_sahulat_visit_no' => 'SSP-V-001',
        'sehat_sahulat_patient_id' => 'SSP-P-001',
    ]);

    // Regular government patient (non-SSP)
    $this->govPatient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mrs.',
        'first_name' => 'Gov',
        'last_name' => 'Patient',
        'father_husband_name' => 'Father',
        'age' => 35,
        'years_months' => 'Year(s)',
        'dob' => '1991-01-01',
        'sex' => false,
        'address' => 'Test Address',
        'government_non_gov' => 1,
        'government_department_id' => 1,
        'government_card_no' => 'GOV-123',
        'designation' => 'Officer',
    ]);

    // Private patient
    $this->privatePatient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mr.',
        'first_name' => 'Private',
        'last_name' => 'Patient',
        'father_husband_name' => 'Father',
        'age' => 25,
        'years_months' => 'Year(s)',
        'dob' => '2001-01-01',
        'sex' => true,
        'address' => 'Test Address',
        'government_non_gov' => 0,
    ]);
});

/*
|--------------------------------------------------------------------------
| Chit Tests — SSP actual_amount tracking
|--------------------------------------------------------------------------
*/

it('stores actual_amount on chit for SSP patient', function () {
    $chit = Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->sspPatient->id,
        'address' => $this->sspPatient->address,
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'amount_hif' => 0,
        'govt_amount' => 0,
        'ipd_opd' => 1,
        'payment_status' => 1,
        'sehat_sahulat_visit_no' => 'SSP-V-001',
        'actual_amount' => $this->feeType->amount,
    ]);

    expect((float) $chit->amount)->toBe(0.0)
        ->and((float) $chit->amount_hif)->toBe(0.0)
        ->and((float) $chit->govt_amount)->toBe(0.0)
        ->and((float) $chit->actual_amount)->toBe(50.0);
});

it('stores actual_amount as zero on chit for non-SSP government patient', function () {
    $chit = Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->govPatient->id,
        'address' => $this->govPatient->address,
        'government_non_gov' => 1,
        'government_department_id' => 1,
        'government_card_no' => 'GOV-123',
        'designation' => 'Officer',
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => 0,
        'amount_hif' => 0,
        'govt_amount' => 0,
        'ipd_opd' => 1,
        'payment_status' => 1,
        'actual_amount' => 0,
    ]);

    expect((float) $chit->amount)->toBe(0.0)
        ->and((float) $chit->actual_amount)->toBe(0.0);
});

it('stores actual_amount as zero on chit for private patient', function () {
    $chit = Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->privatePatient->id,
        'address' => $this->privatePatient->address,
        'government_non_gov' => 0,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => $this->feeType->amount,
        'amount_hif' => $this->feeType->hif,
        'govt_amount' => $this->feeType->amount - $this->feeType->hif,
        'ipd_opd' => 1,
        'payment_status' => 1,
        'actual_amount' => 0,
    ]);

    expect((float) $chit->amount)->toBe(50.0)
        ->and((float) $chit->actual_amount)->toBe(0.0);
});

/*
|--------------------------------------------------------------------------
| Invoice & PatientTest Tests — SSP actual_total_amount tracking
|--------------------------------------------------------------------------
*/

it('stores actual_total_amount on patient_tests for SSP patient', function () {
    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
    ]);

    $pt1 = PatientTest::create([
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->labFeeType->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => $this->labFeeType->amount,
        'status' => 'Normal',
    ]);

    $pt2 = PatientTest::create([
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $this->labFeeType2->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => $this->labFeeType2->amount,
        'status' => 'Normal',
    ]);

    $actualTotal = (float) $pt1->actual_total_amount + (float) $pt2->actual_total_amount;

    $invoice->update([
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => $actualTotal,
    ]);

    expect((float) $invoice->total_amount)->toBe(0.0)
        ->and((float) $invoice->actual_total_amount)->toBe(350.0)
        ->and((float) $pt1->total_amount)->toBe(0.0)
        ->and((float) $pt1->actual_total_amount)->toBe(200.0)
        ->and((float) $pt2->total_amount)->toBe(0.0)
        ->and((float) $pt2->actual_total_amount)->toBe(150.0);
});

it('stores actual_total_amount as zero on patient_tests for non-SSP government patient', function () {
    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->govPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 1,
        'government_card_no' => 'GOV-123',
    ]);

    $pt = PatientTest::create([
        'patient_id' => $this->govPatient->id,
        'fee_type_id' => $this->labFeeType->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => 1,
        'government_department_id' => 1,
        'government_card_no' => 'GOV-123',
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => 0,
        'status' => 'Normal',
    ]);

    expect((float) $pt->total_amount)->toBe(0.0)
        ->and((float) $pt->actual_total_amount)->toBe(0.0);
});

/*
|--------------------------------------------------------------------------
| Admission Tests — SSP tracking
|--------------------------------------------------------------------------
*/

it('stores government_department_id and actual_total_amount on admission for SSP patient', function () {
    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => 350.00,
    ]);

    $admission = Admission::create([
        'user_id' => $this->user->id,
        'invoice_id' => $invoice->id,
        'patient_id' => $this->sspPatient->id,
        'government_department_id' => 95,
        'actual_total_amount' => 350.00,
        'unit_ward' => 'Medical Ward',
        'disease' => 'Flu',
        'category' => 'General',
    ]);

    expect((int) $admission->government_department_id)->toBe(95)
        ->and((float) $admission->actual_total_amount)->toBe(350.0);
});

it('stores actual_total_amount as zero on admission for non-SSP patient', function () {
    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->govPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 1,
        'government_card_no' => 'GOV-123',
    ]);

    $admission = Admission::create([
        'user_id' => $this->user->id,
        'invoice_id' => $invoice->id,
        'patient_id' => $this->govPatient->id,
        'government_department_id' => 1,
        'actual_total_amount' => 0,
        'unit_ward' => 'Surgical Ward',
    ]);

    expect((float) $admission->actual_total_amount)->toBe(0.0)
        ->and((int) $admission->government_department_id)->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Return Fee Tests — SSP actual_total_amount for returns
|--------------------------------------------------------------------------
*/

it('stores negative actual_total_amount for SSP return test', function () {
    $returnFeeType = FeeType::create([
        'fee_category_id' => $this->pathologyCategory->id,
        'type' => 'Return CBC',
        'amount' => 200.00,
        'hif' => 80.00,
        'status' => 'Return Fee',
    ]);

    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->sspPatient->id,
        'government_non_government' => 1,
        'government_department_id' => 95,
    ]);

    // Normal test
    $pt1 = PatientTest::create([
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

    // Return test
    $pt2 = PatientTest::create([
        'patient_id' => $this->sspPatient->id,
        'fee_type_id' => $returnFeeType->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => 1,
        'government_department_id' => 95,
        'total_amount' => 0,
        'hif_amount' => 0,
        'govt_amount' => 0,
        'actual_total_amount' => -200.00,
        'status' => 'Return',
    ]);

    $actualTotal = (float) $pt1->actual_total_amount + (float) $pt2->actual_total_amount;

    expect($actualTotal)->toBe(0.0)
        ->and((float) $pt1->actual_total_amount)->toBe(200.0)
        ->and((float) $pt2->actual_total_amount)->toBe(-200.0);
});

/*
|--------------------------------------------------------------------------
| Existing data backward compatibility
|--------------------------------------------------------------------------
*/

it('defaults actual_amount to zero for chits without explicit value', function () {
    $chit = Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->privatePatient->id,
        'address' => $this->privatePatient->address,
        'government_non_gov' => 0,
        'fee_type_id' => $this->feeType->id,
        'issued_date' => now(),
        'amount' => $this->feeType->amount,
        'amount_hif' => $this->feeType->hif,
        'govt_amount' => $this->feeType->amount - $this->feeType->hif,
        'ipd_opd' => 1,
        'payment_status' => 1,
    ]);

    // actual_amount should default to 0 when not explicitly set
    $freshChit = Chit::find($chit->id);
    expect((float) $freshChit->actual_amount)->toBe(0.0);
});

it('defaults actual_total_amount to zero for invoices without explicit value', function () {
    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->privatePatient->id,
        'government_non_government' => 0,
        'total_amount' => 200.00,
        'hif_amount' => 80.00,
        'govt_amount' => 120.00,
    ]);

    $freshInvoice = Invoice::find($invoice->id);
    expect((float) $freshInvoice->actual_total_amount)->toBe(0.0);
});
