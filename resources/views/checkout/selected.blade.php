@extends('layouts.app')

@section('content')
  <div class="mx-auto max-w-6xl px-4 py-10"
       x-data="checkoutShipping({
         optionsUrl: '{{ route('checkout.shipping-options') }}',
         csrf: '{{ csrf_token() }}',
         subtotal: {{ (int) $subtotal }},
         savedPostalCode: @js($savedPostalCode),
         savedShipping: @js($savedShipping),
       })"
       x-init="init()">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="ps-flow-page-title"><h1 class="text-2xl font-extrabold text-slate-950">Checkout</h1></div>
        <p class="text-sm text-slate-500">
          Lengkapi data penerima, masukkan kode pos tujuan, hitung ongkir, lalu selesaikan pesanan.
        </p>
      </div>

      <a href="{{ route('dashboard') }}"
        class="ps-btn-soft ps-btn-sm">
        ← Back to Dashboard
      </a>
    </div>

    @if(session('error'))
      <div class="mt-6 ps-flow-alert-danger px-4 py-3 text-sm">
        {{ session('error') }}
      </div>
    @endif

    @if($errors->any())
      <div class="mt-6 ps-flow-alert-danger px-4 py-3 text-sm">
        {{ $errors->first() }}
      </div>
    @endif

    @if(count($missingWeightProducts) > 0)
      <div class="mt-6 ps-flow-alert-amber px-4 py-3 text-sm">
        Ada produk yang belum punya berat, jadi ongkir belum bisa dihitung:
        <span class="font-semibold">{{ collect($missingWeightProducts)->pluck('name')->implode(', ') }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('checkout.confirm') }}" class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
      @csrf

      <input type="hidden" name="payment_method" value="bank_transfer">
      <input type="hidden" name="destination_postal_code" :value="destinationPostalCode">
      <input type="hidden" name="shipping_courier" :value="selectedShipping.courier_code">
      <input type="hidden" name="shipping_courier_name" :value="selectedShipping.courier_name">
      <input type="hidden" name="shipping_service_code" :value="selectedShipping.service_code">
      <input type="hidden" name="shipping_service_name" :value="selectedShipping.service">
      <input type="hidden" name="shipping_etd" :value="selectedShipping.etd">
      <input type="hidden" name="shipping_cost" :value="selectedShipping.cost || 0">

      <div class="space-y-6">
        <div class="ps-flow-card p-6 sm:p-7">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Informasi Penerima</h2>
              <p class="text-sm text-slate-500">
                Nama, nomor telepon, alamat detail, dan kode pos tujuan penerima.
              </p>
            </div>

            <div class="ps-flow-pill">
              Akun Terhubung
            </div>
          </div>

          <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Lengkap</label>
              <input type="text"
                     name="name"
                     value="{{ old('name', $user->name ?? '') }}"
                     class="ps-flow-input"
                     required>
            </div>

            <div>
              <label class="mb-2 block text-sm font-semibold text-slate-700">Nomor Telepon</label>
              <input type="text"
                     name="phone"
                     value="{{ old('phone', $user->phone ?? '') }}"
                     class="ps-flow-input"
                     required>
            </div>

            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Alamat Jalan / Detail Rumah</label>
              <textarea name="address"
                        rows="4"
                        class="ps-flow-input"
                        required>{{ old('address', $user->address ?? '') }}</textarea>
            </div>

            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Kode Pos Tujuan</label>
              <div class="flex gap-2">
                <input type="text"
                       x-model="destinationPostalCode"
                       inputmode="numeric"
                       placeholder="Contoh: 80221"
                       class="ps-flow-input">

                <button type="button"
                        x-on:click="loadShippingOptions()"
                        class="ps-flow-secondary-button px-5 py-3">
                  Hitung Ongkir
                </button>
              </div>

              <p class="mt-2 text-xs text-slate-500">
                Masukkan kode pos penerima untuk menghitung ongkir.
              </p>
            </div>

            <div class="sm:col-span-2" x-show="shippingError" x-cloak>
              <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
                   x-text="shippingError"></div>
            </div>

            <div class="sm:col-span-2" x-show="loadingShipping" x-cloak>
              <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                Menghitung ongkir...
              </div>
            </div>

            <div class="sm:col-span-2" x-show="destinationPostalCode" x-cloak>
              <div class="ps-flow-subcard ps-flow-total-box p-4">
                <div class="text-sm font-semibold text-slate-900">Kode Pos Tujuan</div>
                <div class="mt-1 text-sm text-slate-700" x-text="destinationPostalCode"></div>
              </div>
            </div>

            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan Pesanan</label>
              <textarea name="notes"
                        rows="3"
                        placeholder="Contoh: rumah pagar hitam, kirim siang hari"
                        class="ps-flow-input">{{ old('notes') }}</textarea>
            </div>
          </div>
        </div>

        <div class="ps-flow-card p-6 sm:p-7">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Metode Pembayaran</h2>
              <p class="text-sm text-slate-500">
                Untuk sementara pembayaran hanya melalui transfer bank.
              </p>
            </div>
          </div>

          <div class="mt-5 ps-flow-subcard ps-flow-total-box p-4 text-sm text-slate-700 space-y-1">
            <div class="font-semibold text-slate-900">Transfer Bank</div>
            <div>Bank: <span class="font-semibold">{{ config('services.biteship.bank_name') }}</span></div>
            <div>No. Rekening: <span class="font-semibold">{{ config('services.biteship.bank_account_number') }}</span></div>
            <div>Atas Nama: <span class="font-semibold">{{ config('services.biteship.bank_account_name') }}</span></div>
          </div>
        </div>

        <div class="ps-flow-card p-6 sm:p-7">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Item yang Dipilih</h2>
              <p class="text-sm text-slate-500">
                Berat total dipakai untuk menghitung ongkir.
              </p>
            </div>

            <div class="text-sm font-semibold text-slate-500">
              {{ count($items) }} produk · {{ number_format($totalWeight) }} gram
            </div>
          </div>

          <div class="mt-5 space-y-4">
            @foreach($items as $item)
              <div class="ps-flow-product-row">
                <img src="{{ $item['image'] }}"
                     class="h-20 w-20 rounded-2xl bg-white object-cover p-1 shadow-sm ring-1 ring-purple-100"
                     alt="{{ $item['name'] }}">

                <div class="min-w-0 flex-1">
                  <div class="font-semibold text-slate-900">{{ $item['name'] }}</div>

                  <div class="mt-1 space-y-1 text-sm text-slate-500">
                    @if(!empty($item['variant']))
                      <div>Pilihan: {{ $item['variant'] }}</div>
                    @endif

                    @if(!empty($item['size']))
                      <div>Ukuran: {{ $item['size'] }}</div>
                    @endif

                    @if(!empty($item['sku']))
                      <div>SKU: {{ $item['sku'] }}</div>
                    @endif

                    <div>Qty: {{ $item['qty'] }}</div>
                    <div>Berat/item: {{ number_format((int) ($item['weight'] ?? 0)) }} gram</div>
                  </div>
                </div>

                <div class="text-right">
                  <div class="text-sm text-slate-500">Subtotal</div>
                  <div class="font-bold text-purple-600">
                    {{ \App\Support\Money::rupiah($item['line_total']) }}
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <div>
        <div class="sticky top-24 ps-flow-card p-6 sm:p-7">
          <h2 class="text-lg font-bold text-slate-900">Ringkasan Pembayaran</h2>

          <div class="mt-5 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
              <span class="text-slate-500">Subtotal Produk</span>
              <span class="font-semibold text-slate-900">
                {{ \App\Support\Money::rupiah($subtotal) }}
              </span>
            </div>

            <div class="flex items-center justify-between gap-3">
              <span class="text-slate-500">Ongkir</span>
              <span class="font-semibold"
                    :class="selectedShipping.cost > 0 ? 'text-slate-900' : 'text-slate-400'"
                    x-text="shippingText"></span>
            </div>

            <div class="border-t pt-3">
              <div class="flex items-center justify-between gap-3">
                <span class="text-base font-bold text-slate-900">Total Transfer</span>
                <span class="text-2xl font-extrabold text-slate-900" x-text="grandTotalText"></span>
              </div>
            </div>
          </div>

          <div class="mt-6 space-y-3">
            <div x-show="shippingOptions.length > 0" x-cloak class="space-y-2">
              <template x-for="option in shippingOptions" :key="option.courier_code + '-' + option.service">
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-white/70 p-3 shadow-sm ring-1 ring-slate-200/50 transition hover:-translate-y-0.5 hover:bg-white">
                  <input type="radio"
                         name="shipping_option_picker"
                         class="mt-1"
                         :checked="selectedShipping.courier_code === option.courier_code && selectedShipping.service === option.service"
                         x-on:change="selectShipping(option)">

                  <div class="flex-1">
                    <div class="font-semibold text-slate-900" x-text="option.label"></div>
                    <div class="mt-1 text-xs text-slate-500" x-text="option.description || '-'"></div>
                    <div class="mt-1 text-xs text-slate-500">
                      Estimasi: <span x-text="option.etd || '-'"></span>
                    </div>
                  </div>

                  <div class="font-bold text-purple-600" x-text="rupiah(option.cost)"></div>
                </label>
              </template>
            </div>
          </div>

          <button type="submit"
                  class="mt-6 ps-flow-primary-button"
                  :disabled="!canSubmit">
            Buat Pesanan
          </button>

          <p class="mt-3 text-xs leading-5 text-slate-500">
            Total transfer di atas sudah termasuk harga produk dan ongkir yang dipilih.
          </p>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script>
