<?php

namespace App\Console\Commands;

use App\Models\FeeType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyOneTimeFeeUpdate extends Command
{
    protected $signature = 'fees:apply-one-time-update';

    protected $description = 'One-time fee revision: set total and HIF for the listed fee types';

    /**
     * Fee type id => [total fee, HIF fee].
     *
     * @var array<int, array{0: int, 1: int}>
     */
    public const REVISED_FEES = [
        1 => [50, 25],
        107 => [50, 25],
        215 => [-50, -25],
        216 => [-50, -25],
        108 => [50, 25],
        270 => [50, 25],
    ];

    public function handle(): int
    {
        DB::transaction(function (): void {
            foreach (self::REVISED_FEES as $id => [$amount, $hif]) {
                $feeType = FeeType::query()->find($id);

                if (! $feeType) {
                    $this->warn("Fee type {$id} not found, skipped.");

                    continue;
                }

                $feeType->update(['amount' => $amount, 'hif' => $hif]);
                $this->info("{$feeType->type} ({$id}): total {$amount}, HIF {$hif}");
            }
        });

        return self::SUCCESS;
    }
}
