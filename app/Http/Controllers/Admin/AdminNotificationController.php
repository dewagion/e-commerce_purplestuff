<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;

class AdminNotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        if (!Schema::hasTable('notifications')) {
            return response()->json([
                'latest_id' => null,
                'unread_count' => 0,
                'total_count' => 0,
                'notifications' => [],
            ]);
        }

        $admin = $request->user('admin');

        $notifications = $admin
            ->notifications()
            ->latest()
            ->take(8)
            ->get()
            ->map(function (DatabaseNotification $notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->data['message'] ?? 'Notifikasi baru',
                    'order_number' => $notification->data['order_number'] ?? null,
                    'link' => $notification->data['link'] ?? null,
                    'read_at' => optional($notification->read_at)?->toIso8601String(),
                    'time_human' => optional($notification->created_at)?->diffForHumans(),
                    'read_url' => route('admin.notifications.read', $notification),
                    'delete_url' => route('admin.notifications.destroy', $notification),
                ];
            })
            ->values();

        return response()->json([
            'latest_id' => optional($admin->notifications()->latest()->first())->id,
            'unread_count' => $admin->unreadNotifications()->count(),
            'total_count' => $admin->notifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function readAll(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('notifications')) {
            return back();
        }

        $request->user('admin')->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca.');
    }

    public function destroyAll(Request $request): JsonResponse|RedirectResponse
    {
        if (!Schema::hasTable('notifications')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tabel notifikasi belum tersedia.',
                ], 422);
            }

            return back()->with('error', 'Tabel notifikasi belum tersedia.');
        }

        $request->user('admin')->notifications()->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'latest_id' => null,
                'unread_count' => 0,
                'total_count' => 0,
                'notifications' => [],
                'message' => 'Semua notifikasi berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }

    public function destroy(Request $request, DatabaseNotification $notification): JsonResponse|RedirectResponse
    {
        if (!Schema::hasTable('notifications')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tabel notifikasi belum tersedia.',
                ], 422);
            }

            return back()->with('error', 'Tabel notifikasi belum tersedia.');
        }

        $admin = $request->user('admin');
        $this->authorizeNotificationOwner($notification, $admin->id);

        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'latest_id' => optional($admin->notifications()->latest()->first())->id,
                'unread_count' => $admin->unreadNotifications()->count(),
                'total_count' => $admin->notifications()->count(),
                'message' => 'Notifikasi berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        if (!Schema::hasTable('notifications')) {
            return back();
        }

        $admin = $request->user('admin');
        $this->authorizeNotificationOwner($notification, $admin->id);

        $notification->markAsRead();

        return redirect($notification->data['link'] ?? route('admin.dashboard'));
    }

    private function authorizeNotificationOwner(DatabaseNotification $notification, int|string $adminId): void
    {
        if ((string) $notification->notifiable_id !== (string) $adminId || $notification->notifiable_type !== User::class) {
            abort(403);
        }
    }
}
