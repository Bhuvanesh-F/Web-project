<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder
 *
 * Seeds the database with a default admin account.
 * Run with: php artisan db:seed
 *
 * IMPORTANT: Change the admin password before deploying to production.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Admin Account
        User::firstOrCreate(
            ['email' => 'admin@vitalcare.com'],
            [
                'name'     => 'VitalCare Admin',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
                'phone'    => '+230 5000 0000',
                'is_active'=> true,
            ]
        );

        // Sample Doctor
        User::firstOrCreate(
            ['email' => 'doctor@vitalcare.com'],
            [
                'name'     => 'Dr. Sample Doctor',
                'password' => Hash::make('Doctor@1234'),
                'role'     => 'doctor',
                'phone'    => '+230 5111 1111',
                'is_active'=> true,
            ]
        );

        // Sample Patient
        User::firstOrCreate(
            ['email' => 'patient@vitalcare.com'],
            [
                'name'     => 'Sample Patient',
                'password' => Hash::make('Patient@1234'),
                'role'     => 'patient',
                'phone'    => '+230 5222 2222',
                'is_active'=> true,
            ]
        );

        $this->command->info('✅ Default users seeded successfully.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',   'admin@vitalcare.com',   'Admin@1234'],
                ['Doctor',  'doctor@vitalcare.com',  'Doctor@1234'],
                ['Patient', 'patient@vitalcare.com', 'Patient@1234'],
            ]
        );
    }
}
