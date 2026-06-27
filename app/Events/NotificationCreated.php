<?php

namespace App\Events;

use App\Models\Notificatioins;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notificatioins $notification) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('notifications.' . $this->notification->to_user_id)];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
