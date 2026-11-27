<?php

use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

test('patient history lists each invoice once, newest first', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Permission::findOrCreate('view patients', 'sanctum');
    $user = User::factory()->create();
    $user->givePermissionTo('view patients');
    $patient = Patient::factory()->create(['user_id' => $user->id, 'government_non_gov' => 0, 'years_months' => 'Year(s)']);
    $feeType = FeeType::factory()->create(['status' => 'Normal']);
    $addTest = fn (Invoice $invoice) => PatientTest::create([
        'patient_id' => $patient->id, 'fee_type_id' => $feeType->id, 'invoice_id' => $invoice->id, 'government_non_gov' => 0,
        'total_amount' => 100, 'hif_amount' => 0, 'govt_amount' => 100, 'actual_total_amount' => 100, 'status' => 'Normal',
    ]);

    $this->travelTo(now()->subDay());
    $olderInvoice = Invoice::create(['user_id' => $user->id, 'patient_id' => $patient->id, 'government_non_government' => 0]);
    $addTest($olderInvoice);
    $addTest($olderInvoice);
    $this->travelBack();
    $newerInvoice = Invoice::create(['user_id' => $user->id, 'patient_id' => $patient->id, 'government_non_government' => 0]);
    $addTest($newerInvoice);

    $response = $this->actingAs($user)->get(route('patient.history', $patient));

    $olderLink = route('patient.patient_invoice', [$patient->id, $olderInvoice->id]);
    $newerLink = route('patient.patient_invoice', [$patient->id, $newerInvoice->id]);
    $response->assertOk()->assertSeeInOrder([$newerLink, $olderLink], false);
    expect(substr_count($response->getContent(), $olderLink))->toBe(1);
});
