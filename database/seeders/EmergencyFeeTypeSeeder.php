<?php

namespace Database\Seeders;

use App\Models\FeeCategory;
use App\Models\FeeType;
use Illuminate\Database\Seeder;

class EmergencyFeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emergencyCategory = FeeCategory::query()->firstOrCreate(
            ['name' => 'Emergency'],
            ['type' => 'General']
        );

        $emergencyFeeTypes = [
            'Primary PCI / Rescue PCI',
            'TPM (temporary pacemaker)',
            'Pericardiocentesis',
        ];

        foreach ($emergencyFeeTypes as $emergencyFeeType) {
            FeeType::query()->updateOrCreate(
                [
                    'fee_category_id' => $emergencyCategory->id,
                    'type' => $emergencyFeeType,
                    'status' => 'Normal',
                ],
                [
                    'amount' => 0,
                    'hif' => 0,
                ]
            );
        }
    }
}
