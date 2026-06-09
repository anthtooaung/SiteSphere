<?php

namespace App\Models;

use Database\Factories\ReportsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reports extends Model
{
    /** @use HasFactory<ReportsFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admin_read' => 'boolean',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'target_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comments::class, 'target_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
