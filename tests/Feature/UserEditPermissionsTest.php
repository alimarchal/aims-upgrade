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
