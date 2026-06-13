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
     * Get the icon for the audit log category.
     */
    public function getIcon(): string
    {
        return match ($this->category) {
            'moderation' => 'fa-hammer',
            'success' => 'fa-check-circle',
            'announcement' => 'fa-bullhorn',
            default => 'fa-cog',
        };
    }

    /**
     * Get the hex color for the audit log category.
     */
    public function getColor(): string
    {
        return match ($this->category) {
            'moderation' => '#ef4444', // Red-500
            'success' => '#22c55e',    // Green-500
            'announcement' => '#3b82f6', // Blue-500
            default => '#6b7280',      // Gray-500
        };
    }
}
