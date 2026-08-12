<?php

use App\Models\FeeCategory;
use App\Models\FeeType;
use Database\Seeders\EmergencyFeeTypeSeeder;

it('adds emergency fee types with zero amounts without duplicates', function () {
    $this->seed(EmergencyFeeTypeSeeder::class);

    $emergencyCategory = FeeCategory::query()->where('name', 'Emergency')->first();

    expect($emergencyCategory)->not->toBeNull();

    $expectedTypes = [
        'Primary PCI / Rescue PCI',
        'TPM (temporary pacemaker)',
        'Pericardiocentesis',
    ];

    foreach ($expectedTypes as $expectedType) {
        $seededFeeType = FeeType::query()
            ->where('fee_category_id', $emergencyCategory->id)
            ->where('type', $expectedType)
            ->where('status', 'Normal')
            ->first();

        expect($seededFeeType)->not->toBeNull();
        expect((float) $seededFeeType->amount)->toBe(0.0);
        expect((float) $seededFeeType->hif)->toBe(0.0);
    }

    $this->seed(EmergencyFeeTypeSeeder::class);

    expect(
        FeeType::query()
            ->where('fee_category_id', $emergencyCategory->id)
            ->whereIn('type', $expectedTypes)
            ->count()
    )->toBe(3);
});
