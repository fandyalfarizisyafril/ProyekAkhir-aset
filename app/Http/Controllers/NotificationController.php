<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.notifications.index', [
            'notifications' => $request->user()
                ->notifications()
                ->latest()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $item->markAsRead();

        $url = $item->data['url'] ?? null;

        if ($url) {
            return redirect()->to($url);
        }

        return redirect()->back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Semua notifikasi sudah ditandai dibaca.');
    }
}
