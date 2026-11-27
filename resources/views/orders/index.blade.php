@extends('layouts.app')

@section('content')
  <div class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="ps-flow-page-title"><h1 class="text-2xl font-extrabold text-slate-950">Pesanan Saya</h1></div>
        <p class="text-sm text-slate-500">Semua checkout yang berhasil akan muncul di sini.</p>
      </div>

      <a href="{{ route('dashboard') }}"
         class="ps-btn-soft ps-btn-sm">
        ← Back
      </a>
    </div>

    @if(session('success'))
      <div class="mt-6 ps-flow-alert-success px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="mt-6 ps-flow-alert-danger px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    @if($errors->any())
      <div class="mt-6 ps-flow-alert-danger px-4 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    @if($orders->isEmpty())
      <div class="mt-6 ps-flow-card p-8 text-slate-600">
        Belum ada pesanan.
      </div>
    @else
      <div class="mt-6 space-y-6">
        @foreach($orders as $order)
          @php
            $paymentBadge = match($order->payment_status) {
              'paid' => 'bg-green-50 text-green-700 border-green-200',
              'waiting_verification' => 'bg-blue-50 text-blue-700 border-blue-200',
              'expired' => 'bg-rose-50 text-rose-700 border-rose-200',
              default => 'bg-slate-100 text-slate-700 border-slate-200',
            };

            $paymentText = match($order->payment_status) {
              'paid' => 'Sudah Dibayar',
              'waiting_verification' => 'Menunggu Verifikasi',
              'expired' => 'Pembayaran Gagal',
              default => 'Menunggu Transfer',
            };

            $remainingSeconds = $order->paymentRemainingSeconds();

            $statusBadge = match($order->status) {
              'Dikirim' => 'bg-blue-50 text-blue-700 border-blue-200',
              'Selesai' => 'bg-green-50 text-green-700 border-green-200',
              'Diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
              'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
              default => 'bg-slate-50 text-slate-700 border-slate-200',
            };
          @endphp

          <div class="ps-flow-card p-6 sm:p-7">
            <div class="flex flex-col gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <div class="text-sm text-slate-500">Nomor Pesanan</div>
                <div class="text-lg font-extrabold text-slate-900">{{ $order->order_number }}</div>
                <div class="mt-1 text-sm text-slate-500">{{ optional($order->created_at)->translatedFormat('j F Y, H:i') }}</div>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                  {{ $order->status }}
                </span>
                <span class="ps-flow-pill">
                  {{ $order->payment_label }}
                </span>
                <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $paymentBadge }}">
                  {{ $paymentText }}
                </span>
              </div>
            </div>

            <div class="mt-5 grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
              <div>
                <div class="font-semibold text-slate-900">Produk</div>
                <div class="mt-4 space-y-4">
                  @foreach($order->items as $item)
                    <div class="ps-flow-product-row">
                      <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" class="h-16 w-16 rounded-2xl bg-white object-cover p-1 shadow-sm ring-1 ring-purple-100">

                      <div class="min-w-0 flex-1">
                        <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                        <div class="mt-1 space-y-1 text-sm text-slate-500">
                          @if(!empty($item->variant)) <div>Pilihan: {{ $item->variant }}</div> @endif
                          @if(!empty($item->size)) <div>Ukuran: {{ $item->size }}</div> @endif
                          @if(!empty($item->sku)) <div>SKU: {{ $item->sku }}</div> @endif
                          <div>Qty: {{ $item->qty }}</div>
                        </div>
                      </div>

                      <div class="text-right font-bold text-purple-600">
                        {{ \App\Support\Money::rupiah($item->line_total) }}
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              <div class="space-y-4">
                <div class="ps-flow-subcard p-4">
                  <div class="font-semibold text-slate-900">Data Penerima</div>
                  <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <div><span class="font-semibold text-slate-900">Nama:</span> {{ $order->customer_name }}</div>
                    <div><span class="font-semibold text-slate-900">Email:</span> {{ $order->customer_email }}</div>
                    <div><span class="font-semibold text-slate-900">Telepon:</span> {{ $order->customer_phone ?: '-' }}</div>
                    <div><span class="font-semibold text-slate-900">Alamat:</span> <span class="whitespace-pre-line">{{ $order->customer_address ?: '-' }}</span></div>
                    @if(!empty($order->notes))
                      <div><span class="font-semibold text-slate-900">Catatan:</span> {{ $order->notes }}</div>
                    @endif
                  </div>
                </div>

                <div class="ps-flow-subcard p-4">
                  <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="font-semibold text-slate-900">Pengiriman</div>

                    @if($order->tracking_number)
                      <button type="button"
                              data-copy-text="{{ $order->tracking_number }}"
                              class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Salin Resi
                      </button>
                    @endif
                  </div>

                  <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <div><span class="font-semibold text-slate-900">Kurir:</span> {{ $order->shipping_courier_name ?: strtoupper($order->shipping_courier ?: '-') }}</div>
                    <div><span class="font-semibold text-slate-900">Layanan:</span> {{ $order->shipping_service_name ?: '-' }}</div>
                    <div><span class="font-semibold text-slate-900">Estimasi:</span> {{ $order->shipping_etd ?: '-' }}</div>
                    <div><span class="font-semibold text-slate-900">Kode Pos Tujuan:</span> {{ $order->shipping_destination_postal_code ?: '-' }}</div>
                    <div><span class="font-semibold text-slate-900">Berat:</span> {{ number_format((int) $order->shipping_weight) }} gram</div>
                    <div><span class="font-semibold text-slate-900">Nomor Resi:</span> {{ $order->tracking_number ?: '-' }}</div>

                    @if(!empty($order->tracking_notes))
                      <div><span class="font-semibold text-slate-900">Catatan Pengiriman:</span> {{ $order->tracking_notes }}</div>
                    @endif

                    @if($order->tracking_updated_at)
                      <div><span class="font-semibold text-slate-900">Update Resi:</span> {{ $order->tracking_updated_at->translatedFormat('j F Y, H:i') }}</div>
                    @endif

                    <div>
                      <span class="font-semibold text-slate-900">Foto Resi:</span>
                      @if($order->shipping_receipt_path)
                        <a href="{{ route('orders.shipping-receipt', $order) }}" target="_blank" rel="noopener" class="font-semibold text-purple-700 hover:text-purple-500">Lihat foto resi</a>
                      @else
                        <span class="text-slate-500">Belum tersedia</span>
                      @endif
                    </div>
                  </div>

                  @if($order->shipping_receipt_path)
                    <a href="{{ route('orders.shipping-receipt', $order) }}" target="_blank" rel="noopener" class="mt-4 block">
                      <img src="{{ route('orders.shipping-receipt', $order) }}"
                           alt="Foto resi pengiriman"
                           class="max-h-72 w-full rounded-2xl bg-white object-contain p-2 shadow-sm ring-1 ring-slate-200"
                           loading="lazy">
                    </a>
                  @endif
                </div>

                <div class="ps-flow-subcard p-4">
                  <div class="font-semibold text-slate-900">Riwayat Tracking</div>

                  @if($order->trackingHistories->isEmpty())
                    <div class="mt-3 text-sm text-slate-500">Belum ada riwayat update resi.</div>
                  @else
                    <div class="mt-3 space-y-3">
                      @foreach($order->trackingHistories as $history)
                        <div class="rounded-2xl bg-white/70 px-4 py-3 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200/60">
                          <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="font-semibold text-slate-900">
                              {{ $history->event_type === 'tracking_updated' ? 'Tracking Diperbarui' : 'Status Diperbarui' }}
                            </div>
                            <div class="text-xs text-slate-500">
                              {{ optional($history->created_at)->translatedFormat('d M Y H:i') }}
                            </div>
                          </div>

                          <div class="mt-2 space-y-1">
                            <div><span class="font-semibold text-slate-900">Status:</span> {{ $history->status_snapshot ?: '-' }}</div>
                            <div><span class="font-semibold text-slate-900">Nomor Resi:</span> {{ $history->tracking_number ?: '-' }}</div>
                            @if($history->tracking_notes)
                              <div><span class="font-semibold text-slate-900">Catatan:</span> {{ $history->tracking_notes }}</div>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @endif
                </div>

                <div class="ps-flow-subcard p-4">
                  <div class="font-semibold text-slate-900">Alamat Toko Asal</div>
                  <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <div><span class="font-semibold text-slate-900">Toko:</span> {{ config('services.biteship.store_name') }}</div>
                    <div><span class="font-semibold text-slate-900">Alamat:</span> {{ config('services.biteship.store_address') }}</div>
                    <div><span class="font-semibold text-slate-900">Kode Pos Asal:</span> {{ $order->shipping_origin_postal_code ?: config('services.biteship.origin_postal_code') }}</div>
                  </div>
                </div>

                <div class="ps-flow-subcard p-4">
                  <div class="font-semibold text-slate-900">Ringkasan</div>
                  <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <div class="flex items-center justify-between gap-3"><span>Total Item</span><span class="font-semibold text-slate-900">{{ $order->total_items }}</span></div>
                    <div class="flex items-center justify-between gap-3"><span>Subtotal</span><span class="font-semibold text-slate-900">{{ \App\Support\Money::rupiah($order->subtotal) }}</span></div>
                    <div class="flex items-center justify-between gap-3"><span>Ongkir</span><span class="font-semibold text-slate-900">{{ \App\Support\Money::rupiah($order->shipping_cost) }}</span></div>
                    <div class="border-t border-gray-200 pt-2 flex items-center justify-between gap-3"><span class="font-bold text-slate-900">Total Transfer</span><span class="text-lg font-extrabold text-slate-900">{{ \App\Support\Money::rupiah($order->grand_total) }}</span></div>
                  </div>
                </div>

                <div class="ps-flow-subcard ps-flow-total-box p-4">
                  <div class="font-semibold text-slate-900">Instruksi Transfer</div>
                  <div class="mt-2 text-sm text-slate-600">Transfer sesuai total yang sudah termasuk ongkir.</div>
                  <div class="mt-3 space-y-1 text-sm text-slate-700">
                    <div>Bank: <span class="font-semibold">{{ config('services.biteship.bank_name') }}</span></div>
                    <div>No. Rekening: <span class="font-semibold">{{ config('services.biteship.bank_account_number') }}</span></div>
                    <div>Atas Nama: <span class="font-semibold">{{ config('services.biteship.bank_account_name') }}</span></div>
                    <div>Kode Pembayaran: <span class="font-semibold text-purple-700">{{ $order->payment_code }}</span></div>
                    <div>Total Transfer: <span class="font-bold">{{ \App\Support\Money::rupiah($order->grand_total) }}</span></div>
                  </div>

                  @if($order->payment_proof_path)
                    <div class="mt-4">
                      <div class="mb-2 text-sm font-semibold text-slate-900">Bukti Transfer</div>

                      <a href="{{ route('orders.payment-proof', $order) }}" target="_blank" rel="noopener" class="block">
                        <img
                          src="{{ route('orders.payment-proof', $order) }}"
                          alt="Bukti transfer"
                          class="w-full max-h-[420px] rounded-2xl bg-white object-contain p-2 shadow-sm ring-1 ring-purple-100"
                          loading="lazy"
                        >
                      </a>

                      <div class="mt-2 text-xs text-slate-500">
                        Diupload: {{ optional($order->payment_proof_uploaded_at)->translatedFormat('j F Y, H:i') }}
                      </div>
                    </div>
                  @endif

                  @if($order->payment_status === 'pending')
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                      Stok pesanan ini sedang dikunci.
                      @if($order->payment_deadline_at)
                        <div class="mt-1 text-xs">
                          Batas upload bukti: {{ $order->payment_deadline_at->translatedFormat('j F Y, H:i') }}
                          @if($remainingSeconds > 0)
                            · sisa sekitar {{ floor($remainingSeconds / 60) }} menit {{ $remainingSeconds % 60 }} detik
                          @endif
                        </div>
                      @endif
                    </div>

                    <a href="{{ route('orders.payment', $order) }}"
                       class="mt-4 ps-flow-primary-button">
                      Lanjutkan Pembayaran / Upload Bukti
                    </a>
                  @elseif($order->payment_status === 'expired')
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                      Pembayaran gagal karena bukti transfer tidak diupload dalam batas waktu. Stok sudah dikembalikan.
                    </div>
                  @elseif($order->payment_status === 'waiting_verification')
                    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                      Bukti transfer sudah diupload dan sedang menunggu verifikasi.
                    </div>
                    <a href="{{ route('orders.payment', $order) }}"
                       class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-sky-50/80 px-4 py-3 font-extrabold text-sky-700 shadow-sm ring-1 ring-sky-100 hover:bg-sky-100/80">
                      Lihat Detail Pembayaran
                    </a>
                  @else
                    <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                      Pembayaran sudah berhasil diverifikasi.
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <div id="copyToast"
       class="pointer-events-none fixed bottom-5 right-5 z-50 hidden rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-xl">
    Nomor resi berhasil disalin.
  </div>

  <script>
    document.addEventListener('click', async function (event) {
      const button = event.target.closest('[data-copy-text]');
      if (!button) return;

      const text = button.getAttribute('data-copy-text') || '';
      if (!text) return;

      try {
        await navigator.clipboard.writeText(text);
      } catch (error) {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
      }

      const toast = document.getElementById('copyToast');
      if (!toast) return;

      toast.classList.remove('hidden');
      clearTimeout(window.__copyToastTimer);
      window.__copyToastTimer = setTimeout(() => {
        toast.classList.add('hidden');
      }, 1800);
    });
  </script>
@endsection