<?php

namespace App\Models;

use Database\Factories\UserPostsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPosts extends Model
{
    /** @use HasFactory<UserPostsFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_hidden' => 'boolean',
            'is_unsecure' => 'boolean',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the ban reason from the audit log.
     */
    public function getBanReason(): ?string
    {
        if (! $this->trashed()) {
            return null;
        }

        $banLog = AuditLogs::query()
            ->where('target_type', UserPosts::class)
            ->where('target_id', $this->id)
            ->where('action', 'ban_audit')
            ->latest()
            ->first();

        return $banLog?->reason;
    }
}
