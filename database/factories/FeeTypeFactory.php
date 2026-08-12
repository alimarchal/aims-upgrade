<?php

namespace Database\Factories;

use App\Models\FeeCategory;
use App\Models\FeeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeeType>
 */
class FeeTypeFactory extends Factory
{
    protected $model = FeeType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fee_category_id' => FeeCategory::factory(),
            'type' => fake()->unique()->word(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'hif' => fake()->randomFloat(2, 0, 1000),
            'status' => true,
        ];
    }
}
