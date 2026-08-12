<?php

namespace Database\Seeders;

use App\Models\GovernmentDepartment;
use Illuminate\Database\Seeder;

class GovernmentDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = json_decode(file_get_contents(database_path('seeders/data/government_departments.json')), true);

        foreach ($departments as $dept) {
            GovernmentDepartment::updateOrCreate(['id' => $dept[0]], [
                'name' => $dept[1],
            ]);
        }
    }
}
