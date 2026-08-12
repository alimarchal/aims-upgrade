<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\GovernmentDepartment;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $gov_depts = json_decode(file_get_contents(database_path('seeders/data/government_departments.json')), true);
        foreach ($gov_depts as $dept) {
            GovernmentDepartment::updateOrCreate(['id' => $dept[0]], [
                'name' => $dept[1],
            ]);
        }

        $departments = json_decode(file_get_contents(database_path('seeders/data/departments.json')), true);
        foreach ($departments as $dept) {
            Department::updateOrCreate(['id' => $dept[0]], [
                'name' => $dept[1],
                'daily_patient_limit' => $dept[2],
                'category' => $dept[3],
            ]);
        }
    }
}
