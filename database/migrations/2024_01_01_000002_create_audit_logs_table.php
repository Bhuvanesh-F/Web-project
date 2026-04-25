<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CreateAuditLogsTable
 *
 * Stores a record of all significant admin/system actions.
 * Used for security auditing and compliance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // e.g. 'user.login', 'appointment.created', 'record.updated'
            $table->string('action_type');

            // Who performed the action (0 = system)
            $table->unsignedBigInteger('performed_by')->default(0);
            $table->foreign('performed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set default');

            // Role of the performer at time of action
            $table->string('performed_by_role')->default('system');

            // Which table/record was affected
            $table->string('affected_table')->nullable();
            $table->unsignedBigInteger('affected_record_id')->nullable();

            // Human-readable description
            $table->text('description')->nullable();

            // Request metadata
            $table->string('ip_address', 45)->nullable();

            // Audit logs are append-only — no updated_at needed
            $table->timestamp('created_at')->nullable();

            // Indexes for fast queries
            $table->index('action_type');
            $table->index('performed_by');
            $table->index(['affected_table', 'affected_record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
