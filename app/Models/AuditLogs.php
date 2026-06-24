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
}
