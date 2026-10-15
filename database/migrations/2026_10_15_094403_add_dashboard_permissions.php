<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds the "view admin dashboard" and "view front desk dashboard" permissions and gives them to the
 * Super-Admin / Administrator and Front Desk/Receptionist roles. Nothing is taken away from anybody;
 * every other role keeps the classic dashboard. Only inserts, so it is quick and safe on a live system.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> permission => roles that receive it */
    private const GRANTS = [
        'view admin dashboard' => ['Super-Admin', 'Administrator'],
        'view front desk dashboard' => ['Front Desk/Receptionist'],
    ];

    private const GUARDS = ['web', 'sanctum'];

    public function up(): void
    {
        $now = now();

        foreach (self::GRANTS as $permissionName => $roleNames) {
            foreach (self::GUARDS as $guard) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $permissionName, 'guard_name' => $guard],
                    ['updated_at' => $now, 'created_at' => $now]
                );

                $permissionId = DB::table('permissions')->where('name', $permissionName)->where('guard_name', $guard)->value('id');

                foreach (DB::table('roles')->where('guard_name', $guard)->whereIn('name', $roleNames)->pluck('id') as $roleId) {
                    DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', array_keys(self::GRANTS))->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
