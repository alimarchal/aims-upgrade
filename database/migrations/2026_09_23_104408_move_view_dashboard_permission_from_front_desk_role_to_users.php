<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION_NAME = 'view dashboard';

    private const ROLE_NAME = 'Front Desk/Receptionist';

    /**
     * Move "view dashboard" from the Front Desk role to each Front Desk user as a direct
     * permission, so it can be removed per user from the user edit screen.
     */
    public function up(): void
    {
        foreach ($this->rolePermissionPairs() as [$roleId, $permissionId]) {
            $userIds = DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_type', User::class)
                ->pluck('model_id');

            foreach ($userIds as $userId) {
                DB::table('model_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'model_type' => User::class,
                    'model_id' => $userId,
                ]);
            }

            DB::table('role_has_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->rolePermissionPairs() as [$roleId, $permissionId]) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);

            $userIds = DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_type', User::class)
                ->pluck('model_id');

            DB::table('model_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('model_type', User::class)
                ->whereIn('model_id', $userIds)
                ->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * @return array<int, array{0: int, 1: int}>
     */
    private function rolePermissionPairs(): array
    {
        $pairs = [];

        foreach (['web', 'sanctum'] as $guard) {
            $roleId = DB::table('roles')
                ->where('name', self::ROLE_NAME)
                ->where('guard_name', $guard)
                ->value('id');

            $permissionId = DB::table('permissions')
                ->where('name', self::PERMISSION_NAME)
                ->where('guard_name', $guard)
                ->value('id');

            if ($roleId && $permissionId) {
                $pairs[] = [$roleId, $permissionId];
            }
        }

        return $pairs;
    }
};
