@extends('layouts.app')

@section('content')
  @php
    $isPending = $order->payment_status === 'pending';
    $isExpired = $order->payment_status === 'expired';
    $isWaiting = $order->payment_status === 'waiting_verification';
    $isPaid = $order->payment_status === 'paid';

    $paymentText = match($order->payment_status) {
      'paid' => 'Pembayaran Diterima',
      'waiting_verification' => 'Menunggu Verifikasi Admin',
      'expired' => 'Pembayaran Gagal',
      default => 'Menunggu Transfer',
    };

    $paymentBadge = match($order->payment_status) {
      'paid' => 'bg-green-50 text-green-700 border-green-200',
      'waiting_verification' => 'bg-blue-50 text-blue-700 border-blue-200',
      'expired' => 'bg-rose-50 text-rose-700 border-rose-200',
      default => 'bg-amber-50 text-amber-700 border-amber-200',
    };

    $paymentTimeoutMinutes = ($order->created_at && $order->payment_deadline_at)
      ? max(1, (int) ceil($order->created_at->diffInSeconds($order->payment_deadline_at) / 60))
      : 10;

    $displayTimezone = config('app.timezone', 'Asia/Makassar');
    $displayTimezoneLabel = $displayTimezone === 'Asia/Makassar' ? 'WITA' : $displayTimezone;
    $deadlineMs = $order->payment_deadline_at ? $order->payment_deadline_at->getTimestamp() * 1000 : null;
    $serverNowMs = now()->getTimestamp() * 1000;
  @endphp

  <div class="mx-auto max-w-6xl px-4 py-10"
       x-data="paymentCountdown({
         statusUrl: '{{ route('orders.payment-status', $order) }}',
         initialSeconds: {{ (int) $remainingSeconds }},
         initialStatus: @js($order->payment_status),
         deadlineAt: @js(optional($order->payment_deadline_at)?->toIso8601String()),
         serverNow: @js(now()->toIso8601String()),
         deadlineMs: @js($deadlineMs),
         serverNowMs: @js($serverNowMs),
       })"
       x-init="init()">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="ps-flow-page-title"><h1 class="text-2xl font-extrabold text-slate-950">Pembayaran Pesanan</h1></div>
        <p class="text-sm text-slate-500">Selesaikan transfer dan upload bukti pembayaran sebelum waktu habis.</p>
      </div>

      <a href="{{ route('orders.index') }}"
         class="ps-btn-soft ps-btn-sm">
        ← Pesanan Saya
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

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_.85fr]">
      <div class="space-y-6">
        <div class="ps-flow-card p-6 sm:p-7">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <div class="text-sm text-slate-500">Nomor Pesanan</div>
              <div class="text-xl font-extrabold text-slate-900">{{ $order->order_number }}</div>
              <div class="mt-1 text-sm text-slate-500">Dibuat: {{ $order->created_at ? $order->created_at->copy()->timezone($displayTimezone)->translatedFormat('j F Y, H:i') . ' ' . $displayTimezoneLabel : '-' }}</div>
            </div>

            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $paymentBadge }}" x-text="statusLabel || @js($paymentText)">
              {{ $paymentText }}
            </span>
          </div>

          @if($isPending)
            <div class="mt-5 ps-flow-alert-amber p-4" x-show="!expiredNow">
              <div class="text-sm font-semibold text-amber-800">Batas waktu pembayaran</div>
              <div class="mt-2 text-4xl font-extrabold tracking-tight text-amber-700" x-text="timeText">{{ gmdate('i:s', max(0, (int) $remainingSeconds)) }}</div>
              <div class="mt-1 text-sm text-amber-700">
                Upload bukti transfer sebelum
                <span class="font-semibold">{{ $order->payment_deadline_at ? $order->payment_deadline_at->copy()->timezone($displayTimezone)->translatedFormat('j F Y, H:i') . ' ' . $displayTimezoneLabel : '-' }}</span>.
              </div>
              <div class="mt-3 text-xs leading-5 text-amber-700">
                Jika waktu habis dan bukti transfer belum diupload, pesanan otomatis gagal, stok dikembalikan, dan produk bisa dibeli user lain.
              </div>
            </div>
          @endif

          <div x-show="expiredNow" x-cloak class="mt-5 ps-flow-alert-danger p-4 text-sm">
            Waktu pembayaran sudah habis. Pesanan dibatalkan dan stok sudah dikembalikan.
          </div>

          @if($isExpired)
            <div class="mt-5 ps-flow-alert-danger p-4 text-sm">
              Pembayaran gagal karena bukti transfer tidak diupload dalam batas waktu {{ $paymentTimeoutMinutes }} menit. Stok sudah dikembalikan.
              @if($order->payment_expired_at)
                <div class="mt-1 text-xs">Expired pada: {{ $order->payment_expired_at->copy()->timezone($displayTimezone)->translatedFormat('j F Y, H:i') }} {{ $displayTimezoneLabel }}</div>
              @endif
            </div>
          @elseif($isWaiting)
            <div class="mt-5 ps-flow-alert-info p-4 text-sm">
              Bukti transfer sudah diterima dan sedang menunggu verifikasi admin.
            </div>
          @elseif($isPaid)
            <div class="mt-5 ps-flow-alert-success p-4 text-sm">
              Pembayaran sudah diverifikasi. Pesanan akan diproses admin.
            </div>
          @endif
        </div>

        <div class="ps-flow-card p-6 sm:p-7">
          <h2 class="text-lg font-bold text-slate-900">Produk yang Dipesan</h2>

          <div class="mt-4 space-y-4">
            @foreach($order->items as $item)
              <div class="ps-flow-product-row">
                <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" class="h-16 w-16 rounded-2xl bg-white object-cover p-1 shadow-sm ring-1 ring-purple-100">

                <div class="min-w-0 flex-1">
                  <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                  <div class="mt-1 space-y-1 text-sm text-slate-500">
                    @if($item->variant)<div>Pilihan: {{ $item->variant }}</div>@endif
                    @if($item->size)<div>Ukuran: {{ $item->size }}</div>@endif
                    @if($item->sku)<div>SKU: {{ $item->sku }}</div>@endif
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
      </div>

      <div class="space-y-6">
        <div class="ps-flow-card p-6 sm:p-7">
          <h2 class="text-lg font-bold text-slate-900">Instruksi Transfer</h2>
          <div class="mt-4 space-y-2 text-sm text-slate-700">
            <div class="flex justify-between gap-3"><span>Bank</span><span class="font-semibold text-slate-900">{{ config('services.biteship.bank_name') }}</span></div>
            <div class="flex justify-between gap-3"><span>No. Rekening</span><span class="font-semibold text-slate-900">{{ config('services.biteship.bank_account_number') }}</span></div>
            <div class="flex justify-between gap-3"><span>Atas Nama</span><span class="font-semibold text-slate-900">{{ config('services.biteship.bank_account_name') }}</span></div>
            <div class="flex justify-between gap-3"><span>Kode Pembayaran</span><span class="font-semibold text-purple-700">{{ $order->payment_code }}</span></div>
          </div>

          <div class="mt-5 ps-flow-subcard ps-flow-total-box p-4">
            <div class="text-sm text-slate-600">Total Transfer</div>
            <div class="text-3xl font-extrabold text-purple-700">{{ \App\Support\Money::rupiah($order->grand_total) }}</div>
            <div class="mt-1 text-xs text-slate-500">Transfer sesuai nominal ini agar admin mudah mencocokkan pembayaran.</div>
          </div>
        </div>

        <div class="ps-flow-card p-6 sm:p-7">
          <h2 class="text-lg font-bold text-slate-900">Upload Bukti Transfer</h2>

          @if($order->payment_proof_path)
            <div class="mt-4">
              <a href="{{ route('orders.payment-proof', $order) }}" target="_blank" rel="noopener" class="block">
                <img src="{{ route('orders.payment-proof', $order) }}"
                     alt="Bukti transfer"
                     class="max-h-[420px] w-full rounded-2xl bg-white object-contain p-2 shadow-sm ring-1 ring-purple-100"
                     loading="lazy">
              </a>
              <div class="mt-2 text-xs text-slate-500">Diupload: {{ $order->payment_proof_uploaded_at ? $order->payment_proof_uploaded_at->copy()->timezone($displayTimezone)->translatedFormat('j F Y, H:i') . ' ' . $displayTimezoneLabel : '-' }}</div>
            </div>
          @endif

          @if($isPending)
            <form method="POST" action="{{ route('orders.pay', $order) }}" enctype="multipart/form-data" class="mt-4 space-y-3" x-show="!expiredNow">
              @csrf
              <div>
                <label class="mb-2 block text-sm font-semibold text-slate-900">Pilih bukti transfer</label>
                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp,image/*" class="block ps-flow-input text-sm text-slate-700" required>
                <p class="mt-1 text-xs text-slate-500">Format: JPG, JPEG, PNG, WEBP. Maksimal 3MB.</p>
              </div>

              <button type="submit" class="ps-flow-primary-button">
                Upload Bukti Transfer
              </button>
            </form>

            <div x-show="expiredNow" x-cloak class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
              Form upload ditutup karena waktu pembayaran sudah habis.
            </div>
          @elseif($isExpired)
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
              Upload bukti transfer sudah ditutup karena pembayaran gagal.
            </div>
          @elseif($isWaiting)
            <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
              Bukti sudah diupload. Silakan tunggu verifikasi admin.
            </div>
          @elseif($isPaid)
            <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
              Pesanan ini sudah dibayar.
            </div>
          @endif
        </div>

        <div class="ps-flow-card p-6 sm:p-7">
          <h2 class="text-lg font-bold text-slate-900">Ringkasan</h2>
          <div class="mt-4 space-y-2 text-sm text-slate-600">
            <div class="flex items-center justify-between"><span>Subtotal</span><span class="font-semibold text-slate-900">{{ \App\Support\Money::rupiah($order->subtotal) }}</span></div>
            <div class="flex items-center justify-between"><span>Ongkir</span><span class="font-semibold text-slate-900">{{ \App\Support\Money::rupiah($order->shipping_cost) }}</span></div>
            <div class="border-t border-gray-200 pt-2 flex items-center justify-between"><span class="font-bold text-slate-900">Grand Total</span><span class="text-lg font-extrabold text-slate-900">{{ \App\Support\Money::rupiah($order->grand_total) }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
function paymentCountdown(config) {
  return {
    seconds: Number(config.initialSeconds || 0),
    status: config.initialStatus || 'pending',
    expiredNow: config.initialStatus === 'expired',
    statusLabel: '',
    timer: null,
    pollTimer: null,
    checking: false,
    deadlineMs: Number.isFinite(Number(config.deadlineMs)) && Number(config.deadlineMs) > 0
      ? Number(config.deadlineMs)
      : (config.deadlineAt ? Date.parse(config.deadlineAt) : null),
    serverOffsetMs: Number.isFinite(Number(config.serverNowMs)) && Number(config.serverNowMs) > 0
      ? (Number(config.serverNowMs) - Date.now())
      : (config.serverNow ? (Date.parse(config.serverNow) - Date.now()) : 0),

    init() {
      this.statusLabel = this.labelFor(this.status);
      this.recalculateSeconds();

      if (this.status !== 'pending') {
        return;
      }

      if (this.seconds <= 0) {
        this.expireNow();
        return;
      }

      // Timer tidak lagi mengurangi angka secara manual.
      // Setiap detik dihitung ulang dari waktu deadline asli,
      // sehingga detik lebih normal walaupun browser sempat lag.
      this.timer = setInterval(() => {
        this.recalculateSeconds();

        if (this.seconds <= 0) {
          this.expireNow();
        }
      }, 1000);

      // Polling dibuat ringan dan tidak memengaruhi detik timer.
      this.pollTimer = setInterval(() => this.checkStatus(false), 20000);
    },

    recalculateSeconds() {
      if (this.deadlineMs) {
        const adjustedNow = Date.now() + this.serverOffsetMs;
        this.seconds = Math.max(0, Math.ceil((this.deadlineMs - adjustedNow) / 1000));
      } else {
        this.seconds = Math.max(0, Number(this.seconds || 0));
      }
    },

    get timeText() {
      const minutes = Math.floor(this.seconds / 60).toString().padStart(2, '0');
      const seconds = Math.floor(this.seconds % 60).toString().padStart(2, '0');
      return `${minutes}:${seconds}`;
    },

    expireNow() {
      this.seconds = 0;

      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
      }

      this.checkStatus(true);
    },

    async checkStatus(forceExpire) {
      if (this.checking) return;
      this.checking = true;

      try {
        const res = await fetch(config.statusUrl, {
          headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
          return;
        }

        const json = await res.json();
        this.status = json.payment_status || this.status;
        this.statusLabel = this.labelFor(this.status);

        if (Number.isFinite(Number(json.server_now_ms)) && Number(json.server_now_ms) > 0) {
          this.serverOffsetMs = Number(json.server_now_ms) - Date.now();
        }

        if (Number.isFinite(Number(json.deadline_ms)) && Number(json.deadline_ms) > 0) {
          this.deadlineMs = Number(json.deadline_ms);
        } else if (json.deadline) {
          this.deadlineMs = Date.parse(json.deadline);
        }

        if (typeof json.remaining_seconds !== 'undefined') {
          this.seconds = Math.max(0, Number(json.remaining_seconds || 0));
        } else {
          this.recalculateSeconds();
        }

        if (json.expired || this.status === 'expired') {
          this.expiredNow = true;
          this.seconds = 0;

          if (this.timer) clearInterval(this.timer);
          if (this.pollTimer) clearInterval(this.pollTimer);

          this.timer = null;
          this.pollTimer = null;
          return;
        }

        if (this.status !== 'pending') {
          if (this.timer) clearInterval(this.timer);
          if (this.pollTimer) clearInterval(this.pollTimer);
          this.timer = null;
          this.pollTimer = null;
          return;
        }

        // Jika waktu lokal sudah habis tetapi server belum selesai memproses,
        // cek ulang setelah jeda pendek tanpa membuat refresh/error.
        if (forceExpire && this.seconds <= 0) {
          setTimeout(() => this.checkStatus(true), 1500);
        }
      } catch (error) {
        // Abaikan kegagalan polling agar halaman tetap bisa dipakai.
        if (forceExpire) {
          setTimeout(() => this.checkStatus(true), 3000);
        }
      } finally {
        this.checking = false;
      }
    },

    labelFor(status) {
      if (status === 'paid') return 'Pembayaran Diterima';
      if (status === 'waiting_verification') return 'Menunggu Verifikasi Admin';
      if (status === 'expired') return 'Pembayaran Gagal';
      return 'Menunggu Transfer';
    }
  }
}
</script>
@endpush
