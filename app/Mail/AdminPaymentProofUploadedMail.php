<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPaymentProofUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['items', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Purple Stuff] Bukti Pembayaran Baru - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-payment-proof-uploaded',
            with: [
                'order' => $this->order,
                'adminOrderUrl' => route('admin.orders.show', $this->order),
                'paymentProofUrl' => route('admin.orders.payment-proof', $this->order),
                'storeName' => config('app.name', 'Purple Stuff'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
