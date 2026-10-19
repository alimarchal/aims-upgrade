<?php

use App\Models\Department;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\GovernmentDepartment;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $names = [];
    foreach (['departments', 'government departments', 'fee types'] as $subject) {
        foreach (['view', 'create', 'edit'] as $action) {
            $names[] = "{$action} {$subject}";
        }
    }
    foreach ($names as $name) {
        Permission::findOrCreate($name, 'sanctum');
    }

    Role::findOrCreate('Viewer', 'sanctum')->syncPermissions(array_filter($names, fn ($n) => str_starts_with($n, 'view ')));
    Role::findOrCreate('Administrator', 'sanctum')->syncPermissions($names);
    Role::findOrCreate('Super-Admin', 'sanctum')->syncPermissions($names);
});

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('a viewer sees the lists but no create or edit links and cannot open them directly', function () {
    $viewer = userWithRole('Viewer');
    $department = Department::factory()->create();
    $government = GovernmentDepartment::factory()->create();
    $category = FeeCategory::factory()->create();
    $fee = FeeType::factory()->create(['fee_category_id' => $category->id]);

    $this->actingAs($viewer)->get(route('department.index'))->assertSuccessful()
        ->assertDontSee(route('department.create'))->assertDontSee(route('department.edit', $department->id));
    $this->actingAs($viewer)->get(route('governmentDepartment.index'))->assertSuccessful()
        ->assertDontSee(route('governmentDepartment.create'))->assertDontSee(route('governmentDepartment.edit', $government->id));
    $this->actingAs($viewer)->get(route('feeType.index'))->assertSuccessful()
        ->assertDontSee(route('feeType.create'))->assertDontSee(route('feeType.edit', $fee->id));

    $this->actingAs($viewer)->get(route('department.create'))->assertForbidden();
    $this->actingAs($viewer)->get(route('department.edit', $department->id))->assertForbidden();
    $this->actingAs($viewer)->get(route('governmentDepartment.create'))->assertForbidden();
    $this->actingAs($viewer)->get(route('governmentDepartment.edit', $government->id))->assertForbidden();
    $this->actingAs($viewer)->get(route('feeType.create'))->assertForbidden();
    $this->actingAs($viewer)->get(route('feeType.edit', $fee->id))->assertForbidden();
});

test('administrator can open create and edit pages but cannot delete', function () {
    $admin = userWithRole('Administrator');
    $department = Department::factory()->create();
    $government = GovernmentDepartment::factory()->create();

    $this->actingAs($admin)->get(route('department.create'))->assertSuccessful();
    $this->actingAs($admin)->get(route('department.edit', $department->id))->assertSuccessful();
    $this->actingAs($admin)->get(route('governmentDepartment.create'))->assertSuccessful();
    $this->actingAs($admin)->get(route('governmentDepartment.edit', $government->id))->assertSuccessful();

    $this->actingAs($admin)->get(route('department.index'))->assertDontSee('Delete department');
    $this->actingAs($admin)->delete(route('department.destroy', $department->id))->assertForbidden();
    $this->actingAs($admin)->delete(route('governmentDepartment.destroy', $government->id))->assertForbidden();

    expect(Department::find($department->id))->not->toBeNull();
});

test('only super admin sees and can use delete', function () {
    $super = userWithRole('Super-Admin');
    $department = Department::factory()->create();

    $this->actingAs($super)->get(route('department.index'))->assertSuccessful()->assertSee('Delete department');
    $this->actingAs($super)->delete(route('department.destroy', $department->id))->assertRedirect(route('department.index'));

    expect(Department::find($department->id))->toBeNull();
});
