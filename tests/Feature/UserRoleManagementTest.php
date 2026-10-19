<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view users', 'create users', 'edit users', 'delete users', 'view roles', 'create roles', 'edit roles', 'delete roles', 'view patients', 'edit patients'] as $name) {
        Permission::findOrCreate($name, 'sanctum');
    }

    Role::findOrCreate('Super-Admin', 'sanctum')->syncPermissions(Permission::where('guard_name', 'sanctum')->get());
    Role::findOrCreate('Front Desk/Receptionist', 'sanctum')->syncPermissions(['view patients']);
    Role::findOrCreate('Viewer', 'sanctum')->syncPermissions(['view users']);
});

function superAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('Super-Admin');

    return $user;
}

test('super admin can create a user with role and extra permission', function () {
    $frontDesk = Role::findByName('Front Desk/Receptionist', 'sanctum');
    $edit = Permission::findByName('edit patients', 'sanctum');

    $this->actingAs(superAdmin())->post(route('users.store'), [
        'name' => 'New Person',
        'email' => 'new.person@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'status' => 1,
        'roles' => [$frontDesk->id],
        'permissions' => [$edit->id],
    ])->assertRedirect(route('users.index'));

    $created = User::where('email', 'new.person@example.com')->firstOrFail();

    expect($created->hasRole('Front Desk/Receptionist'))->toBeTrue()
        ->and($created->hasDirectPermission('edit patients'))->toBeTrue();
});

test('a user who can only view users cannot create, edit or delete', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    $other = User::factory()->create();

    $this->actingAs($viewer)->get(route('users.index'))->assertSuccessful();
    $this->actingAs($viewer)->get(route('users.create'))->assertForbidden();
    $this->actingAs($viewer)->get(route('users.edit', $other))->assertForbidden();
    $this->actingAs($viewer)->delete(route('users.destroy', $other))->assertForbidden();
});

test('only a super admin can assign the super admin role', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Viewer');
    $editor->givePermissionTo(['view users', 'create users']);

    $this->actingAs($editor)->post(route('users.store'), [
        'name' => 'Sneaky',
        'email' => 'sneaky@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'status' => 1,
        'roles' => [Role::findByName('Super-Admin', 'sanctum')->id],
    ])->assertSessionHasErrors('roles');

    expect(User::where('email', 'sneaky@example.com')->exists())->toBeFalse();
});

test('a user cannot delete their own account', function () {
    $admin = superAdmin();

    $this->actingAs($admin)->delete(route('users.destroy', $admin))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('error');

    expect(User::find($admin->id))->not->toBeNull();
});

test('editing a user keeps role permissions inherited and does not copy them as direct', function () {
    $target = User::factory()->create();
    $target->assignRole('Front Desk/Receptionist');
    $frontDesk = Role::findByName('Front Desk/Receptionist', 'sanctum');

    $this->actingAs(superAdmin())->put(route('users.update', $target), [
        'name' => $target->name,
        'email' => $target->email,
        'status' => 1,
        'roles' => [$frontDesk->id],
    ])->assertRedirect(route('users.index'));

    expect($target->fresh()->getDirectPermissions())->toHaveCount(0)
        ->and($target->fresh()->hasPermissionTo('view patients'))->toBeTrue();
});

test('deleting a role keeps its permissions', function () {
    $role = Role::findOrCreate('Temp Role', 'sanctum');
    $role->givePermissionTo('view patients');

    $this->actingAs(superAdmin())->delete(route('roles.destroy', $role))->assertRedirect(route('roles.index'));

    expect(Role::where('name', 'Temp Role')->exists())->toBeFalse()
        ->and(Permission::where('name', 'view patients')->exists())->toBeTrue();
});

test('a role that still has users cannot be deleted', function () {
    $user = User::factory()->create();
    $user->assignRole('Viewer');

    $this->actingAs(superAdmin())->delete(route('roles.destroy', Role::findByName('Viewer', 'sanctum')))
        ->assertSessionHas('error');

    expect(DB::table('roles')->where('name', 'Viewer')->exists())->toBeTrue();
});

test('roles can be created from the form with grouped permissions', function () {
    $permission = Permission::findByName('view patients', 'sanctum');

    $this->actingAs(superAdmin())->post(route('roles.store'), [
        'name' => 'Records Clerk',
        'permissions' => [$permission->id],
    ])->assertRedirect(route('roles.index'));

    expect(Role::findByName('Records Clerk', 'sanctum')->hasPermissionTo('view patients'))->toBeTrue();
});

test('user and role screens render', function () {
    $admin = superAdmin();
    $target = User::factory()->create();

    foreach ([route('users.index'), route('users.create'), route('users.edit', $target), route('users.show', $target), route('roles.index'), route('roles.create')] as $url) {
        $this->actingAs($admin)->get($url)->assertSuccessful();
    }
});

test('administrator cannot delete users, roles or permissions even with delete permissions', function () {
    $adminRole = Role::findOrCreate('Administrator', 'sanctum');
    $adminRole->syncPermissions(Permission::where('guard_name', 'sanctum')->get());

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $victim = User::factory()->create();
    $role = Role::findOrCreate('Temp Role', 'sanctum');

    $this->actingAs($admin)->delete(route('users.destroy', $victim))->assertForbidden();
    $this->actingAs($admin)->delete(route('roles.destroy', $role))->assertForbidden();
    $this->actingAs($admin)->delete(route('permissions.destroy', Permission::findByName('view patients', 'sanctum')))->assertForbidden();

    $this->actingAs($admin)->get(route('users.index'))
        ->assertSuccessful()
        ->assertDontSee('Delete user');

    expect(User::find($victim->id))->not->toBeNull();
});

test('super admin sees the delete confirmation on the users list', function () {
    User::factory()->create();

    $this->actingAs(superAdmin())->get(route('users.index'))->assertSuccessful()->assertSee('Delete user');
});