function checkoutShipping(config) {
  return {
    destinationPostalCode: config.savedPostalCode || '',
    shippingOptions: [],
    loadingShipping: false,
    shippingError: '',
    selectedShipping: {
      courier_code: config.savedShipping?.courier || '',
      courier_name: config.savedShipping?.courier_name || '',
      service_code: config.savedShipping?.service_code || '',
      service: config.savedShipping?.service || '',
      etd: config.savedShipping?.etd || '',
      cost: Number(config.savedShipping?.cost || 0),
    },
    subtotal: Number(config.subtotal || 0),

    init() {
      if (this.destinationPostalCode && this.selectedShipping.cost > 0) {
        return;
      }
    },

    get canSubmit() {
      return this.destinationPostalCode.trim() !== '' && Number(this.selectedShipping.cost || 0) > 0;
    },

    get shippingText() {
      return Number(this.selectedShipping.cost || 0) > 0
        ? this.rupiah(this.selectedShipping.cost)
        : 'Belum dipilih';
    },

    get grandTotalText() {
      return this.rupiah(this.subtotal + Number(this.selectedShipping.cost || 0));
    },

    rupiah(number) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
      }).format(Number(number || 0));
    },

    resetShipping() {
      this.shippingOptions = [];
      this.selectedShipping = {
        courier_code: '',
        courier_name: '',
        service_code: '',
        service: '',
        etd: '',
        cost: 0
      };
    },

    async loadShippingOptions() {
      this.shippingError = '';
      this.shippingOptions = [];
      this.resetShipping();

      if (!this.destinationPostalCode || this.destinationPostalCode.trim().length < 5) {
        this.shippingError = 'Masukkan kode pos tujuan yang valid terlebih dahulu.';
        return;
      }

      this.loadingShipping = true;

      try {
        const res = await fetch(config.optionsUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': config.csrf
          },
          body: JSON.stringify({
            destination_postal_code: this.destinationPostalCode.trim()
          })
        });

        const json = await res.json();

        if (!res.ok) {
          throw new Error(json.message || 'Gagal menghitung ongkir.');
        }

        this.shippingOptions = Array.isArray(json.data) ? json.data : [];

        if (this.shippingOptions.length === 0) {
          this.shippingError = 'Tidak ada layanan ongkir yang tersedia untuk kode pos ini.';
        }
      } catch (error) {
        this.shippingError = error.message || 'Gagal menghitung ongkir.';
      } finally {
        this.loadingShipping = false;
      }
    },

    selectShipping(option) {
      this.selectedShipping = {
        courier_code: option.courier_code || '',
        courier_name: option.courier_name || '',
        service_code: option.service_code || '',
        service: option.service || '',
        etd: option.etd || '',
        cost: Number(option.cost || 0)
      };
    },
  }
}
</script>
@endpush