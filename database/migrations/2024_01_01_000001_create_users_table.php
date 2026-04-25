<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CreateUsersTable
 *
 * Central users table for all VitalCare roles.
 * Role differentiation is stored in the 'role' column.
 * Sanctum tokens are stored in the personal_access_tokens table (auto-created by Sanctum).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Core identity fields
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Role-based access control
            $table->enum('role', [
                'patient',
                'doctor',
                'vet',
                'nurse',
                'receptionist',
                'pet_owner',
                'admin',
            ])->default('patient');

            // Optional contact info
            $table->string('phone', 20)->nullable();

            // Account status — allows soft-disabling users without deletion
            $table->boolean('is_active')->default(true);

            $table->rememberToken();
            $table->timestamps();

            // Index role for fast middleware lookups
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
