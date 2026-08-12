<?php

namespace Database\Seeders;

use App\Models\Disease;
use Illuminate\Database\Seeder;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = json_decode(file_get_contents(database_path('seeders/data/diseases.json')), true);
        foreach ($diseases as $disease) {
            Disease::updateOrCreate(['id' => $disease[0]], [
                'name' => $disease[1],
                'code' => $disease[2],
                'description' => $disease[3],
                'is_active' => $disease[4],
            ]);
        }
    }
}
