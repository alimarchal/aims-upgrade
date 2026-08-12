<?php

use App\Models\FeeType;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('prevents users without return permission from adding return invoices to cart', function () {
    Permission::findOrCreate('view patients', 'sanctum');
    Permission::findOrCreate('return invoices', 'sanctum');

    $role = Role::findOrCreate('Front Desk/Receptionist', 'sanctum');
    $role->givePermissionTo('view patients');

    $user = User::factory()->create();
    $user->assignRole($role);

    $patient = Patient::factory()->create([
        'user_id' => $user->id,
        'government_non_gov' => 0,
        'years_months' => 'Year(s)',
    ]);

    $feeType = FeeType::factory()->create(['status' => 'Normal']);

    $this->actingAs($user)->post(route('patient.add-to-cart', $patient), [
        'patient_id' => $patient->id,
        'fee_type_id' => $feeType->id,
        'status' => 'Return',
    ])->assertForbidden();

    $this->assertDatabaseMissing('patient_test_carts', [
        'patient_id' => $patient->id,
        'fee_type_id' => $feeType->id,
        'status' => 'Return',
    ]);
});

it('allows users with return permission to add return invoices to cart', function () {
    Permission::findOrCreate('view patients', 'sanctum');
    Permission::findOrCreate('return invoices', 'sanctum');

    $role = Role::findOrCreate('Front Desk/Receptionist', 'sanctum');
    $role->givePermissionTo(['view patients', 'return invoices']);

    $user = User::factory()->create();
    $user->assignRole($role);

    $patient = Patient::factory()->create([
        'user_id' => $user->id,
        'government_non_gov' => 0,
        'years_months' => 'Year(s)',
    ]);

    $feeType = FeeType::factory()->create(['status' => 'Normal']);

    $this->actingAs($user)->post(route('patient.add-to-cart', $patient), [
        'patient_id' => $patient->id,
        'fee_type_id' => $feeType->id,
        'status' => 'Return',
    ])->assertRedirect(route('patient.proceed', $patient));

    $this->assertDatabaseHas('patient_test_carts', [
        'patient_id' => $patient->id,
        'fee_type_id' => $feeType->id,
        'status' => 'Return',
    ]);
});
