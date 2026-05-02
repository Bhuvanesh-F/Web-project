<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'specialization' => fake()->randomElement([
                'Cardiology',
                'Dermatology',
                'Neurology',
                'Pediatrics',
                'General Medicine'
            ]),
        ];
    }
}