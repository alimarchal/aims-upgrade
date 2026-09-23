<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('shows role-based permissions as checked in user edit screen', function () {
    $viewUsersPermission = Permission::findOrCreate('view users', 'sanctum');
    $returnInvoicesPermission = Permission::findOrCreate('return invoices', 'sanctum');

    $adminRole = Role::findOrCreate('Administrator', 'sanctum');
    $adminRole->givePermissionTo($viewUsersPermission);

    $targetRole = Role::findOrCreate('Front Desk/Receptionist', 'sanctum');
    $targetRole->givePermissionTo($returnInvoicesPermission);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $targetUser = User::factory()->create();
    $targetUser->assignRole($targetRole);

    $this->actingAs($admin)
        ->get(route('users.edit', $targetUser))
        ->assertOk()
        ->assertViewHas('userPermissions', function (array $userPermissions) use ($returnInvoicesPermission) {
            return in_array($returnInvoicesPermission->id, $userPermissions, true);
        });
});

it('removes the dashboard permission from a front desk user when unchecked', function () {
    $viewUsersPermission = Permission::findOrCreate('view users', 'sanctum');
    $viewDashboardPermission = Permission::findOrCreate('view dashboard', 'sanctum');
    $viewChitsPermission = Permission::findOrCreate('view chits', 'sanctum');

    $adminRole = Role::findOrCreate('Administrator', 'sanctum');
    $adminRole->givePermissionTo($viewUsersPermission);

    $frontDeskRole = Role::findOrCreate('Front Desk/Receptionist', 'sanctum');
    $frontDeskRole->givePermissionTo($viewChitsPermission);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $targetUser = User::factory()->create();
    $targetUser->assignRole($frontDeskRole);
    $targetUser->givePermissionTo($viewDashboardPermission);

    $this->actingAs($admin)
        ->put(route('users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'role' => $frontDeskRole->id,
            'status' => 1,
            'permissions' => [$viewChitsPermission->id],
        ])
        ->assertRedirect(route('users.index'));

    expect($targetUser->fresh()->can('view dashboard'))->toBeFalse()
        ->and($targetUser->fresh()->can('view chits'))->toBeTrue();
});

it('gives new front desk users the dashboard permission directly', function () {
    $viewUsersPermission = Permission::findOrCreate('view users', 'sanctum');
    Permission::findOrCreate('view dashboard', 'sanctum');

    $adminRole = Role::findOrCreate('Administrator', 'sanctum');
    $adminRole->givePermissionTo($viewUsersPermission);

    $frontDeskRole = Role::findOrCreate('Front Desk/Receptionist', 'sanctum');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'New Receptionist',
            'email' => 'receptionist@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $frontDeskRole->id,
            'status' => 1,
        ])
        ->assertRedirect(route('users.index'));

    $newUser = User::where('email', 'receptionist@example.com')->first();

    expect($newUser->hasDirectPermission('view dashboard'))->toBeTrue()
        ->and($frontDeskRole->fresh()->hasPermissionTo('view dashboard'))->toBeFalse();
});
