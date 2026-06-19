<?php

namespace App\Models;

use Database\Factories\NotificatioinsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Notificatioins extends Model
{
    /** @use HasFactory<NotificatioinsFactory> */
    use HasFactory;

    protected $table = 'notificatioins';

    protected $fillable = [
        'to_user_id',
        'from_user_id',
        'target_type',
        'target_id',
        'message',
        'is_read',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $notification): void {
            Cache::forget('notifications.unread.'.$notification->to_user_id);
        });

        static::deleted(function (self $notification): void {
            Cache::forget('notifications.unread.'.$notification->to_user_id);
        });
    }
}
