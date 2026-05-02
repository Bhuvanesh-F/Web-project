<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalRecord;

class MedicalRecordSeeder extends Seeder
{
    public function run(): void
    {
        MedicalRecord::create([
            'patient_id' => 1,
            'diagnosis' => 'Fever',
            'treatment' => 'Paracetamol'
        ]);

        MedicalRecord::create([
            'patient_id' => 2,
            'diagnosis' => 'Flu',
            'treatment' => 'Rest + Flu meds'
        ]);
    }
}