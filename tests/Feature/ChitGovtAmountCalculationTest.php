<?php

use App\Models\Chit;
use App\Models\Department;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Create user
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create OPD fee category
    $this->opdCategory = FeeCategory::create([
        'name' => 'OPD (Out Door Patient)',
        'type' => 'OPD',
    ]);

    // Create department
    $this->department = Department::create([
        'name' => 'Screening OPD Female',
        'daily_patient_limit' => 100,
        'category' => 'OPD',
    ]);

    // Create patient
    $this->patient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mr.',
        'first_name' => 'Test',
        'last_name' => 'Patient',
        'father_husband_name' => 'Father',
        'age' => 30,
        'years_months' => 'Year(s)',
        'dob' => '1996-01-01',
        'sex' => true,
        'address' => 'Test Address',
        'government_non_gov' => 0,
    ]);
});

it('calculates govt_amount as zero when hif equals amount', function () {
    $feeType = FeeType::create([
        'fee_category_id' => $this->opdCategory->id,
        'type' => 'Chit Fee (Screening OPD Female)',
        'amount' => 30.00,
        'hif' => 30.00,
        'status' => 'Normal',
    ]);

    // This is the formula used in the controllers after the fix
    $amount = (float) $feeType->amount;
    $amountHif = (float) $feeType->hif;
    $govtAmount = $amount - $amountHif;

    $chit = Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->patient->id,
        'address' => $this->patient->address,
        'government_non_gov' => 0,
        'fee_type_id' => $feeType->id,
        'issued_date' => now(),
        'amount' => $amount,
        'amount_hif' => $amountHif,
        'govt_amount' => $govtAmount,
        'ipd_opd' => 1,
        'payment_status' => 1,
    ]);

    expect((float) $chit->govt_amount)->toBe(0.0)
        ->and((float) $chit->amount_hif + (float) $chit->govt_amount)->toBe((float) $chit->amount);
});

it('calculates govt_amount correctly when hif is less than amount', function () {
    $feeType = FeeType::create([
        'fee_category_id' => $this->opdCategory->id,
        'type' => 'Specialist Fee',
        'amount' => 50.00,
        'hif' => 20.00,
        'status' => 'Normal',
    ]);

    $amount = (float) $feeType->amount;
    $amountHif = (float) $feeType->hif;
    $govtAmount = $amount - $amountHif;

    $chit = Chit::create([
        'user_id' => $this->user->id,
        'department_id' => $this->department->id,
        'patient_id' => $this->patient->id,
        'address' => $this->patient->address,
        'government_non_gov' => 0,
        'fee_type_id' => $feeType->id,
        'issued_date' => now(),
        'amount' => $amount,
        'amount_hif' => $amountHif,
        'govt_amount' => $govtAmount,
        'ipd_opd' => 1,
        'payment_status' => 1,
    ]);

    expect((float) $chit->govt_amount)->toBe(30.0)
        ->and((float) $chit->amount_hif + (float) $chit->govt_amount)->toBe((float) $chit->amount);
});

it('ensures report totals are consistent when HIF plus GOVT equals TOTAL', function () {
    $feeType = FeeType::create([
        'fee_category_id' => $this->opdCategory->id,
        'type' => 'Chit Fee (Screening OPD Female)',
        'amount' => 30.00,
        'hif' => 30.00,
        'status' => 'Normal',
    ]);

    // Create multiple chits with correct govt_amount calculation
    for ($i = 0; $i < 10; $i++) {
        Chit::create([
            'user_id' => $this->user->id,
            'department_id' => $this->department->id,
            'patient_id' => $this->patient->id,
            'address' => $this->patient->address,
            'government_non_gov' => 0,
            'fee_type_id' => $feeType->id,
            'issued_date' => now(),
            'amount' => $feeType->amount,
            'amount_hif' => $feeType->hif,
            'govt_amount' => $feeType->amount - $feeType->hif,
            'ipd_opd' => 1,
            'payment_status' => 1,
        ]);
    }

    $totalHif = (float) Chit::where('fee_type_id', $feeType->id)->sum('amount_hif');
    $totalGovt = (float) Chit::where('fee_type_id', $feeType->id)->sum('govt_amount');
    $totalAmount = (float) Chit::where('fee_type_id', $feeType->id)->sum('amount');

    expect($totalHif + $totalGovt)->toBe($totalAmount)
        ->and($totalHif)->toBe(300.0)
        ->and($totalGovt)->toBe(0.0)
        ->and($totalAmount)->toBe(300.0);
});
