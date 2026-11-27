<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTrackingHistory extends Model
{
    protected $fillable = [
        'order_id',
        'admin_user_id',
        'tracking_number',
        'tracking_notes',
        'status_snapshot',
        'event_type',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}