@extends('layouts.admin')

@php
  $heading = 'Detail Pesanan';
  $subheading = $order->order_number;

  $statusBadgeClass = match($order->status) {
      'Dikirim' => 'bg-blue-50 text-blue-700 border-blue-200',
      'Selesai' => 'bg-green-50 text-green-700 border-green-200',
      'Diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
      'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
      default => 'bg-slate-50 text-slate-700 border-slate-200',
  };

  $paymentBadgeClass = match($order->payment_status) {
      'paid' => 'bg-green-50 text-green-700 border-green-200',
      'waiting_verification' => 'bg-blue-50 text-blue-700 border-blue-200',
      'expired' => 'bg-rose-50 text-rose-700 border-rose-200',
      default => 'bg-slate-50 text-slate-700 border-slate-200',
  };

  $paymentText = match($order->payment_status) {
      'paid' => 'Sudah Dibayar',
      'waiting_verification' => 'Menunggu Verifikasi',
      'expired' => 'Pembayaran Gagal',
      default => 'Pending',
  };


  $paymentLocked = in_array($order->payment_status, ['paid', 'expired'], true);
  $paymentCanBeFailed = !in_array($order->payment_status, ['paid', 'expired'], true);
  $orderRequiresPaidStatuses = ['Diproses', 'Dikirim', 'Selesai'];
  $paymentStatusOptions = [
      'pending' => 'Pending',
      'waiting_verification' => 'Menunggu Verifikasi',
      'paid' => 'Paid',
      'expired' => 'Pembayaran Gagal',
  ];
  $orderStatusOptions = ['Menunggu Pembayaran','Menunggu Verifikasi Pembayaran','Diproses','Dikirim','Selesai','Dibatalkan'];
@endphp

