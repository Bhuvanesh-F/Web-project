<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        Doctor::create([
            'name' => 'Dr Smith',
            'email' => 'smith@clinic.com',
            'specialization' => 'Cardiology'
        ]);

        Doctor::create([
            'name' => 'Dr John',
            'email' => 'john@clinic.com',
            'specialization' => 'General'
        ]);
    }
}