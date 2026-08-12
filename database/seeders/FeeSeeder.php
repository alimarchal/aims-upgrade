<?php

namespace Database\Seeders;

use App\Models\FeeCategory;
use App\Models\FeeType;
use Illuminate\Database\Seeder;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = json_decode(file_get_contents(database_path('seeders/data/fee_categories.json')), true);
        foreach ($categories as $cat) {
            FeeCategory::updateOrCreate(['id' => $cat[0]], [
                'name' => $cat[1],
                'type' => $cat[2],
            ]);
        }

        $types = json_decode(file_get_contents(database_path('seeders/data/fee_types.json')), true);
        foreach ($types as $type) {
            FeeType::updateOrCreate(['id' => $type[0]], [
                'fee_category_id' => $type[1],
                'type' => $type[2],
                'amount' => $type[3],
                'hif' => $type[4],
                'status' => $type[5],
            ]);
        }
    }
}
