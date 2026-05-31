<?php

namespace App\View\Components;

use App\Models\Notificatioins;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class NotiBtn extends Component
{
    /**
     * @var Collection<int, Notificatioins>
     */
    public Collection $unreadNotifications;

    public int $unreadCount;

    public function __construct()
    {
        $this->unreadNotifications = new Collection;
        $this->unreadCount = 0;

        if (! Auth::check()) {
            return;
        }

        $unreadQuery = Notificatioins::query()
            ->where('to_user_id', Auth::id())
            ->where('is_read', false);

        $this->unreadCount = (clone $unreadQuery)->count();
        $this->unreadNotifications = $unreadQuery
            ->select(['id', 'message', 'target_type', 'target_id', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.noti-btn');
    }
}
