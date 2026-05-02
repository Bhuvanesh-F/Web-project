<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;
use App\Models\Doctor;

class MedicalRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'diagnosis' => fake()->sentence(),
            'treatment' => fake()->paragraph(),
            'notes' => fake()->text(200),
            'record_date' => fake()->date(),
        ];
    }
}