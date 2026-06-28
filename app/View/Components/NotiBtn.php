<?php

namespace App\View\Components;

use App\Models\Notificatioins;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class NotiBtn extends Component
{
    /**
     * @var Collection<int, Notificatioins>
     */
    public Collection $unreadNotifications;

    public int $unreadCount;

    public function __construct(
        public string $trigger = 'bottom',
        public string $mobileMode = 'both',
    ) {
        $this->unreadNotifications = new Collection;
        $this->unreadCount = 0;

        if (! Auth::check()) {
            return;
        }

        $cachedNotifications = Cache::remember(
            'notifications.unread.'.Auth::id(),
            now()->addSeconds(30),
            function (): array {
                $unreadQuery = Notificatioins::query()
                    ->where('to_user_id', Auth::id())
                    ->where('is_read', false);

                return [
                    'count' => (clone $unreadQuery)->count(),
                    'notifications' => $unreadQuery
                        ->select(['id', 'message', 'target_type', 'target_id', 'created_at'])
                        ->latest()
                        ->limit(5)
                        ->get()
                        ->map(fn (Notificatioins $notification): array => [
                            'id' => $notification->id,
                            'message' => $notification->message,
                            'target_type' => $notification->target_type,
                            'target_id' => $notification->target_id,
                            'created_at' => $notification->created_at?->toDateTimeString(),
                        ])
                        ->all(),
                ];
            },
        );

        $this->unreadCount = (int) $cachedNotifications['count'];
        $this->unreadNotifications = new Collection(
            collect($cachedNotifications['notifications'])
                ->map(function (array $attributes): Notificatioins {
                    $notification = new Notificatioins;
                    $notification->forceFill([
                        ...$attributes,
                        'created_at' => $attributes['created_at']
                            ? Carbon::parse($attributes['created_at'])
                            : null,
                    ]);
                    $notification->exists = true;

                    return $notification;
                })
                ->all(),
        );
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.noti-btn');
    }
}
