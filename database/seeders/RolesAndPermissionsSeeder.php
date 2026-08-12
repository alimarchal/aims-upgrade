<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = User::getDefaultGuardName();
        $guards = [$guard];

        // Granular permissions for middleware-based authorization
        $permissions = [
            // Dashboard
            'view dashboard',
            'view dashboard statistics',

            // Patients
            'view patients',
            'create patients',
            'edit patients',
            'delete patients',

            // Chits/OPD
            'view chits',
            'create chits',
            'edit chits',
            'delete chits',

            // Invoices
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'return invoices',

            // Admissions
            'view admissions',
            'create admissions',
            'edit admissions',
            'delete admissions',

            // Reports
            'view reports',
            'view opd reports',
            'view daily reports',
            'view monthly income reports',
            'view department reports',
            'view admission reports',
            'view emergency reports',
            'view ssp reports',

            // Departments
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',

            // Government Departments
            'view government departments',
            'create government departments',
            'edit government departments',
            'delete government departments',

            // Fee Types
            'view fee types',
            'create fee types',
            'edit fee types',
            'delete fee types',

            // Fee Categories
            'view fee categories',
            'create fee categories',
            'edit fee categories',
            'delete fee categories',

            // Users
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Roles & Permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'manage permissions',
            'assign permissions',

            // System Data
            'view diseases',
            'manage diseases',
            'view districts',
            'manage districts',
            'view tehsils',
            'manage tehsils',
            'view admission wards',
            'manage admission wards',
        ];

        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['name' => $permission, 'guard_name' => $guard],
                    ['name' => $permission, 'guard_name' => $guard]
                );
            }
        }

        $rolesMap = [
            ['id' => 1, 'name' => 'Administrator'],
            ['id' => 12, 'name' => 'Auditor'],
            ['id' => 3, 'name' => 'Doctor/Physician'],
            ['id' => 2, 'name' => 'Front Desk/Receptionist'],
            ['id' => 8, 'name' => 'Insurance Coordinator/Billing Specialist'],
            ['id' => 11, 'name' => 'IT Support/System Administrator'],
            ['id' => 5, 'name' => 'Laboratory Technician'],
            ['id' => 10, 'name' => 'Manager/Executive'],
            ['id' => 4, 'name' => 'Nurse'],
            ['id' => 9, 'name' => 'Patient/Portal User'],
            ['id' => 6, 'name' => 'Pharmacist'],
            ['id' => 7, 'name' => 'Radiologist/Imaging Technician'],
            ['id' => 13, 'name' => 'Super-Admin'],
        ];

        foreach ($guards as $guard) {
            foreach ($rolesMap as $role) {
                $r = Role::updateOrCreate(
                    ['name' => $role['name'], 'guard_name' => $guard],
                    ['name' => $role['name'], 'guard_name' => $guard]
                );

                // Super-Admin gets all permissions
                if ($role['name'] === 'Super-Admin') {
                    $r->givePermissionTo(Permission::where('guard_name', $guard)->get());
                }

                // Administrator gets only dashboard and reports access
                if ($role['name'] === 'Administrator') {
                    $adminPermissions = [
                        'view dashboard',
                        'view dashboard statistics',
                        'view patients',
                        'view reports',
                        'view opd reports',
                        'view daily reports',
                        'view monthly income reports',
                        'view department reports',
                        'view admission reports',
                        'view emergency reports',
                        'view ssp reports',
                    ];
                    $r->givePermissionTo(
                        Permission::where('guard_name', $guard)
                            ->whereIn('name', $adminPermissions)
                            ->get()
                    );
                }

                // Front Desk/Receptionist gets default permissions
                if ($role['name'] === 'Front Desk/Receptionist') {
                    $frontDeskPermissions = [
                        'create admissions',
                        'create chits',
                        'create invoices',
                        'create patients',
                        'view chits',
                        'view admissions',
                        'view dashboard',
                        'view invoices',
                        'view patients',
                    ];
                    $r->givePermissionTo(
                        Permission::where('guard_name', $guard)
                            ->whereIn('name', $frontDeskPermissions)
                            ->get()
                    );
                }
            }
        }
    }
}
