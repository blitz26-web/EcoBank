<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->authorizeOwner($request->user(), $notification);

        $target = (string) ($notification->data['url'] ?? route('notifications.index'));

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        return redirect($target)->with('status', 'notification-read');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'notifications-read-all');
    }

    private function authorizeOwner(User $user, DatabaseNotification $notification): void
    {
        $owned = $notification->notifiable_type === $user::class
            && (string) $notification->notifiable_id === (string) $user->getKey();

        abort_unless($owned, 404);
    }
}
