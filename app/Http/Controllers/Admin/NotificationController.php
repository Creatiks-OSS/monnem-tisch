<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AdminNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function feed(): JsonResponse
    {
        $query = AdminNotification::query()->where('user_id', auth()->id());

        $items = (clone $query)->latest()->limit(8)->get()->map(fn (AdminNotification $n) => [
            'id' => $n->id,
            'title' => $n->title,
            'body' => $n->body,
            'type' => $n->type,
            'label' => $n->typeLabel(),
            'url' => route('admin.notifications.open', $n),
            'read' => $n->read_at !== null,
            'time' => $n->created_at?->diffForHumans(),
        ]);

        return response()->json([
            'unread' => (clone $query)->unread()->count(),
            'items' => $items,
        ]);
    }

    public function open(AdminNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markAsRead();

        return redirect()->to($notification->targetUrl());
    }

    public function markRead(AdminNotification $notification): JsonResponse|RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Okundu olarak işaretlendi.');
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        AdminNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Tümü okundu olarak işaretlendi.');
    }

    public function destroy(AdminNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->delete();

        return back()->with('success', 'Bildirim silindi.');
    }

    public function destroyAll(): RedirectResponse
    {
        AdminNotification::query()
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Tüm bildirimler silindi.');
    }
}
