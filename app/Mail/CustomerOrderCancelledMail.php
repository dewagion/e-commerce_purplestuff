<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $reason = 'Pesanan dibatalkan.')
    {
        $this->order->loadMissing(['items', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Purple Stuff] Pesanan Dibatalkan - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-order-cancelled',
            with: [
                'order' => $this->order,
                'reason' => $this->reason,
                'productsUrl' => route('products.index'),
                'storeName' => config('app.name', 'Purple Stuff'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
