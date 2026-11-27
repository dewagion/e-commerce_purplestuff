<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['items', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Purple Stuff] Pesanan Dikirim - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-order-shipped',
            with: [
                'order' => $this->order,
                'ordersUrl' => route('orders.index'),
                'storeName' => config('app.name', 'Purple Stuff'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
