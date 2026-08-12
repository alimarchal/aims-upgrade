<?php

namespace Database\Seeders;

use App\Models\AdmissionWard;
use Illuminate\Database\Seeder;

class AdmissionWardSeeder extends Seeder
{
    public function run(): void
    {
        $wards = [
            ['id' => 1, 'name' => 'Male Medical Ward'],
            ['id' => 2, 'name' => 'Female Medical Ward'],
            ['id' => 3, 'name' => 'Pediatric Ward'],
            ['id' => 4, 'name' => 'Surgical Ward'],
            ['id' => 5, 'name' => 'ICU (Intensive Care Unit)'],
            ['id' => 6, 'name' => 'Labor/Delivery Ward'],
            ['id' => 7, 'name' => 'Emergency Ward'],
            ['id' => 8, 'name' => 'Isolation Ward'],
            ['id' => 9, 'name' => 'Gynae Ward'],
            ['id' => 10, 'name' => 'Orthopedic Ward'],
        ];

        foreach ($wards as $ward) {
            AdmissionWard::updateOrCreate(['id' => $ward['id']], [
                'name' => $ward['name'],
            ]);
        }
    }
}
