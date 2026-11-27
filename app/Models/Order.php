<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'notes',
        'subtotal',
        'shipping_cost',
        'grand_total',
        'total_items',
        'shipping_courier',
        'shipping_courier_name',
        'shipping_service_code',
        'shipping_service_name',
        'shipping_etd',
        'shipping_weight',
        'shipping_origin_postal_code',
        'shipping_destination_postal_code',
        'tracking_number',
        'tracking_notes',
        'tracking_updated_at',
        'shipping_receipt_path',
        'shipping_receipt_uploaded_at',
        'payment_method',
        'payment_label',
        'payment_status',
        'payment_code',
        'payment_deadline_at',
        'payment_expired_at',
        'payment_proof_path',
        'payment_proof_uploaded_at',
        'status',
        'paid_at',
        'confirmed_at',
        'shipped_at',
        'completed_at',
        'stock_reserved_at',
        'stock_released_at',
        'stock_release_reason',
        'customer_order_cancelled_email_sent_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'grand_total' => 'integer',
        'total_items' => 'integer',
        'shipping_weight' => 'integer',
        'payment_deadline_at' => 'datetime',
        'payment_expired_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
        'tracking_updated_at' => 'datetime',
        'shipping_receipt_uploaded_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'stock_reserved_at' => 'datetime',
        'stock_released_at' => 'datetime',
        'customer_order_cancelled_email_sent_at' => 'datetime',
    ];


    public function isPaymentExpired(): bool
    {
        if ($this->payment_status === 'expired') {
            return true;
        }

        return $this->payment_status === 'pending'
            && empty($this->payment_proof_path)
            && $this->payment_deadline_at
            && now()->greaterThanOrEqualTo($this->payment_deadline_at);
    }

    public function paymentRemainingSeconds(): int
    {
        if (!$this->payment_deadline_at || $this->payment_status !== 'pending') {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->payment_deadline_at, false));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function trackingHistories(): HasMany
    {
        return $this->hasMany(OrderTrackingHistory::class)->latest();
    }
}