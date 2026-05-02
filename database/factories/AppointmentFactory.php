<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;
use App\Models\Doctor;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_date' => fake()->dateTimeBetween('+1 days', '+1 month'),
            'status' => fake()->randomElement([
                'pending',
                'confirmed',
                'cancelled'
            ]),
            'reason' => fake()->sentence(),
        ];
    }
}