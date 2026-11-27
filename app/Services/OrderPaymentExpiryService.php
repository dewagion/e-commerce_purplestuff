<?php

namespace App\Services;

use App\Mail\CustomerOrderCancelledMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOptionStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OrderPaymentExpiryService
{
    public function expireIfNeeded(Order $order): bool
    {
        if (!$this->canUseExpiryColumns()) {
            return false;
        }

        // Jika pesanan sudah terlanjur expired sebelum kode email ini dipasang,
        // service tetap mencoba mengirim email pembatalan satu kali saat pesanan dicek kembali.
        if ($order->payment_status === 'expired' && $order->status === 'Dibatalkan') {
            $this->sendCustomerOrderCancelledEmailOnce(
                $order,
                'Pembayaran melewati batas waktu yang diberikan, sehingga pesanan otomatis dibatalkan dan stok dikembalikan.'
            );

            return false;
        }

        $deadline = $order->payment_deadline_at;

        if (!$deadline || now()->lt($deadline)) {
            return false;
        }

        if ($order->payment_status !== 'pending') {
            return false;
        }

        if (!empty($order->payment_proof_path)) {
            return false;
        }

        return $this->expireOrder($order);
    }

    public function expireDuePendingOrders(int $limit = 50): int
    {
        if (!$this->canUseExpiryColumns()) {
            return 0;
        }

        try {
            $orders = Order::query()
                ->where('payment_status', 'pending')
                ->whereNull('payment_proof_path')
                ->whereNotNull('payment_deadline_at')
                ->where('payment_deadline_at', '<=', now())
                ->oldest('payment_deadline_at')
                ->limit(max(1, $limit))
                ->get();
        } catch (Throwable $exception) {
            return 0;
        }

        $expired = 0;

        foreach ($orders as $order) {
            try {
                if ($this->expireOrder($order)) {
                    $expired++;
                }
            } catch (Throwable $exception) {
                Log::warning('Gagal mengubah pesanan pending menjadi expired.', [
                    'order_id' => $order->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $expired;
    }

    public function remainingSeconds(Order $order): int
    {
        if (!$order->payment_deadline_at) {
            return 0;
        }

        return max(0, now()->diffInSeconds($order->payment_deadline_at, false));
    }

    public function canUseExpiryColumns(): bool
    {
        try {
            return Schema::hasTable('orders')
                && Schema::hasColumn('orders', 'payment_deadline_at')
                && Schema::hasColumn('orders', 'payment_expired_at');
        } catch (Throwable $exception) {
            return false;
        }
    }

    protected function canUseStockReleaseColumns(): bool
    {
        try {
            return Schema::hasTable('orders')
                && Schema::hasColumn('orders', 'stock_released_at')
                && Schema::hasColumn('orders', 'stock_release_reason');
        } catch (Throwable $exception) {
            return false;
        }
    }

    protected function expireOrder(Order $order): bool
    {
        $expired = (bool) DB::transaction(function () use ($order) {
            /** @var Order|null $freshOrder */
            $freshOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->first();

            if (!$freshOrder) {
                return false;
            }

            if ($freshOrder->payment_status !== 'pending') {
                return false;
            }

            if (!empty($freshOrder->payment_proof_path)) {
                return false;
            }

            if (!$freshOrder->payment_deadline_at || now()->lt($freshOrder->payment_deadline_at)) {
                return false;
            }

            $canTrackStockRelease = $this->canUseStockReleaseColumns();
            $shouldReturnStock = !$canTrackStockRelease || !$freshOrder->stock_released_at;

            if ($shouldReturnStock) {
                $this->returnStock($freshOrder);

                if ($canTrackStockRelease) {
                    $freshOrder->stock_released_at = now();
                    $freshOrder->stock_release_reason = 'payment_timeout';
                }
            }

            $freshOrder->payment_status = 'expired';
            $freshOrder->status = 'Dibatalkan';
            $freshOrder->payment_expired_at = now();
            $freshOrder->save();

            $order->setRawAttributes($freshOrder->getAttributes(), true);
            $order->syncOriginal();

            return true;
        });

        if ($expired) {
            $this->sendCustomerOrderExpiredEmail($order);
        }

        return $expired;
    }

    protected function sendCustomerOrderExpiredEmail(Order $order): void
    {
        $this->sendCustomerOrderCancelledEmailOnce(
            $order,
            'Pembayaran melewati batas waktu yang diberikan, sehingga pesanan otomatis dibatalkan dan stok dikembalikan.'
        );
    }

    public function sendCustomerOrderCancelledEmailOnce(Order $order, string $reason): bool
    {
        try {
            $order->refresh()->loadMissing(['items', 'user']);

            if ($this->canUseCancelledEmailColumn() && $order->customer_order_cancelled_email_sent_at) {
                return false;
            }

            $recipient = $this->customerEmailForOrder($order, 'pesanan dibatalkan / pembayaran gagal');

            if (!$recipient) {
                return false;
            }

            Mail::to($recipient)->send(new CustomerOrderCancelledMail($order, $reason));

            if ($this->canUseCancelledEmailColumn()) {
                $order->forceFill([
                    'customer_order_cancelled_email_sent_at' => now(),
                ])->save();
            }

            Log::info('Email pesanan dibatalkan berhasil dikirim ke customer.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'recipient' => $recipient,
            ]);

            return true;
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim email pesanan dibatalkan / expired ke customer.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    protected function canUseCancelledEmailColumn(): bool
    {
        try {
            return Schema::hasTable('orders')
                && Schema::hasColumn('orders', 'customer_order_cancelled_email_sent_at');
        } catch (Throwable $exception) {
            return false;
        }
    }

    protected function customerEmailForOrder(Order $order, string $context): ?string
    {
        $recipient = trim((string) ($order->customer_email ?: optional($order->user)->email));

        if ($recipient !== '' && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return $recipient;
        }

        Log::warning('Email customer tidak dikirim karena alamat email pelanggan tidak valid.', [
            'context' => $context,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_email' => $order->customer_email,
            'user_email' => optional($order->user)->email,
        ]);

        return null;
    }

    protected function returnStock(Order $order): void
    {
        $order->loadMissing('items');

        $qtyByOption = $order->items
            ->filter(fn ($item) => !empty($item->product_id))
            ->groupBy(fn ($item) => $item->product_id . '||' . ($item->variant ?? '') . '||' . ($item->size ?? ''))
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'product_id' => $first->product_id,
                    'variant' => $first->variant,
                    'size' => $first->size,
                    'qty' => $items->sum(fn ($item) => (int) $item->qty),
                ];
            });

        if ($qtyByOption->isEmpty()) {
            return;
        }

        $products = Product::with('optionStocks')
            ->whereIn('id', $qtyByOption->pluck('product_id')->filter()->unique()->values()->all())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($qtyByOption as $row) {
            $product = $products->get($row['product_id']);

            if (!$product) {
                continue;
            }

            $this->increaseStockForOption(
                $product,
                $row['variant'] ?? null,
                $row['size'] ?? null,
                (int) ($row['qty'] ?? 0)
            );
        }
    }

    protected function increaseStockForOption(Product $product, ?string $variant, ?string $size, int $qty): void
    {
        $qty = max(0, $qty);

        if ($qty <= 0) {
            return;
        }

        if ($product->hasOptionStockRows()) {
            $variant = ProductOptionStock::normalizeOption($variant);
            $size = ProductOptionStock::normalizeOption($size);
            $row = $product->getOptionStockRow($variant, $size) ?: $product->getOptionStockRow('', '');

            if ($row) {
                $row->stock = max(0, (int) $row->stock) + $qty;
                $row->save();

                $product->load('optionStocks');
                $product->stock = $product->optionStocks->sum(fn ($option) => max(0, (int) $option->stock));
                $product->in_stock = $product->stock > 0;
                $product->save();

                return;
            }
        }

        $product->stock = max(0, (int) ($product->stock ?? 0)) + $qty;
        $product->in_stock = $product->stock > 0;
        $product->save();
    }
}
