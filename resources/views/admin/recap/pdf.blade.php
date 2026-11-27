<!DOCTYPE html>
<html lang="id">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Rekap Penjualan Purple Stuff - {{ $salesRecap['label'] }}</title>
  <style>
    @page { margin: 18px 20px 22px; }

    body {
      font-family: DejaVu Sans, sans-serif;
      color: #111827;
      font-size: 10px;
      line-height: 1.42;
      margin: 0;
      padding: 0;
      background: #ffffff;
    }

    h1, h2, h3, h4, p { margin: 0; }

    .letterhead {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
      border-bottom: 3px solid #7c3aed;
      padding-bottom: 10px;
    }

    .brand-cell { width: 56%; vertical-align: top; }
    .meta-cell { width: 44%; vertical-align: top; text-align: right; }

    .brand-name {
      font-size: 20px;
      font-weight: 800;
      letter-spacing: .5px;
      color: #5b21b6;
      text-transform: uppercase;
    }

    .brand-subtitle {
      margin-top: 3px;
      color: #475569;
      font-size: 9.5px;
    }

    .report-title {
      font-size: 18px;
      font-weight: 800;
      color: #111827;
      margin-top: 13px;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    .report-subtitle {
      color: #475569;
      font-size: 9.8px;
      margin-bottom: 13px;
    }

    .meta-box {
      display: inline-block;
      border: 1px solid #ddd6fe;
      background: #f5f3ff;
      border-radius: 8px;
      padding: 8px 10px;
      min-width: 210px;
      text-align: left;
    }

    .meta-row {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.3px;
    }

    .meta-row td {
      padding: 2px 0;
      vertical-align: top;
    }

    .meta-label {
      color: #64748b;
      width: 74px;
    }

    .meta-value {
      color: #111827;
      font-weight: 700;
    }

    .summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }

    .summary-table td {
      width: 25%;
      border: 1px solid #e5e7eb;
      background: #fafafa;
      padding: 9px 10px;
      vertical-align: top;
    }

    .summary-table .summary-main {
      background: #f5f3ff;
      border-color: #ddd6fe;
    }

    .summary-label {
      color: #64748b;
      font-size: 8.8px;
      text-transform: uppercase;
      letter-spacing: .25px;
      margin-bottom: 5px;
    }

    .summary-value {
      font-size: 14px;
      font-weight: 800;
      color: #111827;
    }

    .summary-value.big {
      font-size: 15px;
      color: #6d28d9;
    }

    .section {
      margin-top: 13px;
      page-break-inside: avoid;
    }

    .section-title {
      font-size: 12.3px;
      font-weight: 800;
      color: #111827;
      margin-bottom: 3px;
      padding-bottom: 4px;
      border-bottom: 1px solid #e5e7eb;
      text-transform: uppercase;
      letter-spacing: .2px;
    }

    .section-note {
      color: #64748b;
      font-size: 9.2px;
      margin-bottom: 7px;
    }

    .info-grid {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }

    .info-grid td {
      width: 33.333%;
      border: 1px solid #e5e7eb;
      padding: 8px 9px;
      vertical-align: top;
    }

    .info-label {
      color: #64748b;
      font-size: 8.8px;
      margin-bottom: 4px;
    }

    .info-value {
      color: #111827;
      font-size: 11px;
      font-weight: 800;
    }

    .report-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 9.1px;
    }

    .report-table th,
    .report-table td {
      border: 1px solid #e5e7eb;
      padding: 5px 6px;
      vertical-align: top;
    }

    .report-table th {
      background: #f3f4f6;
      color: #374151;
      font-weight: 800;
      text-align: left;
    }

    .report-table tbody tr:nth-child(even) td { background: #fbfbfb; }

    .detail-table {
      font-size: 8.1px;
    }

    .detail-table th,
    .detail-table td {
      padding: 4px 4px;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .muted { color: #64748b; }
    .bold { font-weight: 800; }
    .purple { color: #6d28d9; }
    .green { color: #15803d; }
    .orange { color: #c2410c; }
    .red { color: #b91c1c; }

    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 999px;
      font-size: 8px;
      font-weight: 700;
      background: #f1f5f9;
      color: #334155;
      white-space: nowrap;
    }

    .badge-green { background: #dcfce7; color: #166534; }
    .badge-yellow { background: #fef3c7; color: #92400e; }
    .badge-orange { background: #ffedd5; color: #9a3412; }
    .badge-blue { background: #dbeafe; color: #1e40af; }
    .badge-purple { background: #ede9fe; color: #5b21b6; }
    .badge-red { background: #fee2e2; color: #991b1b; }

    .empty {
      text-align: center;
      color: #64748b;
      padding: 12px 10px;
    }

    .page-break { page-break-before: always; }

    .calculation-note {
      margin-top: 8px;
      padding: 8px 10px;
      border: 1px solid #e5e7eb;
      background: #f8fafc;
      color: #475569;
      font-size: 9px;
    }

    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 22px;
      font-size: 9.5px;
    }

    .signature-table td {
      width: 50%;
      vertical-align: top;
      padding-top: 4px;
    }

    .signature-space { height: 46px; }

    .footer-note {
      margin-top: 12px;
      color: #64748b;
      font-size: 8.8px;
      border-top: 1px solid #e5e7eb;
      padding-top: 7px;
    }
  </style>
</head>
<body>
@php
  $money = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');

  $paymentLabel = function (?string $status): string {
      return match ($status) {
          'paid' => 'Dibayar',
          'pending' => 'Menunggu Transfer',
          'waiting_verification' => 'Menunggu Verifikasi',
          'expired' => 'Kedaluwarsa',
          'failed' => 'Gagal',
          default => $status ? ucwords(str_replace('_', ' ', $status)) : '-',
      };
  };

  $paymentBadge = function (?string $status): string {
      return match ($status) {
          'paid' => 'badge-green',
          'pending' => 'badge-yellow',
          'waiting_verification' => 'badge-orange',
          'expired', 'failed' => 'badge-red',
          default => '',
      };
  };

  $orderBadge = function (?string $status): string {
      return match ($status) {
          'Diproses' => 'badge-blue',
          'Dikirim' => 'badge-purple',
          'Selesai' => 'badge-green',
          'Dibatalkan' => 'badge-red',
          'Menunggu Pembayaran' => 'badge-yellow',
          'Menunggu Verifikasi Pembayaran' => 'badge-orange',
          default => '',
      };
  };

  $periodText = $salesRecap['start']->translatedFormat('d F Y') . ' sampai ' . $salesRecap['end']->translatedFormat('d F Y');
@endphp

  <table class="letterhead">
    <tr>
      <td class="brand-cell">
        <div class="brand-name">Purple Stuff</div>
        <div class="brand-subtitle">Laporan penjualan website e-commerce merchandise</div>
        <div class="report-title">Rekap Penjualan {{ $salesRecap['label'] }}</div>
        <div class="report-subtitle">Periode {{ $periodText }}</div>
      </td>
      <td class="meta-cell">
        <div class="meta-box">
          <table class="meta-row">
            <tr>
              <td class="meta-label">Tanggal Cetak</td>
              <td class="meta-value">{{ now()->translatedFormat('d F Y H:i') }} WITA</td>
            </tr>
            <tr>
              <td class="meta-label">Jenis Rekap</td>
              <td class="meta-value">{{ $salesRecap['label'] }}</td>
            </tr>
            <tr>
              <td class="meta-label">Status Data</td>
              <td class="meta-value">Final berdasarkan transaksi tersimpan</td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>

  <table class="summary-table">
    <tr>
      <td class="summary-main">
        <div class="summary-label">Total Omzet</div>
        <div class="summary-value big">{{ $money($salesRecap['revenue_total']) }}</div>
      </td>
      <td>
        <div class="summary-label">Total Pesanan</div>
        <div class="summary-value">{{ $salesRecap['order_count'] }}</div>
      </td>
      <td>
        <div class="summary-label">Item Terjual</div>
        <div class="summary-value">{{ $salesRecap['items_sold'] }}</div>
      </td>
      <td>
        <div class="summary-label">Rata-rata Order</div>
        <div class="summary-value">{{ $money($salesRecap['average_order']) }}</div>
      </td>
    </tr>
  </table>

  <div class="section">
    <div class="section-title">Ringkasan Keuangan</div>
    <div class="section-note">Bagian ini merangkum nilai produk, ongkos kirim, pembayaran valid, dan nilai yang belum selesai pada periode laporan.</div>
    <table class="info-grid">
      <tr>
        <td>
          <div class="info-label">Subtotal Produk</div>
          <div class="info-value">{{ $money($salesRecap['subtotal_total']) }}</div>
        </td>
        <td>
          <div class="info-label">Total Ongkos Kirim</div>
          <div class="info-value">{{ $money($salesRecap['shipping_total']) }}</div>
        </td>
        <td>
          <div class="info-label">Omzet Dibayar</div>
          <div class="info-value green">{{ $money($salesRecap['paid_revenue']) }}</div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="info-label">Belum Dibayar atau Verifikasi</div>
          <div class="info-value orange">{{ $money($salesRecap['unpaid_revenue']) }}</div>
        </td>
        <td>
          <div class="info-label">Pesanan Dibayar</div>
          <div class="info-value green">{{ $salesRecap['paid_count'] }} pesanan</div>
        </td>
        <td>
          <div class="info-label">Order Selesai</div>
          <div class="info-value purple">{{ $salesRecap['completed_count'] }} pesanan</div>
        </td>
      </tr>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Ringkasan Operasional Pesanan</div>
    <div class="section-note">Bagian ini menunjukkan posisi transaksi dari sisi pembayaran dan proses pemesanan.</div>
    <table class="report-table">
      <thead>
        <tr>
          <th>Indikator</th>
          <th class="text-right">Jumlah</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Menunggu Transfer</td>
          <td class="text-right bold">{{ $salesRecap['pending_count'] }}</td>
          <td>Pesanan sudah dibuat, tetapi pelanggan belum mengunggah bukti transfer.</td>
        </tr>
        <tr>
          <td>Menunggu Verifikasi</td>
          <td class="text-right bold">{{ $salesRecap['verification_count'] }}</td>
          <td>Bukti transfer sudah diunggah dan menunggu validasi admin.</td>
        </tr>
        <tr>
          <td>Diproses</td>
          <td class="text-right bold">{{ $salesRecap['processed_count'] }}</td>
          <td>Pesanan sudah dibayar dan sedang diproses admin.</td>
        </tr>
        <tr>
          <td>Dikirim atau Selesai</td>
          <td class="text-right bold">{{ $salesRecap['shipped_count'] }}</td>
          <td>Pesanan sudah masuk tahap pengiriman atau telah selesai.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Rekap Penjualan Harian</div>
    <div class="section-note">Tabel ini membantu admin melihat tanggal transaksi, jumlah pesanan, item terjual, dan omzet harian.</div>
    <table class="report-table">
      <thead>
        <tr>
          <th style="width: 16%;">Tanggal</th>
          <th class="text-right" style="width: 12%;">Pesanan</th>
          <th class="text-right" style="width: 10%;">Item</th>
          <th class="text-right" style="width: 20%;">Subtotal Produk</th>
          <th class="text-right" style="width: 18%;">Ongkir</th>
          <th class="text-right" style="width: 24%;">Omzet</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salesRecap['daily_recap'] as $day)
          <tr>
            <td>{{ \Illuminate\Support\Carbon::parse($day->sale_date)->translatedFormat('d M Y') }}</td>
            <td class="text-right">{{ (int) $day->total_orders }}</td>
            <td class="text-right">{{ (int) $day->total_items }}</td>
            <td class="text-right">{{ $money($day->subtotal_total) }}</td>
            <td class="text-right">{{ $money($day->shipping_total) }}</td>
            <td class="text-right bold">{{ $money($day->revenue_total) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty">Belum ada penjualan pada periode ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Produk Terlaris</div>
    <div class="section-note">Daftar produk dengan jumlah penjualan tertinggi pada periode laporan.</div>
    <table class="report-table">
      <thead>
        <tr>
          <th style="width: 8%;" class="text-center">No.</th>
          <th>Nama Produk</th>
          <th class="text-right" style="width: 16%;">Terjual</th>
          <th class="text-right" style="width: 24%;">Omzet Produk</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salesRecap['top_products'] as $item)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="bold">{{ $item->product_name }}</td>
            <td class="text-right">{{ (int) $item->sold_qty }}</td>
            <td class="text-right bold">{{ $money($item->total_revenue) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="empty">Belum ada produk terjual pada periode ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Distribusi Status Pembayaran</div>
    <table class="report-table">
      <thead>
        <tr>
          <th>Status Pembayaran</th>
          <th class="text-right">Jumlah Pesanan</th>
          <th class="text-right">Total Nilai</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salesRecap['payment_breakdown'] as $payment)
          <tr>
            <td><span class="badge {{ $paymentBadge($payment->payment_status) }}">{{ $paymentLabel($payment->payment_status) }}</span></td>
            <td class="text-right">{{ (int) $payment->total_orders }}</td>
            <td class="text-right bold">{{ $money($payment->total_revenue) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="empty">Belum ada data status pembayaran.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Distribusi Status Pesanan</div>
    <table class="report-table">
      <thead>
        <tr>
          <th>Status Pesanan</th>
          <th class="text-right">Jumlah Pesanan</th>
          <th class="text-right">Total Nilai</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salesRecap['status_breakdown'] as $status)
          <tr>
            <td><span class="badge {{ $orderBadge($status->status) }}">{{ $status->status ?: '-' }}</span></td>
            <td class="text-right">{{ (int) $status->total_orders }}</td>
            <td class="text-right bold">{{ $money($status->total_revenue) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="empty">Belum ada data status pesanan.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="calculation-note">
    <span class="bold">Catatan perhitungan:</span>
    Laporan tidak menghitung pesanan berstatus Dibatalkan. Omzet dihitung dari grand total pesanan, yaitu subtotal produk ditambah ongkos kirim. Nilai belum dibayar atau verifikasi berasal dari selisih total omzet dengan omzet yang sudah berstatus paid.
  </div>

  <div class="page-break"></div>

  <div class="section">
    <div class="section-title">Rincian Produk Terjual</div>
    <div class="section-note">Rincian ini digunakan untuk mencocokkan tanggal transaksi, nomor pesanan, pelanggan, produk, variasi, jumlah, harga, dan subtotal item.</div>
    <table class="report-table detail-table">
      <thead>
        <tr>
          <th style="width: 7%;" class="text-center">No.</th>
          <th style="width: 11%;">Tanggal</th>
          <th style="width: 13%;">No. Pesanan</th>
          <th style="width: 12%;">Pelanggan</th>
          <th>Produk</th>
          <th style="width: 10%;">Opsi</th>
          <th class="text-right" style="width: 6%;">Qty</th>
          <th class="text-right" style="width: 11%;">Harga</th>
          <th class="text-right" style="width: 12%;">Subtotal</th>
          <th style="width: 12%;">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salesRecap['product_details'] as $detail)
          @php
            $options = collect([$detail->variant, $detail->size])->filter()->implode(' / ');
          @endphp
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ \Illuminate\Support\Carbon::parse($detail->order_created_at)->translatedFormat('d M Y') }}</td>
            <td>{{ $detail->order_number }}</td>
            <td>{{ $detail->customer_name }}</td>
            <td class="bold">{{ $detail->product_name }}</td>
            <td>{{ $options ?: '-' }}</td>
            <td class="text-right">{{ (int) $detail->qty }}</td>
            <td class="text-right">{{ $money($detail->price) }}</td>
            <td class="text-right bold">{{ $money($detail->line_total) }}</td>
            <td>
              <span class="badge {{ $orderBadge($detail->status) }}">{{ $detail->status ?: '-' }}</span><br>
              <span class="muted">{{ $paymentLabel($detail->payment_status) }}</span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="empty">Belum ada rincian produk terjual pada periode ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <table class="signature-table">
    <tr>
      <td>
        <div class="muted">Diperiksa oleh,</div>
        <div class="signature-space"></div>
        <div class="bold">Admin Purple Stuff</div>
      </td>
      <td class="text-right">
        <div class="muted">Dicetak otomatis oleh sistem</div>
        <div class="signature-space"></div>
        <div class="bold">Website E-Commerce Purple Stuff</div>
      </td>
    </tr>
  </table>

  <div class="footer-note">
    Laporan ini dibuat dari dashboard admin Purple Stuff. Data bersumber dari tabel orders dan order_items pada database sistem. Gunakan laporan ini sebagai rekap penjualan periode berjalan dan bahan pengecekan transaksi.
  </div>
</body>
</html>
