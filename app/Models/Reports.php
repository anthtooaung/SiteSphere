<?php

namespace App\Models;

use Database\Factories\ReportsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reports extends Model
{
    /** @use HasFactory<ReportsFactory> */
    use HasFactory;

    protected $guarded = [];

    // Status constants
    const STATUS_NEW = 'new';

    const STATUS_PENDING = 'pending_review';

    const STATUS_INVESTIGATING = 'investigating';

    const STATUS_RESOLVED_ACTION = 'resolved_action_taken';

    const STATUS_RESOLVED_NO_ACTION = 'resolved_no_action';

    const STATUS_DISMISSED = 'dismissed';

    const STATUS_CLOSED = 'closed';

    // Valid status transitions
    const TRANSITIONS = [
        self::STATUS_NEW => [self::STATUS_PENDING, self::STATUS_DISMISSED],
        self::STATUS_PENDING => [self::STATUS_INVESTIGATING, self::STATUS_RESOLVED_NO_ACTION, self::STATUS_DISMISSED],
        self::STATUS_INVESTIGATING => [self::STATUS_RESOLVED_ACTION, self::STATUS_RESOLVED_NO_ACTION],
        self::STATUS_RESOLVED_ACTION => [self::STATUS_CLOSED],
        self::STATUS_RESOLVED_NO_ACTION => [self::STATUS_CLOSED],
        self::STATUS_DISMISSED => [self::STATUS_CLOSED],
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'target_id')->withTrashed();
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comments::class, 'target_id')->withTrashed();
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class, 'report_id');
    }

    // --- Status helper methods ---

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [
            self::STATUS_RESOLVED_ACTION,
            self::STATUS_RESOLVED_NO_ACTION,
            self::STATUS_DISMISSED,
            self::STATUS_CLOSED,
        ], true);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return isset(self::TRANSITIONS[$this->status])
            && in_array($newStatus, self::TRANSITIONS[$this->status], true);
    }

    public function transitionTo(string $newStatus, ?string $reason = null): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;

        $this->forceFill(['status' => $newStatus]);

        if (in_array($newStatus, [self::STATUS_RESOLVED_ACTION, self::STATUS_RESOLVED_NO_ACTION], true)) {
            $this->forceFill(['resolved_at' => now()]);
        }

        if ($newStatus === self::STATUS_CLOSED) {
            $this->forceFill(['closed_at' => now()]);
        }

        $this->save();

        ReportStatusHistory::query()->create([
            'report_id' => $this->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => auth()->id(),
            'reason' => $reason,
        ]);

        return true;
    }

    // --- Scopes ---

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            self::STATUS_CLOSED,
            self::STATUS_DISMISSED,
        ]);
    }
}
