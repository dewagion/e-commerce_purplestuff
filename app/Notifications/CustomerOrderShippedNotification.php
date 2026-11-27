<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerOrderShippedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_tracking_updated',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'tracking_number' => $this->order->tracking_number,
            'tracking_notes' => $this->order->tracking_notes,
            'status' => $this->order->status,
            'message' => 'Pesanan ' . $this->order->order_number . ' sudah dikirim. Nomor resi: ' . ($this->order->tracking_number ?: '-'),
            'link' => route('orders.index'),
        ];
    }
}
