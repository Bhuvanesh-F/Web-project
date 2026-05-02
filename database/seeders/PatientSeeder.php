<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        Patient::create([
            'name' => 'Test User',
            'email' => 'test@test.com'
        ]);

        Patient::create([
            'name' => 'Alice',
            'email' => 'alice@test.com'
        ]);
    }
}