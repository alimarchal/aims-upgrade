<?php

use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $permissions = [
        'view patients',
        'view dashboard',
        'view chits',
        'view invoices',
        'view dashboard statistics',
        'create invoices',
        'create chits',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'sanctum');
    }

    $role = Role::findOrCreate('Super-Admin', 'sanctum');
    $role->syncPermissions($permissions);
});

test('super admin can see all action cards on patient actions page', function () {
    $user = User::factory()->create();
    $user->assignRole('Super-Admin');

    $patient = Patient::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get(route('patient.actions', $patient));

    $response->assertSuccessful()
        ->assertSeeText('Make Invoice')
        ->assertSeeText('New Chit')
        ->assertSeeText('Emergency');
});

test('super admin can see return option on patient proceed page', function () {
    $user = User::factory()->create();
    $user->assignRole('Super-Admin');

    $patient = Patient::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get(route('patient.proceed', $patient));

    $response->assertSuccessful()
        ->assertSee('Return');
});
