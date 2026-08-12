<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            //DepartmentSeeder::class,
            //FeeSeeder::class,
            //EmergencyFeeTypeSeeder::class,
            //DiseaseSeeder::class,
            //LocationSeeder::class,
            //AdmissionWardSeeder::class,
            //PatientAttendantRelationSeeder::class,
        ]);
    }
}
