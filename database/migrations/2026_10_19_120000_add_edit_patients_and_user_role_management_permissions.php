<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const GUARDS = ['web', 'sanctum'];

    /**
     * "edit patients" is granted to Administrator and Super-Admin only.
     * The user / role management permissions are only created when missing
     * (never handed to any role), so existing access is left untouched.
     */
    public function up(): void
    {
        $now = now();

        foreach (self::GUARDS as $guard) {
            foreach (['edit patients', 'create users', 'edit users', 'delete users', 'create roles', 'edit roles', 'delete roles',
                'create departments', 'edit departments', 'create government departments', 'edit government departments', 'create fee types', 'edit fee types'] as $name) {
                $exists = DB::table('permissions')->where('name', $name)->where('guard_name', $guard)->exists();

                if (! $exists) {
                    DB::table('permissions')->insert([
                        'name' => $name,
                        'guard_name' => $guard,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $permissionId = DB::table('permissions')
                ->where('name', 'edit patients')
                ->where('guard_name', $guard)
                ->value('id');

            $roleIds = DB::table('roles')
                ->where('guard_name', $guard)
                ->whereIn('name', ['Administrator', 'Super-Admin'])
                ->pluck('id');

            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->where('name', 'edit patients')->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
