<?php

use App\Models\Order;
use App\Services\OrderPaymentExpiryService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:expire-pending {--limit=100}', function (OrderPaymentExpiryService $expiryService) {
    $limit = max(1, (int) $this->option('limit'));
    $expiredCount = $expiryService->expireDuePendingOrders($limit);

    $this->info($expiredCount . ' pesanan pending berhasil diproses menjadi expired.');
})->purpose('Expire pending orders that passed the payment deadline');

Artisan::command('orders:send-missing-cancelled-emails {--limit=100}', function (OrderPaymentExpiryService $expiryService) {
    if (!Schema::hasTable('orders')) {
        $this->error('Tabel orders belum tersedia.');
        return 1;
    }

    if (!Schema::hasColumn('orders', 'customer_order_cancelled_email_sent_at')) {
        $this->error('Kolom customer_order_cancelled_email_sent_at belum tersedia. Jalankan php artisan migrate terlebih dahulu.');
        return 1;
    }

    $limit = max(1, (int) $this->option('limit'));

    $orders = Order::query()
        ->with(['items', 'user'])
        ->where('status', 'Dibatalkan')
        ->whereNull('customer_order_cancelled_email_sent_at')
        ->where(function ($query) {
            $query->where('payment_status', 'expired')
                ->orWhereNotNull('payment_expired_at');
        })
        ->oldest('payment_expired_at')
        ->limit($limit)
        ->get();

    $sent = 0;

    foreach ($orders as $order) {
        $reason = 'Pembayaran melewati batas waktu yang diberikan atau pesanan dinyatakan gagal, sehingga pesanan dibatalkan dan stok dikembalikan.';

        if ($expiryService->sendCustomerOrderCancelledEmailOnce($order, $reason)) {
            $sent++;
            $this->line('Email pembatalan dikirim: ' . $order->order_number);
        }
    }

    $this->info($sent . ' email pembatalan/gagal berhasil dikirim.');

    return 0;
})->purpose('Send missing cancellation emails for already expired/cancelled orders');

Artisan::command('orders:test-cancelled-email {order}', function (OrderPaymentExpiryService $expiryService) {
    $orderId = (int) $this->argument('order');
    $orderModel = Order::with(['items', 'user'])->find($orderId);

    if (!$orderModel) {
        $this->error('Order dengan ID ' . $orderId . ' tidak ditemukan.');
        return 1;
    }

    $sent = $expiryService->sendCustomerOrderCancelledEmailOnce(
        $orderModel,
        'Tes email pembayaran gagal dari sistem admin. Email ini digunakan untuk memastikan SMTP dan template email berjalan.'
    );

    if ($sent) {
        $this->info('Email test pembatalan berhasil dikirim ke ' . ($orderModel->customer_email ?: optional($orderModel->user)->email));
        return 0;
    }

    $this->warn('Email test tidak dikirim. Kemungkinan sudah pernah dikirim, email customer kosong/tidak valid, atau SMTP error. Cek storage/logs/laravel.log.');
    return 1;
})->purpose('Test cancellation email for a specific order');

Schedule::command('orders:expire-pending --limit=100')
    ->everyMinute()
    ->withoutOverlapping();
