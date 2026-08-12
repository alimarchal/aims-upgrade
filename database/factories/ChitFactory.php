<?php

namespace Database\Factories;

use App\Models\Chit;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chit>
 */
class ChitFactory extends Factory
{
    protected $model = Chit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'patient_id' => Patient::factory(),
            'fee_type_id' => null,
            'government_department_id' => null,
            'address' => fake()->address(),
            'issued_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'amount_hif' => 0,
            'govt_amount' => 0,
            'actual_amount' => 0,
            'ipd_opd' => fake()->boolean(),
            'payment_status' => true,
            'government_non_gov' => fake()->boolean(),
        ];
    }
}
