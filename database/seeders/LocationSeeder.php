<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Tehsil;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $districts = json_decode(file_get_contents(database_path('seeders/data/districts.json')), true);
        foreach ($districts as $district) {
            District::updateOrCreate(['id' => $district[0]], [
                'name' => $district[1],
            ]);
        }

        $tehsils = json_decode(file_get_contents(database_path('seeders/data/tehsils.json')), true);
        foreach ($tehsils as $tehsil) {
            Tehsil::updateOrCreate(['id' => $tehsil[0]], [
                'name' => $tehsil[1],
                'district_id' => $tehsil[2],
            ]);
        }
    }
}
