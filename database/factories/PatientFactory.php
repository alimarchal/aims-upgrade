<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Mr', 'Mrs', 'Ms']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'relationship_title' => fake()->randomElement(['S/O', 'D/O', 'W/O', 'H/O', 'M/O', 'F/O']),
            'father_husband_name' => fake()->name(),
            'age' => fake()->numberBetween(1, 90),
            'years_months' => 'Years',
            'dob' => fake()->date(),
            'address' => fake()->address(),
            'sex' => fake()->boolean(),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'registration_date' => fake()->date(),
            'phone' => fake()->numerify('03#########'),
            'email' => fake()->safeEmail(),
            'mobile' => fake()->numerify('03#########'),
            'email_alert' => false,
            'mobile_alert' => false,
            'cnic' => fake()->numerify('#############'),
            'government_non_gov' => fake()->boolean(),
        ];
    }
}
