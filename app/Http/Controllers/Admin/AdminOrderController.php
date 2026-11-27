<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomerOrderCancelledMail;
use App\Mail\CustomerOrderShippedMail;
use App\Mail\CustomerPaymentConfirmedMail;
use App\Models\Order;
use App\Models\OrderTrackingHistory;
use App\Models\Product;
use App\Models\ProductOptionStock;
use App\Notifications\CustomerOrderShippedNotification;
use App\Services\AdminNotificationService;
use App\Services\OrderPaymentExpiryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminOrderController extends Controller
{
    public function __construct(
        protected AdminNotificationService $adminNotificationService,
    ) {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $paymentStatus = trim((string) $request->query('payment_status', ''));
        $status = trim((string) $request->query('status', ''));

        $orders = Order::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            })
            ->when($paymentStatus !== '', fn ($query) => $query->where('payment_status', $paymentStatus))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'search', 'paymentStatus', 'status'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'user', 'trackingHistories.admin']);

        return view('admin.orders.show', compact('order'));
    }

    public function paymentProof(Order $order)
    {
        $file = $this->resolveStoredFile($order->payment_proof_path);

        abort_if(!$file, 404, 'Bukti transfer tidak ditemukan.');

        return response()->file($file['absolute'], [
            'Content-Type' => $file['mime'],
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function shippingReceipt(Order $order)
    {
        $file = $this->resolveStoredFile($order->shipping_receipt_path);

        abort_if(!$file, 404, 'Foto resi tidak ditemukan.');

        return response()->file($file['absolute'], [
            'Content-Type' => $file['mime'],
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    protected function resolveStoredFile(?string $storedPath): ?array
    {
        $relativePath = trim((string) $storedPath);

        if ($relativePath === '') {
            return null;
        }

        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePath = preg_replace('#^(public/|storage/)#', '', $relativePath);
        $relativePath = ltrim($relativePath, '/');

        if (!Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return [
            'relative' => $relativePath,
            'absolute' => Storage::disk('public')->path($relativePath),
            'mime' => Storage::disk('public')->mimeType($relativePath) ?: 'application/octet-stream',
        ];
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'action_type' => ['required', 'in:payment,shipping'],
            'payment_status' => ['nullable', 'in:pending,waiting_verification,paid,expired'],
            'status' => ['nullable', 'in:Menunggu Pembayaran,Menunggu Verifikasi Pembayaran,Diproses,Dikirim,Selesai,Dibatalkan'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'tracking_notes' => ['nullable', 'string', 'max:1000'],
            'shipping_receipt' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_shipping_receipt' => ['nullable', 'boolean'],
        ]);

        $order->loadMissing(['items', 'user']);

        $oldPaymentStatus = $order->payment_status;
        $oldStatus = $order->status;
        $oldTrackingNumber = $order->tracking_number;
        $oldTrackingNotes = $order->tracking_notes;
        $oldShippingReceiptPath = $order->shipping_receipt_path;

        if ($data['action_type'] === 'payment') {
            return $this->updatePaymentStatus($request, $order, $data, $oldPaymentStatus, $oldStatus);
        }

        return $this->updateShippingStatus(
            $request,
            $order,
            $data,
            $oldPaymentStatus,
            $oldStatus,
            $oldTrackingNumber,
            $oldTrackingNotes,
            $oldShippingReceiptPath
        );
    }

    protected function updatePaymentStatus(Request $request, Order $order, array $data, ?string $oldPaymentStatus, ?string $oldStatus)
    {
        $paymentStatus = $data['payment_status'] ?? $order->payment_status;

        if ($order->payment_status === 'expired') {
            return back()->with('error', 'Pembayaran yang sudah gagal/expired tidak bisa diverifikasi lagi. Pelanggan perlu membuat pesanan baru agar stok terkunci ulang.');
        }

        if ($order->payment_status === 'paid' && $paymentStatus !== 'paid') {
            return back()->with('error', 'Pembayaran yang sudah paid tidak dapat diubah menjadi pending atau gagal. Jika pesanan perlu dibatalkan setelah paid, gunakan Status Pesanan = Dibatalkan.');
        }

        if ($paymentStatus === 'expired' && $order->payment_status === 'paid') {
            return back()->with('error', 'Pembayaran paid tidak boleh dijadikan gagal. Gunakan pembatalan pesanan jika memang perlu dibatalkan setelah pembayaran diterima.');
        }

        $order->payment_status = $paymentStatus;

        if ($paymentStatus === 'pending') {
            if (!empty($order->payment_proof_path)) {
                return back()->with('error', 'Pesanan yang sudah memiliki bukti transfer tidak sebaiknya dikembalikan ke Pending. Gunakan Menunggu Verifikasi atau Paid.');
            }

            $order->status = 'Menunggu Pembayaran';
        }

        if ($paymentStatus === 'waiting_verification') {
            $order->status = 'Menunggu Verifikasi Pembayaran';
        }

        if ($paymentStatus === 'paid') {
            $order->paid_at = $order->paid_at ?: now();
            $order->confirmed_at = $order->confirmed_at ?: now();

            if (in_array($order->status, ['Menunggu Pembayaran', 'Menunggu Verifikasi Pembayaran', 'Dibatalkan'], true)) {
                $order->status = 'Diproses';
            }
        }

        if ($paymentStatus === 'expired') {
            $order->status = 'Dibatalkan';
            $order->payment_expired_at = $order->payment_expired_at ?: now();
        }

        $order->save();

        if ($oldPaymentStatus !== 'paid' && $order->payment_status === 'paid') {
            $this->adminNotificationService->notifyAdmins(
                $order,
                'payment_paid',
                'Pembayaran untuk pesanan ' . $order->order_number . ' telah dikonfirmasi.'
            );

            $this->sendCustomerPaymentConfirmedEmail($order);
        }

        if ($order->payment_status === 'expired' && ($oldPaymentStatus !== 'expired' || $oldStatus !== 'Dibatalkan')) {
            $this->restoreStockForCancelledOrder($order, 'payment_failed_by_admin');

            $this->adminNotificationService->notifyAdmins(
                $order,
                'order_cancelled',
                'Pesanan ' . $order->order_number . ' dibatalkan karena pembayaran gagal.'
            );

            $this->sendCustomerOrderCancelledEmail($order, 'Pembayaran dinyatakan gagal oleh admin, sehingga pesanan dibatalkan dan stok dikembalikan.');
        }

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    protected function updateShippingStatus(
        Request $request,
        Order $order,
        array $data,
        ?string $oldPaymentStatus,
        ?string $oldStatus,
        ?string $oldTrackingNumber,
        ?string $oldTrackingNotes,
        ?string $oldShippingReceiptPath
    ) {
        $status = $data['status'] ?? $order->status;
        $trackingNumber = trim((string) ($data['tracking_number'] ?? ''));
        $trackingNotes = trim((string) ($data['tracking_notes'] ?? ''));

        if ($oldStatus === 'Selesai' && $status !== 'Selesai') {
            return back()
                ->withErrors(['status' => 'Pesanan yang sudah Selesai tidak dapat dikembalikan ke status sebelumnya.'])
                ->withInput();
        }

        if (in_array($oldStatus, ['Dikirim', 'Selesai'], true) && $status === 'Dibatalkan') {
            return back()
                ->withErrors(['status' => 'Pesanan yang sudah Dikirim atau Selesai tidak dapat dibatalkan dari menu ini agar stok dan resi tetap sinkron.'])
                ->withInput();
        }

        if (in_array($status, ['Diproses', 'Dikirim', 'Selesai'], true) && $order->payment_status !== 'paid') {
            return back()
                ->withErrors(['status' => 'Status ' . $status . ' hanya dapat digunakan setelah pembayaran dikonfirmasi Paid.'])
                ->withInput();
        }

        if ($status === 'Dikirim' && $trackingNumber === '') {
            return back()
                ->withErrors(['tracking_number' => 'Nomor resi wajib diisi saat status pesanan Dikirim.'])
                ->withInput();
        }

        $order->status = $status;
        $order->tracking_number = $trackingNumber !== '' ? $trackingNumber : null;
        $order->tracking_notes = $trackingNotes !== '' ? $trackingNotes : null;

        if ($request->boolean('remove_shipping_receipt')) {
            $oldFile = $this->resolveStoredFile($order->shipping_receipt_path);
            if ($oldFile) {
                Storage::disk('public')->delete($oldFile['relative']);
            }

            $order->shipping_receipt_path = null;
            $order->shipping_receipt_uploaded_at = null;
        }

        if ($request->hasFile('shipping_receipt')) {
            $oldFile = $this->resolveStoredFile($order->shipping_receipt_path);
            if ($oldFile) {
                Storage::disk('public')->delete($oldFile['relative']);
            }

            $order->shipping_receipt_path = $request->file('shipping_receipt')->store('shipping-receipts', 'public');
            $order->shipping_receipt_uploaded_at = now();
        }

        $shippingReceiptChanged = $oldShippingReceiptPath !== $order->shipping_receipt_path;
        $trackingChanged = $oldTrackingNumber !== $order->tracking_number || $oldTrackingNotes !== $order->tracking_notes;

        if ($trackingChanged || $shippingReceiptChanged) {
            $order->tracking_updated_at = now();
        }

        if ($status === 'Diproses') {
            $order->confirmed_at = $order->confirmed_at ?: now();
        }

        if ($status === 'Dikirim') {
            $order->shipped_at = $order->shipped_at ?: now();
        }

        if ($status === 'Selesai') {
            $order->completed_at = $order->completed_at ?: now();
        }

        if ($status === 'Dibatalkan' && $order->payment_status !== 'paid') {
            $order->payment_status = 'expired';
            $order->payment_expired_at = $order->payment_expired_at ?: now();
        }

        $order->save();

        if ($trackingChanged || $shippingReceiptChanged || $oldStatus !== $order->status) {
            OrderTrackingHistory::create([
                'order_id' => $order->id,
                'admin_user_id' => auth('admin')->id(),
                'tracking_number' => $order->tracking_number,
                'tracking_notes' => $order->tracking_notes,
                'status_snapshot' => $order->status,
                'event_type' => ($trackingChanged || $shippingReceiptChanged) ? 'tracking_updated' : 'status_updated',
            ]);
        }

        if ($oldStatus !== 'Dibatalkan' && $order->status === 'Dibatalkan') {
            $reason = $order->payment_status === 'paid'
                ? 'Pesanan dibatalkan oleh admin. Pembayaran sebelumnya sudah tercatat paid, sehingga proses lanjutan dapat dikonfirmasi melalui customer service.'
                : 'Pesanan dibatalkan oleh admin sebelum pembayaran dikonfirmasi. Stok pesanan dikembalikan.';

            $this->restoreStockForCancelledOrder($order, $order->payment_status === 'paid' ? 'cancelled_after_paid' : 'cancelled_unpaid');

            $this->adminNotificationService->notifyAdmins(
                $order,
                'order_cancelled',
                'Pesanan ' . $order->order_number . ' dibatalkan.'
            );

            $this->sendCustomerOrderCancelledEmail($order, $reason);
        }

        if ($oldPaymentStatus !== 'paid' && $order->payment_status === 'paid') {
            $this->adminNotificationService->notifyAdmins(
                $order,
                'payment_paid',
                'Pembayaran untuk pesanan ' . $order->order_number . ' telah dikonfirmasi.'
            );

            $this->sendCustomerPaymentConfirmedEmail($order);
        }

        if ($order->status === 'Dikirim' && ($oldStatus !== 'Dikirim' || ($oldTrackingNumber !== $order->tracking_number && !empty($order->tracking_number)))) {
            $this->notifyCustomerOrderShipped($order);
            $this->sendCustomerOrderShippedEmail($order);
        }

        return back()->with('success', 'Status pesanan, nomor resi, foto resi, dan riwayat tracking berhasil diperbarui.');
    }

    protected function notifyCustomerOrderShipped(Order $order): void
    {
        if (!$order->user) {
            return;
        }

        try {
            $order->user->notify(new CustomerOrderShippedNotification($order));
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan notifikasi resi ke customer.', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function sendCustomerPaymentConfirmedEmail(Order $order): void
    {
        try {
            $order->loadMissing(['items', 'user']);
            $recipient = $this->customerEmailForOrder($order, 'konfirmasi pembayaran');

            if (!$recipient) {
                return;
            }

            Mail::to($recipient)->send(new CustomerPaymentConfirmedMail($order));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email konfirmasi pembayaran ke customer.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendCustomerOrderShippedEmail(Order $order): void
    {
        try {
            $order->loadMissing(['items', 'user']);
            $recipient = $this->customerEmailForOrder($order, 'pesanan dikirim');

            if (!$recipient) {
                return;
            }

            Mail::to($recipient)->send(new CustomerOrderShippedMail($order));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email pesanan dikirim ke customer.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendCustomerOrderCancelledEmail(Order $order, string $reason): void
    {
        app(OrderPaymentExpiryService::class)->sendCustomerOrderCancelledEmailOnce($order, $reason);
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

    protected function restoreStockForCancelledOrder(Order $order, string $reason = 'cancelled'): void
    {
        if (Schema::hasColumn('orders', 'stock_released_at') && $order->stock_released_at) {
            return;
        }

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

        DB::transaction(function () use ($order, $qtyByOption, $reason) {
            $freshOrder = Order::query()->whereKey($order->getKey())->lockForUpdate()->first();

            if (!$freshOrder) {
                return;
            }

            if (Schema::hasColumn('orders', 'stock_released_at') && $freshOrder->stock_released_at) {
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

            if (Schema::hasColumn('orders', 'stock_released_at')) {
                $freshOrder->stock_released_at = now();
            }

            if (Schema::hasColumn('orders', 'stock_release_reason')) {
                $freshOrder->stock_release_reason = $reason;
            }

            $freshOrder->save();
        });

        $order->refresh();
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
