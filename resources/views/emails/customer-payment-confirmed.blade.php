<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Dikonfirmasi</title>
</head>
<body style="margin:0; padding:0; background:#f6f3ff; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f3ff; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #eadcff;">
                    <tr>
                        <td style="padding:26px 28px; background:linear-gradient(135deg, #7c3aed, #a855f7); color:#ffffff;">
                            <div style="font-size:13px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; opacity:.9;">{{ $storeName }}</div>
                            <h1 style="margin:10px 0 0; font-size:24px; line-height:1.3;">Pembayaran Anda Sudah Dikonfirmasi</h1>
                            <p style="margin:8px 0 0; font-size:14px; line-height:1.6; opacity:.95;">
                                Terima kasih. Pembayaran untuk pesanan Anda sudah diverifikasi oleh admin dan pesanan akan masuk ke tahap proses.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 28px;">
                            <p style="margin:0 0 18px; font-size:14px; line-height:1.7; color:#374151;">
                                Halo <strong>{{ $order->customer_name ?: optional($order->user)->name ?: 'Pelanggan' }}</strong>,
                                pembayaran untuk pesanan berikut telah dinyatakan valid.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px; width:190px;">Nomor Pesanan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px; font-weight:700;">{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Status Pembayaran</td>
                                    <td style="padding:10px 0; color:#16a34a; font-size:14px; font-weight:800;">Dibayar / Paid</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Status Pesanan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px; font-weight:700;">{{ $order->status ?: 'Diproses' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Waktu Konfirmasi</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">
                                        {{ optional($order->paid_at)->timezone(config('app.timezone'))->format('d M Y H:i') ?? now()->timezone(config('app.timezone'))->format('d M Y H:i') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Metode Pembayaran</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">{{ $order->payment_label ?: 'Transfer Bank' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Kurir / Layanan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">
                                        {{ trim(($order->shipping_courier_name ?: strtoupper($order->shipping_courier ?: '')) . ' ' . ($order->shipping_service_name ?: '')) ?: '-' }}
                                    </td>
                                </tr>
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

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px;">
                                <tr>
                                    <td align="right" style="padding:5px 0; color:#6b7280; font-size:14px;">Subtotal</td>
                                    <td align="right" style="padding:5px 0; color:#111827; font-size:14px; width:160px;">Rp {{ number_format((int) $order->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td align="right" style="padding:5px 0; color:#6b7280; font-size:14px;">Ongkir</td>
                                    <td align="right" style="padding:5px 0; color:#111827; font-size:14px;">Rp {{ number_format((int) $order->shipping_cost, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td align="right" style="padding:8px 0; color:#111827; font-size:16px; font-weight:700;">Total Pembayaran</td>
                                    <td align="right" style="padding:8px 0; color:#6b21a8; font-size:18px; font-weight:800;">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            <div style="height:1px; background:#eee7ff; margin:18px 0;"></div>

                            <p style="font-size:14px; line-height:1.7; color:#374151; margin:0 0 18px;">
                                Pesanan Anda akan diproses oleh admin. Jika pesanan sudah dikirim, Anda akan mendapatkan pembaruan nomor resi melalui halaman pesanan dan email pengiriman.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="border-radius:12px; background:#7c3aed;">
                                        <a href="{{ $ordersUrl }}" style="display:inline-block; padding:12px 18px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:700;">Lihat Pesanan Saya</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px; background:#faf7ff; color:#6b7280; font-size:12px; line-height:1.6;">
                            Email ini dikirim otomatis oleh sistem {{ $storeName }} setelah admin mengonfirmasi pembayaran Anda.
                            Jika tombol tidak dapat dibuka, silakan login ke website lalu buka menu pesanan Anda.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
