<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        Appointment::create([
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2026-05-02',
            'status' => 'pending'
        ]);

        Appointment::create([
            'patient_id' => 2,
            'doctor_id' => 2,
            'appointment_date' => '2026-05-03',
            'status' => 'confirmed'
        ]);
    }
}