@section('content')
  <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
    <div class="space-y-6">
      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-lg font-bold">Produk</h2>

          <div class="flex flex-wrap gap-2">
            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
              {{ $order->status }}
            </span>
            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $paymentBadgeClass }}">
              {{ $paymentText }}
            </span>
          </div>
        </div>

        <div class="mt-4 space-y-4">
          @foreach($order->items as $item)
            <div class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
              <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}"
                   class="h-16 w-16 rounded-xl object-cover bg-white">

              <div class="min-w-0 flex-1">
                <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                <div class="mt-1 space-y-1 text-sm text-slate-500">
                  @if($item->variant)<div>Varian: {{ $item->variant }}</div>@endif
                  @if($item->size)<div>Ukuran: {{ $item->size }}</div>@endif
                  @if($item->sku)<div>SKU: {{ $item->sku }}</div>@endif
                  <div>Qty: {{ $item->qty }}</div>
                </div>
              </div>

              <div class="font-bold text-purple-700">
                {{ \App\Support\Money::rupiah($item->line_total) }}
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Bukti Transfer</h2>

        @if($order->payment_proof_path)
          <div class="mt-4">
            <a href="{{ route('admin.orders.payment-proof', $order) }}" target="_blank" rel="noopener" class="block">
              <img
                src="{{ route('admin.orders.payment-proof', $order) }}"
                alt="Bukti transfer"
                class="w-full max-h-[500px] rounded-2xl border border-slate-200 bg-white object-contain"
                loading="lazy"
              >
            </a>

            <div class="mt-2 text-sm text-slate-500">
              Diupload: {{ optional($order->payment_proof_uploaded_at)->translatedFormat('d F Y H:i') }}
            </div>
          </div>
        @else
          <div class="mt-4 text-sm text-slate-500">Belum ada bukti transfer.</div>
        @endif
      </div>

      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Riwayat Update Resi</h2>

        @if($order->trackingHistories->isEmpty())
          <div class="mt-4 rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-sm text-slate-500">
            Belum ada riwayat update resi.
          </div>
        @else
          <div class="mt-4 space-y-3">
            @foreach($order->trackingHistories as $history)
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <div class="text-sm font-semibold text-slate-900">
                    {{ $history->event_type === 'tracking_updated' ? 'Tracking Diperbarui' : 'Status Diperbarui' }}
                  </div>
                  <div class="text-xs text-slate-500">
                    {{ optional($history->created_at)->translatedFormat('d M Y H:i') }}
                  </div>
                </div>

                <div class="mt-2 space-y-1 text-sm text-slate-600">
                  <div><span class="font-semibold text-slate-900">Status:</span> {{ $history->status_snapshot ?: '-' }}</div>
                  <div><span class="font-semibold text-slate-900">Nomor Resi:</span> {{ $history->tracking_number ?: '-' }}</div>
                  @if($history->tracking_notes)
                    <div><span class="font-semibold text-slate-900">Catatan:</span> {{ $history->tracking_notes }}</div>
                  @endif
                  <div><span class="font-semibold text-slate-900">Diupdate oleh:</span> {{ $history->admin?->name ?: 'Admin' }}</div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <div class="space-y-6">
      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Ringkasan Pesanan</h2>

        <div class="mt-4 space-y-2 text-sm text-slate-600">
          <div><span class="font-semibold text-slate-900">Nomor Order:</span> {{ $order->order_number }}</div>
          <div><span class="font-semibold text-slate-900">Nama:</span> {{ $order->customer_name }}</div>
          <div><span class="font-semibold text-slate-900">Email:</span> {{ $order->customer_email }}</div>
          <div><span class="font-semibold text-slate-900">Telepon:</span> {{ $order->customer_phone ?: '-' }}</div>
          <div><span class="font-semibold text-slate-900">Alamat:</span> <span class="whitespace-pre-line">{{ $order->customer_address }}</span></div>
          @if($order->notes)
            <div><span class="font-semibold text-slate-900">Catatan:</span> {{ $order->notes }}</div>
          @endif
        </div>

        <div class="mt-4 border-t pt-4 space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Subtotal</span>
            <span class="font-semibold">{{ \App\Support\Money::rupiah($order->subtotal) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Ongkir</span>
            <span class="font-semibold">{{ \App\Support\Money::rupiah($order->shipping_cost) }}</span>
          </div>
          <div class="flex items-center justify-between border-t pt-3">
            <span class="font-bold text-slate-900">Grand Total</span>
            <span class="text-lg font-extrabold text-slate-900">{{ \App\Support\Money::rupiah($order->grand_total) }}</span>
          </div>
          @if($order->payment_deadline_at)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
              Batas upload bukti: {{ $order->payment_deadline_at->translatedFormat('d M Y H:i') }}
              @if($order->payment_expired_at)
                <br>Expired: {{ $order->payment_expired_at->translatedFormat('d M Y H:i') }}
              @endif
            </div>
          @endif
        </div>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Update Pembayaran</h2>

        @if($order->payment_status === 'expired')
          <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            Pembayaran sudah berstatus gagal/expired. Pesanan dibatalkan dan stok dikembalikan. Status ini dikunci agar tidak terjadi stok masuk dua kali.
          </div>
        @elseif($order->payment_status === 'paid')
          <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            Pembayaran sudah paid. Jika pesanan dibatalkan setelah paid, gunakan bagian Status Pesanan agar catatan pembayaran tetap sinkron.
          </div>
        @else
          <div class="mt-4 rounded-xl border border-purple-100 bg-purple-50 px-4 py-3 text-sm text-purple-700">
            Pembayaran Gagal dapat dipilih untuk membatalkan pesanan yang belum paid, misalnya bukti transfer tidak valid. Jika user tidak upload bukti sampai batas waktu, status gagal juga dapat diproses otomatis oleh sistem.
          </div>
        @endif

        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 space-y-4">
          @csrf
          @method('PATCH')
          <input type="hidden" name="action_type" value="payment">

          <select name="payment_status"
                  @disabled($paymentLocked)
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500">
            @foreach($paymentStatusOptions as $value => $label)
              @php
                $disabledPaymentOption = $value === 'expired' && !$paymentCanBeFailed;
              @endphp
              <option value="{{ $value }}" @selected($order->payment_status === $value) @disabled($disabledPaymentOption)>{{ $label }}</option>
            @endforeach
          </select>

          <button type="submit"
                  @disabled($paymentLocked)
                  class="w-full rounded-xl bg-purple-600 px-4 py-3 font-semibold text-white hover:bg-purple-500 disabled:cursor-not-allowed disabled:bg-slate-300">
            Simpan Status Pembayaran
          </button>
        </form>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Update Status Pengiriman</h2>

        <form method="POST" action="{{ route('admin.orders.update', $order) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
          @csrf
          @method('PATCH')
          <input type="hidden" name="action_type" value="shipping">

          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">Status Pesanan</label>
            <select name="status"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
              @foreach($orderStatusOptions as $option)
                @php
                  $disabledStatusOption = in_array($option, $orderRequiresPaidStatuses, true) && $order->payment_status !== 'paid';
                  $disabledStatusOption = $disabledStatusOption || ($order->payment_status === 'paid' && in_array($option, ['Menunggu Pembayaran','Menunggu Verifikasi Pembayaran'], true));
                @endphp
                <option value="{{ $option }}" @selected(old('status', $order->status) === $option) @disabled($disabledStatusOption)>{{ $option }}</option>
              @endforeach
            </select>
            <p class="mt-2 text-xs leading-relaxed text-slate-500">
              Email customer hanya dikirim saat status menjadi Dikirim atau Dibatalkan. Status Diproses dan Selesai tidak mengirim email agar notifikasi tidak berlebihan.
            </p>
            @error('status')
              <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">Nomor Resi</label>
            <input type="text"
                   name="tracking_number"
                   value="{{ old('tracking_number', $order->tracking_number) }}"
                   placeholder="Contoh: JNE1234567890"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
            @error('tracking_number')
              <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan Pengiriman</label>
            <textarea name="tracking_notes"
                      rows="3"
                      placeholder="Contoh: Paket dikirim via JNE REG"
                      class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">{{ old('tracking_notes', $order->tracking_notes) }}</textarea>
            @error('tracking_notes')
              <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label class="mb-2 block text-sm font-semibold text-slate-700">Foto Resi / Bukti Pengiriman</label>
            <input type="file"
                   name="shipping_receipt"
                   accept=".jpg,.jpeg,.png,.webp,image/*"
                   class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-full file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-purple-700">
            <p class="mt-2 text-xs text-slate-500">Opsional. Upload foto resi agar pelanggan bisa melihat bukti pengiriman. Format JPG, PNG, WEBP. Maksimal 4MB.</p>
            @error('shipping_receipt')
              <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror

            @if($order->shipping_receipt_path)
              <div class="mt-4 rounded-xl border border-slate-200 bg-white p-3">
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-sm">
                  <span class="font-semibold text-slate-900">Foto resi saat ini</span>
                  <a href="{{ route('admin.orders.shipping-receipt', $order) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-purple-700 hover:text-purple-500">Lihat penuh</a>
                </div>
                <a href="{{ route('admin.orders.shipping-receipt', $order) }}" target="_blank" rel="noopener" class="block">
                  <img src="{{ route('admin.orders.shipping-receipt', $order) }}"
                       alt="Foto resi pengiriman"
                       class="max-h-64 w-full rounded-xl border border-slate-100 bg-white object-contain"
                       loading="lazy">
                </a>
                <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                  <input type="checkbox" name="remove_shipping_receipt" value="1" class="rounded border-slate-300 text-purple-600 focus:ring-purple-200">
                  Hapus foto resi saat menyimpan
                </label>
                @if($order->shipping_receipt_uploaded_at)
                  <div class="mt-2 text-xs text-slate-500">Diupload: {{ $order->shipping_receipt_uploaded_at->translatedFormat('j F Y, H:i') }}</div>
                @endif
              </div>
            @endif
          </div>

          <button type="submit"
                  class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-800">
            Simpan Status Order & Resi
          </button>
        </form>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-lg font-bold">Info Pengiriman</h2>

          @if($order->tracking_number)
            <button type="button"
                    data-copy-text="{{ $order->tracking_number }}"
                    class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
              Salin Resi
            </button>
          @endif
        </div>

        <div class="mt-4 space-y-2 text-sm text-slate-600">
          <div><span class="font-semibold text-slate-900">Kurir:</span> {{ $order->shipping_courier_name ?: strtoupper($order->shipping_courier ?: '-') }}</div>
          <div><span class="font-semibold text-slate-900">Layanan:</span> {{ $order->shipping_service_name ?: '-' }}</div>
          <div><span class="font-semibold text-slate-900">Estimasi:</span> {{ $order->shipping_etd ?: '-' }}</div>
          <div><span class="font-semibold text-slate-900">Berat:</span> {{ number_format((int) $order->shipping_weight) }} gram</div>
          <div><span class="font-semibold text-slate-900">Kode Pos Asal:</span> {{ $order->shipping_origin_postal_code ?: '-' }}</div>
          <div><span class="font-semibold text-slate-900">Kode Pos Tujuan:</span> {{ $order->shipping_destination_postal_code ?: '-' }}</div>
          <div><span class="font-semibold text-slate-900">Nomor Resi:</span> {{ $order->tracking_number ?: '-' }}</div>
          @if($order->tracking_notes)
            <div><span class="font-semibold text-slate-900">Catatan Pengiriman:</span> {{ $order->tracking_notes }}</div>
          @endif
          <div><span class="font-semibold text-slate-900">Update Resi:</span> {{ optional($order->tracking_updated_at)->translatedFormat('d F Y H:i') ?: '-' }}</div>
          <div>
            <span class="font-semibold text-slate-900">Foto Resi:</span>
            @if($order->shipping_receipt_path)
              <a href="{{ route('admin.orders.shipping-receipt', $order) }}" target="_blank" rel="noopener" class="font-semibold text-purple-700 hover:text-purple-500">Lihat foto resi</a>
            @else
              -
            @endif
          </div>
        </div>

        @if($order->shipping_receipt_path)
          <a href="{{ route('admin.orders.shipping-receipt', $order) }}" target="_blank" rel="noopener" class="mt-4 block">
            <img src="{{ route('admin.orders.shipping-receipt', $order) }}"
                 alt="Foto resi pengiriman"
                 class="max-h-72 w-full rounded-xl border border-slate-200 bg-white object-contain"
                 loading="lazy">
          </a>
        @endif
      </div>
    </div>
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