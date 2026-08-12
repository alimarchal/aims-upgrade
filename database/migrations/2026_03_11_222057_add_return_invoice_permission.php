<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $permissionName = 'return invoices';
        $guards = ['web', 'sanctum'];

        foreach ($guards as $guard) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permissionName, 'guard_name' => $guard],
                ['name' => $permissionName, 'guard_name' => $guard, 'updated_at' => $now, 'created_at' => $now]
            );

            $permissionId = DB::table('permissions')
                ->where('name', $permissionName)
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
                ], [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionName = 'return invoices';

        $permissionIds = DB::table('permissions')
            ->where('name', $permissionName)
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->delete();
        }
    }
};
