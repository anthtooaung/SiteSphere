<?php

namespace App\Models;

use Database\Factories\AuditLogsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLogs extends Model
{
    /** @use HasFactory<AuditLogsFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the hex color for the audit log category.
     * Color circle boxes — no icons.
     */
    public function getColor(): string
    {
        return match ($this->category) {
            'moderation' => '#ef4444',    // Red — bans, deletes, warnings
            'check' => '#3b82f6',         // Blue — admin reviews/inspections
            'announcement' => '#7c3aed',  // Purple — taxonomy/structure changes
            'resolved' => '#10b981',      // Green — restored, resolved
            default => '#6b7280',         // Gray — fallback
        };
    }

    /**
     * Get a human-readable label for the action.
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            'ban_user' => 'Banned User',
            'unban_user' => 'Restored User',
            'delete_user' => 'Deleted User',
            'force_delete_user' => 'Permanently Deleted User',
            'ban_post' => 'Banned Post',
            'unban_post' => 'Restored Post',
            'delete_post' => 'Deleted Post',
            'force_delete_post' => 'Permanently Deleted Post',
            'ban_audit' => 'Banned Description',
            'unban_audit' => 'Restored Description',
            'delete_audit' => 'Deleted Description',
            'force_delete_audit' => 'Permanently Deleted Description',
            'restore_audit' => 'Restored Description',
            'mark_unsecure' => 'Marked Unsecure',
            'mark_secure' => 'Marked Secure',
            'toggle_unsecure' => 'Toggled Security Status',
            'resolve_report' => 'Resolved Report',
            'dismiss_report' => 'Dismissed Report',
            default => str_replace('_', ' ', ucfirst($this->action)),
        };
    }
}
