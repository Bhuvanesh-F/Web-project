<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditLog Model
 *
 * Records all significant admin and staff actions for security auditing.
 * Entries are immutable — no update or delete permitted at the model level.
 *
 * @property int    $id
 * @property string $action_type
 * @property int    $performed_by
 * @property string $performed_by_role
 * @property string|null $affected_table
 * @property int|null    $affected_record_id
 * @property string|null $description
 * @property string      $ip_address
 */
class AuditLog extends Model
{
    use HasFactory;

    /**
     * Disable automatic timestamp updates — audit logs are append-only.
     * We use created_at but never updated_at.
     */
    public const UPDATED_AT = null;

    /**
     * @var array<string>
     */
    protected $fillable = [
        'action_type',
        'performed_by',
        'performed_by_role',
        'affected_table',
        'affected_record_id',
        'description',
        'ip_address',
    ];

    /**
     * The user who performed the action.
     */
    public function performer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
