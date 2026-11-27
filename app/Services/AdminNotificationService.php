<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\AdminOrderNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminNotificationService
{
    public function __construct(
        protected FirebaseCloudMessagingService $firebase,
    ) {
    }

    public function notifyAdmins(Order $order, string $type, string $message): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $pushTableReady = Schema::hasTable('admin_push_subscriptions');

        $adminsQuery = User::query()->where('is_admin', true);

        if ($pushTableReady) {
            $adminsQuery->with(['adminPushSubscriptions']);
        }

        $admins = $adminsQuery->get();

        foreach ($admins as $admin) {
            $admin->notify(new AdminOrderNotification($order, $type, $message));

            $notification = $admin->notifications()->latest()->first();
            $subscriptions = $pushTableReady ? $admin->adminPushSubscriptions : collect();

            if ($subscriptions->isEmpty()) {
                continue;
            }

            try {
                $this->firebase->sendToSubscriptions($subscriptions, [
                    'title' => 'Admin Purplestuff',
                    'body' => $message,
                    'notification_id' => $notification?->id,
                    'link' => route('admin.orders.show', $order),
                    'type' => $type,
                    'order_number' => $order->order_number,
                    'icon' => '/favicon.ico',
                    'badge' => '/favicon.ico',
                ]);
            } catch (\Throwable $exception) {
                Log::warning('FCM admin notification gagal dikirim.', [
                    'message' => $exception->getMessage(),
                    'order_id' => $order->id,
                    'admin_id' => $admin->id,
                ]);
            }
        }
    }
}
