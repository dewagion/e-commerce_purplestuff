<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dibatalkan</title>
</head>
<body style="margin:0; padding:0; background:#f6f3ff; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f3ff; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #eadcff;">
                    <tr>
                        <td style="padding:26px 28px; background:linear-gradient(135deg, #e11d48, #9333ea); color:#ffffff;">
                            <div style="font-size:13px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; opacity:.9;">{{ $storeName }}</div>
                            <h1 style="margin:10px 0 0; font-size:24px; line-height:1.3;">Pesanan Anda Dibatalkan</h1>
                            <p style="margin:8px 0 0; font-size:14px; line-height:1.6; opacity:.95;">
                                Pesanan tidak dapat dilanjutkan. Detail pembatalan dapat dilihat di bawah ini.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 28px;">
                            <p style="margin:0 0 18px; font-size:14px; line-height:1.7; color:#374151;">
                                Halo <strong>{{ $order->customer_name ?: optional($order->user)->name ?: 'Pelanggan' }}</strong>, pesanan berikut telah dibatalkan.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px; width:190px;">Nomor Pesanan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px; font-weight:700;">{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Status Pembayaran</td>
                                    <td style="padding:10px 0; color:#e11d48; font-size:14px; font-weight:800;">{{ $order->payment_status ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Status Pesanan</td>
                                    <td style="padding:10px 0; color:#e11d48; font-size:14px; font-weight:800;">{{ $order->status ?: 'Dibatalkan' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Alasan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">{{ $reason }}</td>
                                </tr>
                                @if($order->payment_expired_at)
                                    <tr>
                                        <td style="padding:10px 0; color:#6b7280; font-size:13px;">Waktu Pembatalan</td>
                                        <td style="padding:10px 0; color:#111827; font-size:14px;">{{ $order->payment_expired_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>

                            <div style="height:1px; background:#eee7ff; margin:18px 0;"></div>

                            <h2 style="font-size:16px; margin:0 0 12px; color:#111827;">Ringkasan Produk</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:1px solid #eee7ff; border-radius:12px; overflow:hidden;">
                                <thead>
                                    <tr style="background:#faf7ff;">
                                        <th align="left" style="padding:12px; font-size:12px; color:#6b21a8; border-bottom:1px solid #eee7ff;">Produk</th>
                                        <th align="center" style="padding:12px; font-size:12px; color:#6b21a8; border-bottom:1px solid #eee7ff;">Qty</th>
                                        <th align="right" style="padding:12px; font-size:12px; color:#6b21a8; border-bottom:1px solid #eee7ff;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td style="padding:12px; font-size:13px; color:#111827; border-bottom:1px solid #f3eefc;">
                                                <strong>{{ $item->product_name }}</strong><br>
                                                <span style="color:#6b7280; font-size:12px;">
                                                    @if($item->variant)
                                                        Variant: {{ $item->variant }}
                                                    @endif
                                                    @if($item->size)
                                                        @if($item->variant) | @endif
                                                        Ukuran: {{ $item->size }}
                                                    @endif
                                                    @if(!$item->variant && !$item->size)
                                                        -
                                                    @endif
                                                </span>
                                            </td>
                                            <td align="center" style="padding:12px; font-size:13px; color:#111827; border-bottom:1px solid #f3eefc;">{{ $item->qty }}</td>
                                            <td align="right" style="padding:12px; font-size:13px; color:#111827; border-bottom:1px solid #f3eefc;">Rp {{ number_format((int) $item->line_total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div style="height:1px; background:#eee7ff; margin:18px 0;"></div>

                            <p style="font-size:14px; line-height:1.7; color:#374151; margin:0 0 18px;">
                                Jika pesanan dibatalkan karena pembayaran melewati batas waktu, stok akan dikembalikan ke sistem. Anda dapat membuat pesanan baru selama produk masih tersedia.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="border-radius:12px; background:#7c3aed;">
                                        <a href="{{ $productsUrl }}" style="display:inline-block; padding:12px 18px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:700;">Lihat Produk</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px; background:#faf7ff; color:#6b7280; font-size:12px; line-height:1.6;">
                            Email ini dikirim otomatis oleh sistem {{ $storeName }} ketika pesanan dibatalkan atau pembayaran melewati batas waktu.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
