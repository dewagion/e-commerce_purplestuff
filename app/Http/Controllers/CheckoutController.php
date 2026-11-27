<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductOptionStock;
use App\Models\User;
use App\Mail\AdminPaymentProofUploadedMail;
use App\Notifications\AdminOrderNotification;
use App\Services\BiteshipService;
use App\Services\CartPersistenceService;
use App\Services\OrderPaymentExpiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{

    private const PAYMENT_TIMEOUT_MINUTES = 10;

    public function selected(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login', [
                'redirect' => route('cart.index'),
            ])->with('error', 'Silakan login atau daftar untuk melanjutkan checkout.');
        }

        $data = $request->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['string'],
        ]);

        session([
            'checkout_selected_keys' => array_values($data['selected']),
        ]);
        session()->forget('checkout_shipping_options');

        return redirect()->route('checkout.selected.show');
    }

    public function show(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login', [
                'redirect' => route('cart.index'),
            ])->with('error', 'Silakan login atau daftar untuk melanjutkan checkout.');
        }

        $checkout = $this->buildCheckoutFromSession(syncCart: true);

        if ($checkout['count'] === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Item checkout tidak ditemukan. Silakan pilih ulang produk.');
        }

        if (count($checkout['unavailable_products']) > 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Ada produk yang sudah tidak tersedia. Keranjang sudah diperbarui, silakan pilih ulang checkout.');
        }

        $user = $request->user();

        return view('checkout.selected', [
            'items' => $checkout['items'],
            'subtotal' => $checkout['subtotal'],
            'shippingCost' => (int) old('shipping_cost', 0),
            'grandTotal' => $checkout['subtotal'] + (int) old('shipping_cost', 0),
            'user' => $user,
            'totalWeight' => $checkout['total_weight'],
            'missingWeightProducts' => $checkout['missing_weight_products'],
            'savedPostalCode' => old('destination_postal_code', $user->shipping_postal_code ?? ''),
            'savedShipping' => [
                'courier' => old('shipping_courier', ''),
                'courier_name' => old('shipping_courier_name', ''),
                'service_code' => old('shipping_service_code', ''),
                'service' => old('shipping_service_name', old('shipping_service', '')),
                'etd' => old('shipping_etd', ''),
                'cost' => (int) old('shipping_cost', 0),
            ],
        ]);
    }

    public function shippingOptions(Request $request, BiteshipService $biteship): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'data' => [],
            ], 401);
        }

        $data = $request->validate([
            'destination_postal_code' => ['required', 'digits_between:5,10'],
        ]);

        $checkout = $this->buildCheckoutFromSession(syncCart: true);

        if ($checkout['count'] === 0) {
            return response()->json([
                'message' => 'Item checkout tidak ditemukan.',
                'data' => [],
            ], 422);
        }

        if (count($checkout['unavailable_products']) > 0) {
            return response()->json([
                'message' => 'Ada produk yang sudah tidak tersedia. Silakan buka ulang keranjang.',
                'data' => $checkout['unavailable_products'],
            ], 422);
        }

        if (count($checkout['missing_weight_products']) > 0) {
            return response()->json([
                'message' => 'Ada produk yang belum punya berat. Isi kolom weight produk terlebih dahulu.',
                'data' => $checkout['missing_weight_products'],
            ], 422);
        }

        try {
            $pricing = $biteship->calculateRatesByPostalCode(
                (string) config('services.biteship.origin_postal_code'),
                (string) $data['destination_postal_code'],
                $this->buildBiteshipItems($checkout['items']),
                (string) config('services.biteship.couriers', 'jne')
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => [],
            ], 422);
        }

        $options = collect($pricing)
            ->map(function (array $row) {
                $cost = (int) ($row['price'] ?? $row['shipping_fee'] ?? 0);

                return [
                    'courier_name' => (string) ($row['courier_name'] ?? ''),
                    'courier_code' => (string) ($row['courier_code'] ?? ''),
                    'service' => (string) ($row['courier_service_name'] ?? ''),
                    'service_code' => (string) ($row['courier_service_code'] ?? ''),
                    'description' => (string) ($row['description'] ?? ''),
                    'etd' => (string) ($row['duration'] ?? ''),
                    'cost' => $cost,
                    'label' => trim(((string) ($row['courier_name'] ?? '')) . ' ' . ((string) ($row['courier_service_name'] ?? ''))),
                ];
            })
            ->filter(fn (array $row) => $row['courier_code'] !== '' && $row['service'] !== '' && $row['cost'] > 0)
            ->values()
            ->all();

        if (count($options) === 0) {
            session()->forget('checkout_shipping_options');

            return response()->json([
                'message' => 'Tidak ada layanan ongkir yang tersedia untuk tujuan ini.',
                'data' => [],
            ], 422);
        }

        /**
         * Opsi ongkir yang benar disimpan di session.
         */
        session([
            'checkout_shipping_options' => [
                'destination_postal_code' => (string) $data['destination_postal_code'],
                'items_hash' => $checkout['items_hash'],
                'options' => $options,
                'created_at' => now()->toIso8601String(),
            ],
        ]);

        return response()->json([
            'message' => 'OK',
            'data' => $options,
            'meta' => [
                'weight' => $checkout['total_weight'],
                'subtotal' => $checkout['subtotal'],
                'destination_postal_code' => $data['destination_postal_code'],
            ],
        ]);
    }

    public function confirm(Request $request, CartPersistenceService $cartService)
    {
        if (!auth()->check()) {
            return redirect()->route('login', [
                'redirect' => route('cart.index'),
            ])->with('error', 'Silakan login atau daftar untuk melanjutkan checkout.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:bank_transfer'],

            'destination_postal_code' => ['required', 'digits_between:5,10'],

            'shipping_courier' => ['required', 'string', 'max:50'],
            'shipping_courier_name' => ['required', 'string', 'max:100'],
            'shipping_service_code' => ['required', 'string', 'max:50'],
            'shipping_service_name' => ['required', 'string', 'max:100'],
            'shipping_etd' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['required', 'integer', 'min:1'],
        ]);

        $checkout = $this->buildCheckoutFromSession(syncCart: true);

        if ($checkout['count'] === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Item checkout tidak ditemukan. Silakan pilih ulang produk.');
        }

        if (count($checkout['unavailable_products']) > 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Ada produk yang sudah tidak tersedia. Keranjang sudah diperbarui, silakan pilih ulang checkout.');
        }

        if (count($checkout['missing_weight_products']) > 0) {
            return back()
                ->withErrors([
                    'shipping_cost' => 'Ada produk yang belum punya berat. Isi kolom weight produk terlebih dahulu.',
                ])
                ->withInput();
        }

        $selectedShipping = $this->validatedShippingOption($data, $checkout);

        if (!$selectedShipping) {
            return back()
                ->withErrors([
                    'shipping_cost' => 'Pilihan ongkir tidak valid atau sudah berubah. Silakan klik Hitung Ongkir lagi lalu pilih layanan pengiriman.',
                ])
                ->withInput();
        }

        // Gunakan cost dari session server-side, bukan angka dari hidden input yang bisa dimanipulasi.
        $shippingCost = (int) $selectedShipping['cost'];
        $grandTotal = $checkout['subtotal'] + $shippingCost;

        $user = $request->user();

        $user->fill([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'shipping_postal_code' => $data['destination_postal_code'],
        ])->save();

        $selectedKeys = session('checkout_selected_keys', []);
        $cart = session('cart', []);
        $createdOrder = null;

        DB::transaction(function () use ($user, $data, $checkout, $selectedShipping, $shippingCost, $grandTotal, $selectedKeys, &$cart, &$createdOrder) {
            $lockedProducts = Product::with('optionStocks')
                ->whereIn('id', collect($checkout['items'])->pluck('id')->filter()->unique()->values()->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $qtyByOption = collect($checkout['items'])
                ->groupBy(fn ($row) => ($row['id'] ?? '') . '||' . ($row['variant'] ?? '') . '||' . ($row['size'] ?? ''))
                ->map(function ($rows) {
                    $first = $rows->first();

                    return [
                        'product_id' => $first['id'] ?? null,
                        'variant' => $first['variant'] ?? null,
                        'size' => $first['size'] ?? null,
                        'qty' => $rows->sum(fn ($row) => (int) ($row['qty'] ?? 1)),
                    ];
                });

            foreach ($qtyByOption as $requested) {
                $product = $lockedProducts->get($requested['product_id']);
                $availableStock = $product ? $this->availableStock($product, $requested['variant'] ?? null, $requested['size'] ?? null) : 0;

                if (!$product || $availableStock < (int) ($requested['qty'] ?? 0)) {
                    throw ValidationException::withMessages([
                        'stock' => 'Stok produk untuk pilihan yang dipilih tidak mencukupi atau sudah habis. Silakan periksa keranjang dan coba checkout ulang.',
                    ]);
                }
            }

            $paymentTimeoutMinutes = self::PAYMENT_TIMEOUT_MINUTES;

            $orderPayload = [
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),

                'customer_name' => $data['name'],
                'customer_email' => $user->email,
                'customer_phone' => $data['phone'],
                'customer_address' => trim($data['address'] . "\nKode Pos: " . $data['destination_postal_code']),
                'notes' => $data['notes'] ?? null,

                'subtotal' => $checkout['subtotal'],
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'total_items' => $checkout['total_qty'],

                'shipping_courier' => $selectedShipping['courier_code'],
                'shipping_courier_name' => $selectedShipping['courier_name'],
                'shipping_service_code' => $selectedShipping['service_code'],
                'shipping_service_name' => $selectedShipping['service'],
                'shipping_etd' => $selectedShipping['etd'] ?? null,
                'shipping_weight' => $checkout['total_weight'],
                'shipping_origin_postal_code' => (string) config('services.biteship.origin_postal_code'),
                'shipping_destination_postal_code' => $data['destination_postal_code'],

                'payment_method' => 'bank_transfer',
                'payment_label' => 'Transfer Bank',
                'payment_status' => 'pending',
                'payment_code' => 'TRF-' . now()->format('Ymd') . '-' . random_int(100000, 999999),
                'status' => 'Menunggu Pembayaran',
            ];

            // Stok dikunci saat order
            if (Schema::hasColumn('orders', 'payment_deadline_at')) {
                $orderPayload['payment_deadline_at'] = now()->addMinutes($paymentTimeoutMinutes);
            }

            if (Schema::hasColumn('orders', 'stock_reserved_at')) {
                $orderPayload['stock_reserved_at'] = now();
            }

            $createdOrder = Order::create($orderPayload);

            foreach ($checkout['items'] as $item) {
                OrderItem::create([
                    'order_id' => $createdOrder->id,
                    'product_id' => $item['id'] ?? null,
                    'product_name' => $item['name'] ?? '-',
                    'product_image' => $item['image'] ?? null,
                    'sku' => $item['sku'] ?? null,
                    'variant' => $item['variant'] ?? null,
                    'size' => $item['size'] ?? null,
                    'type' => $item['type'] ?? null,
                    'price' => (int) ($item['price'] ?? 0),
                    'qty' => (int) ($item['qty'] ?? 1),
                    'line_total' => (int) ($item['line_total'] ?? 0),
                ]);
            }

            foreach ($qtyByOption as $purchased) {
                $product = $lockedProducts->get($purchased['product_id']);

                if (!$product) {
                    continue;
                }

                $this->decreaseStockForOption(
                    $product,
                    $purchased['variant'] ?? null,
                    $purchased['size'] ?? null,
                    (int) ($purchased['qty'] ?? 0)
                );
            }

            foreach ($selectedKeys as $key) {
                unset($cart[$key]);
            }

            session(['cart' => $cart]);
        });

        $cartService->putCart($cart);

        session()->forget(['checkout_selected_keys', 'checkout_shipping_options']);

        if ($createdOrder) {
            $this->notifyAdmins(
                $createdOrder,
                'new_order',
                'Pesanan baru ' . $createdOrder->order_number . ' dibuat oleh ' . ($createdOrder->customer_name ?? 'Pelanggan') . '.'
            );
        }

        return redirect()->route('orders.payment', $createdOrder)
            ->with('success', 'Pesanan berhasil dibuat. Stok sudah dikunci selama ' . self::PAYMENT_TIMEOUT_MINUTES . ' menit. Silakan upload bukti transfer sebelum waktu habis.');
    }

    public function payment(Request $request, Order $order, OrderPaymentExpiryService $expiryService)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $expiryService->expireIfNeeded($order);
        $order->refresh()->load('items');

        return view('orders.payment', [
            'order' => $order,
            'remainingSeconds' => $expiryService->remainingSeconds($order),
        ]);
    }

    public function paymentStatus(Request $request, Order $order, OrderPaymentExpiryService $expiryService): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $expiryService->expireIfNeeded($order);
        $order->refresh();

        $deadlineMs = $order->payment_deadline_at
            ? $order->payment_deadline_at->getTimestamp() * 1000
            : null;

        $expiredAtMs = $order->payment_expired_at
            ? $order->payment_expired_at->getTimestamp() * 1000
            : null;

        return response()->json([
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'expired' => $order->payment_status === 'expired',
            'remaining_seconds' => $expiryService->remainingSeconds($order),
            'deadline' => optional($order->payment_deadline_at)?->toIso8601String(),
            'expired_at' => optional($order->payment_expired_at)?->toIso8601String(),
            'deadline_ms' => $deadlineMs,
            'expired_at_ms' => $expiredAtMs,
            'server_now_ms' => now()->getTimestamp() * 1000,
        ]);
    }

    public function pay(Request $request, Order $order, OrderPaymentExpiryService $expiryService)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $expiryService->expireIfNeeded($order);
        $order->refresh();

        if ($order->payment_status === 'expired') {
            return redirect()->route('orders.payment', $order)
                ->with('error', 'Waktu pembayaran sudah habis. Pesanan dibatalkan dan stok sudah dikembalikan.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.payment', $order)->with('success', 'Pesanan ini sudah dikonfirmasi dibayar.');
        }

        if ($order->payment_status === 'waiting_verification') {
            return redirect()->route('orders.payment', $order)->with('success', 'Bukti transfer sudah diupload dan sedang menunggu verifikasi.');
        }

        $data = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $oldFile = $this->resolvePaymentProofFile($order->payment_proof_path);
        if ($oldFile) {
            Storage::disk('public')->delete($oldFile['relative']);
        }

        $path = $data['payment_proof']->store('payment-proofs', 'public');

        $order->update([
            'payment_proof_path' => $path,
            'payment_proof_uploaded_at' => now(),
            'payment_status' => 'waiting_verification',
            'status' => 'Menunggu Verifikasi Pembayaran',
        ]);

        $this->notifyAdmins(
            $order,
            'payment_proof_uploaded',
            'Bukti pembayaran untuk pesanan ' . $order->order_number . ' telah diunggah dan menunggu verifikasi.'
        );

        $this->sendAdminPaymentProofEmail($order);

        return redirect()->route('orders.payment', $order)
            ->with('success', 'Bukti transfer berhasil diupload. Pesanan menunggu verifikasi admin.');
    }

    public function paymentProof(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $file = $this->resolvePaymentProofFile($order->payment_proof_path);

        abort_if(!$file, 404, 'Bukti transfer tidak ditemukan.');

        return response()->file($file['absolute'], [
            'Content-Type' => $file['mime'],
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }


    public function shippingReceipt(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $file = $this->resolvePaymentProofFile($order->shipping_receipt_path);

        abort_if(!$file, 404, 'Foto resi tidak ditemukan.');

        return response()->file($file['absolute'], [
            'Content-Type' => $file['mime'],
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    protected function resolvePaymentProofFile(?string $storedPath): ?array
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

    protected function notifyAdmins(Order $order, string $type, string $message): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $admins = User::where('is_admin', true)->get();

        foreach ($admins as $admin) {
            $admin->notify(new AdminOrderNotification($order, $type, $message));
        }
    }

    protected function sendAdminPaymentProofEmail(Order $order): void
    {
        try {
            $configuredEmail = trim((string) config('services.admin.payment_email', ''));

            $recipients = collect();

            if ($configuredEmail !== '') {
                $recipients = $recipients->merge(
                    collect(preg_split('/[,;]/', $configuredEmail))
                        ->map(fn ($email) => trim((string) $email))
                        ->filter()
                );
            }

            if ($recipients->isEmpty()) {
                $recipients = User::query()
                    ->where('is_admin', true)
                    ->whereNotNull('email')
                    ->pluck('email');
            }

            $recipients = $recipients
                ->map(fn ($email) => trim((string) $email))
                ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                ->unique()
                ->values();

            if ($recipients->isEmpty()) {
                Log::warning('Email notifikasi pembayaran admin tidak dikirim karena alamat email admin belum tersedia.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);

                return;
            }

            $order->loadMissing(['items', 'user']);

            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new AdminPaymentProofUploadedMail($order));
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email notifikasi bukti pembayaran ke admin.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function buildCheckoutFromSession(bool $syncCart = false): array
    {
        $selectedKeys = session('checkout_selected_keys', []);
        $cart = session('cart', []);

        $items = [];
        $subtotal = 0;
        $totalQty = 0;
        $totalWeight = 0;
        $missingWeightProducts = [];
        $unavailableProducts = [];

        $productIds = [];
        foreach ($selectedKeys as $key) {
            if (isset($cart[$key])) {
                $productIds[] = $cart[$key]['id'] ?? null;
            }
        }

        $products = Product::with(['images', 'variants', 'sizes', 'optionStocks'])
            ->whereIn('id', array_filter($productIds))
            ->get()
            ->keyBy('id');

        foreach ($selectedKeys as $key) {
            if (!isset($cart[$key])) {
                continue;
            }

            $cartItem = $cart[$key];
            $productId = $cartItem['id'] ?? null;
            $product = $productId ? $products->get($productId) : null;

            if (!$product) {
                $unavailableProducts[] = [
                    'id' => $productId,
                    'name' => $cartItem['name'] ?? 'Produk tidak tersedia',
                ];
                unset($cart[$key]);
                continue;
            }

            $size = $this->cleanNullableString($cartItem['size'] ?? null);
            $variant = $this->cleanNullableString($cartItem['variant'] ?? null);

            $validSizes = $product->sizes->pluck('size')->filter()->values()->all();
            $validVariants = $product->variants->pluck('name')->filter()->values()->all();

            if ($product->type === 'apparel' && !$size) {
                $unavailableProducts[] = [
                    'id' => $productId,
                    'name' => $product->name,
                ];
                unset($cart[$key]);
                continue;
            }

            if ($size && count($validSizes) > 0 && !in_array($size, $validSizes, true)) {
                $unavailableProducts[] = [
                    'id' => $productId,
                    'name' => $product->name,
                ];
                unset($cart[$key]);
                continue;
            }

            if ($variant && count($validVariants) > 0 && !in_array($variant, $validVariants, true)) {
                $unavailableProducts[] = [
                    'id' => $productId,
                    'name' => $product->name,
                ];
                unset($cart[$key]);
                continue;
            }

            $availableStock = $this->availableStock($product, $variant, $size);

            if ($availableStock <= 0) {
                $unavailableProducts[] = [
                    'id' => $productId,
                    'name' => $product->name,
                ];
                unset($cart[$key]);
                continue;
            }

            $qty = max(1, min($availableStock, (int) ($cartItem['qty'] ?? 1)));

            $newKey = $product->id . ':' . ($size ?: '-') . ':' . ($variant ?: '-');
            $weight = (int) ($product->weight ?? 0);
            $price = method_exists($product, 'sellingPriceFor')
                ? $product->sellingPriceFor($variant, $size)
                : (method_exists($product, 'sellingPrice') ? $product->sellingPrice() : (int) $product->price);
            $image = method_exists($product, 'imageForOption')
                ? $product->imageForOption($variant, $size)
                : $product->primary_image;

            $item = [
                'key' => $newKey,
                'cart_key' => $newKey,
                'id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'image' => $image ?: ($product->images->first()?->path ?: asset('images/placeholder.jpg')),
                'qty' => $qty,
                'type' => $product->type ?: 'accessory',
                'size' => $size,
                'variant' => $variant,
                'sku' => $product->sku,
                'in_stock' => $availableStock > 0,
                'stock' => (int) ($product->stock ?? 0),
                'available_stock' => $availableStock,
                'shipping' => $product->shipping ?: '1-3 Hari Kerja',
                'line_total' => $price * $qty,
                'weight' => $weight,
                'line_weight' => $weight * $qty,
            ];

            if ($weight <= 0) {
                $missingWeightProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }

            $items[] = $item;
            $subtotal += $item['line_total'];
            $totalQty += $qty;
            $totalWeight += max(0, $item['line_weight']);

            unset($cart[$key]);
            $cart[$newKey] = collect($item)
                ->except(['cart_key', 'line_total', 'weight', 'line_weight'])
                ->all();
        }

        if ($syncCart) {
            session(['cart' => $cart]);
            session(['checkout_selected_keys' => collect($items)->pluck('cart_key')->values()->all()]);
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_qty' => $totalQty,
            'total_weight' => $totalWeight,
            'count' => count($items),
            'missing_weight_products' => $missingWeightProducts,
            'unavailable_products' => $unavailableProducts,
            'items_hash' => $this->checkoutItemsHash($items),
        ];
    }

    protected function validatedShippingOption(array $data, array $checkout): ?array
    {
        $stored = session('checkout_shipping_options');

        if (!is_array($stored)) {
            return null;
        }

        if ((string) ($stored['destination_postal_code'] ?? '') !== (string) $data['destination_postal_code']) {
            return null;
        }

        if ((string) ($stored['items_hash'] ?? '') !== (string) $checkout['items_hash']) {
            return null;
        }

        $options = $stored['options'] ?? [];
        if (!is_array($options)) {
            return null;
        }

        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $sameCourier = (string) ($option['courier_code'] ?? '') === (string) $data['shipping_courier'];
            $sameServiceCode = (string) ($option['service_code'] ?? '') === (string) $data['shipping_service_code'];
            $sameServiceName = (string) ($option['service'] ?? '') === (string) $data['shipping_service_name'];
            $sameCost = (int) ($option['cost'] ?? 0) === (int) $data['shipping_cost'];

            if ($sameCourier && $sameServiceCode && $sameServiceName && $sameCost) {
                return $option;
            }
        }

        return null;
    }

    protected function checkoutItemsHash(array $items): string
    {
        $payload = collect($items)
            ->map(fn (array $item) => [
                'id' => (string) ($item['id'] ?? ''),
                'qty' => (int) ($item['qty'] ?? 1),
                'size' => (string) ($item['size'] ?? ''),
                'variant' => (string) ($item['variant'] ?? ''),
                'price' => (int) ($item['price'] ?? 0),
                'weight' => (int) ($item['weight'] ?? 0),
                'available_stock' => (int) ($item['available_stock'] ?? 0),
            ])
            ->values()
            ->all();

        return sha1(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function buildBiteshipItems(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                return [
                    'name' => (string) ($item['name'] ?? 'Produk'),
                    'description' => trim(implode(' | ', array_filter([
                        $item['type'] ?? null,
                        !empty($item['variant']) ? 'Variant: ' . $item['variant'] : null,
                        !empty($item['size']) ? 'Size: ' . $item['size'] : null,
                        !empty($item['sku']) ? 'SKU: ' . $item['sku'] : null,
                    ]))),
                    'value' => (int) ($item['price'] ?? 0),
                    'quantity' => (int) ($item['qty'] ?? 1),
                    'weight' => (int) ($item['weight'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    protected function availableStock(Product $product, ?string $variant = null, ?string $size = null): int
    {
        return $product->availableStockFor($variant, $size);
    }

    protected function decreaseStockForOption(Product $product, ?string $variant, ?string $size, int $qty): void
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
                $row->stock = max(0, (int) $row->stock - $qty);
                $row->save();

                $product->load('optionStocks');
                $product->stock = $product->optionStocks->sum(fn ($option) => max(0, (int) $option->stock));
                $product->in_stock = $product->stock > 0;
                $product->save();

                return;
            }
        }

        $product->stock = max(0, (int) ($product->stock ?? 0) - $qty);
        $product->in_stock = $product->stock > 0;
        $product->save();
    }

    protected function cleanNullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'PSM-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
