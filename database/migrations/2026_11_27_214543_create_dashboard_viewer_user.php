<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the "Front Desk Viewer" role and the Dashboard Viewer user: view-only access to the
 * management dashboard (today only, without the year-on-year chart and the month projection),
 * patients, and chits / invoices history. The permissions are given to the user directly, so any
 * of them can be ticked on or off later from Users > Edit. Skips everything if the user exists.
 */
return new class extends Migration
{
    private const EMAIL = 'dashboard@aims.com';

    private const ROLE = 'Front Desk Viewer';

    private const GUARD = 'sanctum';

    private const PERMISSIONS = [
        'view dashboard',
        'view admin dashboard',
        'view patients',
        'view chits',
        'view invoices',
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
    ];

    public function up(): void
    {
        $now = now();

        DB::table('roles')->insertOrIgnore(['name' => self::ROLE, 'guard_name' => self::GUARD, 'created_at' => $now, 'updated_at' => $now]);

        if (DB::table('users')->whereRaw('LOWER(email) = ?', [self::EMAIL])->exists()) {
            return;
        }

        $userId = DB::table('users')->insertGetId([
            'name' => 'Dashboard Viewer',
            'email' => self::EMAIL,
            'password' => Hash::make('aims@2026'),
            'email_verified_at' => $now,
            'status' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $roleId = DB::table('roles')->where('name', self::ROLE)->where('guard_name', self::GUARD)->value('id');
        DB::table('model_has_roles')->insertOrIgnore(['role_id' => $roleId, 'model_type' => User::class, 'model_id' => $userId]);

        $permissionIds = DB::table('permissions')->where('guard_name', self::GUARD)->whereIn('name', self::PERMISSIONS)->pluck('id');
        DB::table('model_has_permissions')->insertOrIgnore(
            $permissionIds->map(fn ($id) => ['permission_id' => $id, 'model_type' => User::class, 'model_id' => $userId])->all()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $userIds = DB::table('users')->whereRaw('LOWER(email) = ?', [self::EMAIL])->pluck('id');

        DB::table('model_has_permissions')->where('model_type', User::class)->whereIn('model_id', $userIds)->delete();
        DB::table('model_has_roles')->where('model_type', User::class)->whereIn('model_id', $userIds)->delete();
        DB::table('users')->whereIn('id', $userIds)->delete();

        $roleIds = DB::table('roles')->where('name', self::ROLE)->where('guard_name', self::GUARD)->pluck('id');
        if (! DB::table('model_has_roles')->whereIn('role_id', $roleIds)->exists()) {
            DB::table('role_has_permissions')->whereIn('role_id', $roleIds)->delete();
            DB::table('roles')->whereIn('id', $roleIds)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
