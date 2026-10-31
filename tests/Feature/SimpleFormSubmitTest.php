<?php

use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view fee types', 'edit fee types'] as $name) {
        Permission::findOrCreate($name, 'sanctum');
    }
    Role::findOrCreate('Administrator', 'sanctum')->syncPermissions(['view fee types', 'edit fee types']);
});

test('the fee edit form confirm button submits the form element, not the button', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $fee = FeeType::factory()->create(['fee_category_id' => FeeCategory::factory()->create()->id, 'status' => 'Normal']);

    $html = $this->actingAs($admin)->get(route('feeType.edit', $fee))->assertOk()->getContent();

    expect($html)->toContain('this.$root.submit()')->not->toContain('this.$el.submit()');
});

test('an administrator can update a fee type', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $fee = FeeType::factory()->create(['fee_category_id' => FeeCategory::factory()->create()->id, 'status' => 'Normal']);

    $this->actingAs($admin)
        ->put(route('feeType.update', $fee), ['type' => 'Emergency', 'amount' => 50, 'hif' => 25])
        ->assertRedirect(route('feeType.index'));

    expect($fee->refresh())->type->toBe('Emergency')->hif->toEqual(25);
});

test('the role and permission forms confirm button submits the form element', function () {
    foreach (['view roles', 'create roles', 'edit roles', 'manage permissions'] as $name) {
        Permission::findOrCreate($name, 'sanctum');
    }
    $role = Role::findOrCreate('Administrator', 'sanctum');
    $role->givePermissionTo(['view roles', 'create roles', 'edit roles', 'manage permissions']);
    $admin = User::factory()->create();
    $admin->assignRole('Administrator');

    foreach (['roles.create', 'permissions.create'] as $routeName) {
        $html = $this->actingAs($admin)->get(route($routeName))->assertOk()->getContent();

        expect($html)->toContain('this.$root.submit()')->not->toContain('this.$el.submit()');
    }
});
