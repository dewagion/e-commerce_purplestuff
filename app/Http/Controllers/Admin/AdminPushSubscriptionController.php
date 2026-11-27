<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminPushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        if (!Schema::hasTable('admin_push_subscriptions')) {
            return response()->json([
                'ok' => false,
                'message' => 'Tabel push subscription belum tersedia.',
            ], 422);
        }

        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:4096'],
        ]);

        $admin = $request->user('admin');

        $subscription = AdminPushSubscription::updateOrCreate(
            ['fcm_token' => $data['fcm_token']],
            [
                'user_id' => $admin->id,
                'user_agent' => substr((string) $request->userAgent(), 0, 65535),
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
            'subscription_id' => $subscription->id,
        ]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        if (!Schema::hasTable('admin_push_subscriptions')) {
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:4096'],
        ]);

        $admin = $request->user('admin');

        AdminPushSubscription::query()
            ->where('user_id', $admin->id)
            ->where('fcm_token', $data['fcm_token'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}
