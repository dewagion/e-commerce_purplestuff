<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran Baru</title>
</head>
<body style="margin:0; padding:0; background:#f6f3ff; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f3ff; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #eadcff;">
                    <tr>
                        <td style="padding:26px 28px; background:linear-gradient(135deg, #7c3aed, #a855f7); color:#ffffff;">
                            <div style="font-size:13px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; opacity:.9;">{{ $storeName }}</div>
                            <h1 style="margin:10px 0 0; font-size:24px; line-height:1.3;">Bukti Pembayaran Baru</h1>
                            <p style="margin:8px 0 0; font-size:14px; line-height:1.6; opacity:.95;">
                                User telah mengunggah bukti transfer dan pesanan menunggu verifikasi admin.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px; width:180px;">Nomor Pesanan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px; font-weight:700;">{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Nama Pelanggan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">{{ $order->customer_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Email Pelanggan</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">{{ $order->customer_email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Telepon</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">{{ $order->customer_phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Status Pembayaran</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px; font-weight:700;">{{ str_replace('_', ' ', $order->payment_status ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0; color:#6b7280; font-size:13px;">Waktu Upload Bukti</td>
                                    <td style="padding:10px 0; color:#111827; font-size:14px;">
                                        {{ optional($order->payment_proof_uploaded_at)->timezone(config('app.timezone'))->format('d M Y H:i') ?? '-' }}
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
                                Silakan buka halaman detail pesanan admin untuk mengecek bukti transfer dan mengubah status pembayaran jika bukti sudah sesuai.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="border-radius:12px; background:#7c3aed;">
                                        <a href="{{ $adminOrderUrl }}" style="display:inline-block; padding:12px 18px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:700;">Buka Detail Pesanan</a>
                                    </td>
                                    <td style="width:10px;"></td>
                                    <td style="border-radius:12px; background:#f3e8ff;">
                                        <a href="{{ $paymentProofUrl }}" style="display:inline-block; padding:12px 18px; color:#6b21a8; text-decoration:none; font-size:14px; font-weight:700;">Lihat Bukti Transfer</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px; background:#faf7ff; color:#6b7280; font-size:12px; line-height:1.6;">
                            Email ini dikirim otomatis oleh sistem {{ $storeName }} setelah pelanggan mengunggah bukti transfer.
                            Jika tombol tidak dapat dibuka, silakan login ke dashboard admin dan buka menu Pesanan.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
