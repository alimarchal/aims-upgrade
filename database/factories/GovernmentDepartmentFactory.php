<?php

namespace Database\Factories;

use App\Models\GovernmentDepartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GovernmentDepartment>
 */
class GovernmentDepartmentFactory extends Factory
{
    protected $model = GovernmentDepartment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
        ];
    }
}
