<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SystemNotifier
{
    public static function notifyRoles(
        array|string $roles,
        string $title,
        string $message,
        ?string $url = null,
        string $tone = 'info',
        string $category = 'system'
    ): void {
        $roles = is_array($roles) ? $roles : [$roles];

        $users = User::query()
            ->whereIn('role', $roles)
            ->where('status', 'Aktif')
            ->get();

        self::send($users, $title, $message, $url, $tone, $category);
    }

    public static function notifyUser(
        ?User $user,
        string $title,
        string $message,
        ?string $url = null,
        string $tone = 'info',
        string $category = 'system'
    ): void {
        if (! $user || $user->status !== 'Aktif') {
            return;
        }

        $user->notify(new SystemNotification($title, $message, $url, $tone, $category));
    }

    private static function send(
        Collection $users,
        string $title,
        string $message,
        ?string $url,
        string $tone,
        string $category
    ): void {
        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new SystemNotification($title, $message, $url, $tone, $category));
    }
}
