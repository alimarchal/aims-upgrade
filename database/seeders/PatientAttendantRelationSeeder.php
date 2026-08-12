<?php

namespace Database\Seeders;

use App\Models\PatientAttendantRelation;
use Illuminate\Database\Seeder;

class PatientAttendantRelationSeeder extends Seeder
{
    public function run(): void
    {
        $relations = [
            ['id' => 1, 'name' => 'Father'],
            ['id' => 2, 'name' => 'Mother'],
            ['id' => 3, 'name' => 'Spouse'],
            ['id' => 4, 'name' => 'Brother'],
            ['id' => 5, 'name' => 'Sister'],
            ['id' => 6, 'name' => 'Son'],
            ['id' => 7, 'name' => 'Daughter'],
            ['id' => 8, 'name' => 'Uncle'],
            ['id' => 9, 'name' => 'Aunt'],
            ['id' => 10, 'name' => 'Guardian'],
            ['id' => 11, 'name' => 'Friend'],
            ['id' => 12, 'name' => 'Other'],
        ];

        foreach ($relations as $relation) {
            PatientAttendantRelation::updateOrCreate(['id' => $relation['id']], [
                'name' => $relation['name'],
            ]);
        }
    }
}
