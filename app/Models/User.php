<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * Central authentication model for all VitalCare roles.
 * Uses Laravel Sanctum for API token management.
 *
 * Security:
 * - Password is hidden from array/JSON serialization
 * - Remember token is hidden from array/JSON serialization
 * - All attributes are mass-assignment protected via $fillable
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $phone
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Valid user roles in the VitalCare system.
     */
    public const ROLES = [
        'patient',
        'doctor',
        'vet',
        'nurse',
        'receptionist',
        'pet_owner',
        'admin',
    ];

    /**
     * The attributes that are mass assignable.
     * All other attributes are protected by default.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Prevents password hashes and tokens from leaking in API responses.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // =========================================================================
    // Role helper methods
    // =========================================================================

    /**
     * Check if the user has a specific role.
     *
     * @param  string  $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param  string[]  $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Determine if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Determine if the user is medical staff (doctor, vet, nurse).
     *
     * @return bool
     */
    public function isMedicalStaff(): bool
    {
        return in_array($this->role, ['doctor', 'vet', 'nurse'], true);
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * A user may have many audit log entries.
     * Used to track admin actions.
     */
    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditLog::class, 'performed_by');
    }
}
