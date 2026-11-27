<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds one permission per management-dashboard chart / section plus "view dashboard filters".
 * Administrator and Super-Admin (and anybody holding "view admin dashboard" directly) receive all
 * of them, so everybody who sees the dashboard today keeps seeing exactly the same. Only inserts.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'view dashboard filters',
        'view dashboard highlights',
        'view dashboard key numbers',
        'view dashboard insights',
        'view dashboard daily chits chart',
        'view dashboard daily revenue chart',
        'view dashboard revenue race chart',
        'view dashboard entitled vs paid chart',
        'view dashboard control chart',
        'view dashboard government vs hif share chart',
        'view dashboard last 12 months chart',
        'view dashboard year on year revenue chart',
        'view dashboard services revenue chart',
        'view dashboard department revenue chart',
        'view dashboard heat map chart',
        'view dashboard busiest hours chart',
        'view dashboard busiest weekdays chart',
        'view dashboard weekday variation chart',
        'view dashboard day of month chart',
        'view dashboard opd by department chart',
        'view dashboard specialist vs general chart',
        'view dashboard service categories chart',
        'view dashboard operation theatre chart',
        'view dashboard radiology chart',
        'view dashboard employers chart',
        'view dashboard gender chart',
        'view dashboard age pyramid chart',
        'view dashboard admissions chart',
        'view dashboard wards chart',
        'view dashboard month projection',
    ];

    private const ROLES = ['Super-Admin', 'Administrator'];

    private const GUARDS = ['web', 'sanctum'];

    public function up(): void
    {
        $now = now();

        foreach (self::GUARDS as $guard) {
            foreach (self::PERMISSIONS as $name) {
                DB::table('permissions')->insertOrIgnore(['name' => $name, 'guard_name' => $guard, 'created_at' => $now, 'updated_at' => $now]);
            }

            $permissionIds = DB::table('permissions')->where('guard_name', $guard)->whereIn('name', self::PERMISSIONS)->pluck('id');

            foreach (DB::table('roles')->where('guard_name', $guard)->whereIn('name', self::ROLES)->pluck('id') as $roleId) {
                DB::table('role_has_permissions')->insertOrIgnore(
                    $permissionIds->map(fn ($id) => ['permission_id' => $id, 'role_id' => $roleId])->all()
                );
            }

            $adminDashboardId = DB::table('permissions')->where('guard_name', $guard)->where('name', 'view admin dashboard')->value('id');
            $directUserIds = $adminDashboardId
                ? DB::table('model_has_permissions')->where('permission_id', $adminDashboardId)->where('model_type', User::class)->pluck('model_id')
                : collect();

            foreach ($directUserIds as $userId) {
                DB::table('model_has_permissions')->insertOrIgnore(
                    $permissionIds->map(fn ($id) => ['permission_id' => $id, 'model_type' => User::class, 'model_id' => $userId])->all()
                );
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', self::PERMISSIONS)->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
