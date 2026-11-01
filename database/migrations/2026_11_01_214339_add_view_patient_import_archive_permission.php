<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION = 'view patient import archive';

    private const GUARDS = ['web', 'sanctum'];

    public function up(): void
    {
        foreach (self::GUARDS as $guard) {
            DB::table('permissions')->updateOrInsert(
                ['name' => self::PERMISSION, 'guard_name' => $guard],
                ['created_at' => now(), 'updated_at' => now()],
            );

            $permissionId = DB::table('permissions')
                ->where('name', self::PERMISSION)
                ->where('guard_name', $guard)
                ->value('id');

            $roleIds = DB::table('roles')
                ->where('guard_name', $guard)
                ->where('name', 'Super-Admin')
                ->pluck('id');

            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('name', self::PERMISSION)
            ->whereIn('guard_name', self::GUARDS)
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
