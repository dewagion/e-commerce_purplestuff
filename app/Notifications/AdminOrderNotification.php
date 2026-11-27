<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $type,
        public string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'grand_total' => (int) $this->order->grand_total,
            'status' => $this->order->status,
            'payment_status' => $this->order->payment_status,
            'message' => $this->message,
            'link' => route('admin.orders.show', $this->order),
        ];
    }
}