<?php

use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view patients', 'edit patients'] as $name) {
        Permission::findOrCreate($name, 'sanctum');
    }

    Role::findOrCreate('Front Desk/Receptionist', 'sanctum')->syncPermissions(['view patients']);
    Role::findOrCreate('Administrator', 'sanctum')->syncPermissions(['view patients', 'edit patients']);
    Role::findOrCreate('Super-Admin', 'sanctum')->syncPermissions(['view patients', 'edit patients']);
});

test('front desk cannot see or open the patient edit page', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');
    $patient = Patient::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('patient.index'))
        ->assertSuccessful()
        ->assertDontSee(route('patient.edit', $patient->id));

    $this->actingAs($user)->get(route('patient.edit', $patient->id))->assertForbidden();
    $this->actingAs($user)->put(route('patient.update', $patient->id), [])->assertForbidden();
});

test('administrator and super admin see the edit link and can open the page', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $patient = Patient::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('patient.index'))
        ->assertSuccessful()
        ->assertSee(route('patient.edit', $patient->id));

    $this->actingAs($user)->get(route('patient.edit', $patient->id))->assertSuccessful();
})->with(['Administrator', 'Super-Admin']);

test('a front desk user given edit patients directly can edit', function () {
    $user = User::factory()->create();
    $user->assignRole('Front Desk/Receptionist');
    $user->givePermissionTo('edit patients');
    $patient = Patient::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('patient.edit', $patient->id))->assertSuccessful();
});
