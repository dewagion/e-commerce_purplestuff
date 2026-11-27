@extends('layouts.app')

@section('content')
  <div class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-extrabold">Keranjang</h1>
      <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-500">
        ← Lanjut Belanja
      </a>
    </div>

    @if(!empty($ids))
  <div class="mt-3 text-sm text-slate-500">
    Menampilkan produk pilihan dari banner.
  </div>
@endif

    @if(session('success'))
      <div class="mt-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
      </div>
    @endif

    @if(empty($cart))
      <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-8 text-center text-slate-600 shadow-sm">
        Keranjang masih kosong.
      </div>
    @else
      {{-- Checkout Selected --}}
      <form id="cart-page-checkout-form" method="POST" action="{{ route('checkout.selected') }}">
        @csrf

        <div class="mt-6 flex items-center justify-between rounded-2xl border border-purple-100 bg-purple-50 px-4 py-3">
          <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-800">
            <input type="checkbox"
                   checked
                   data-cart-page-select-all
                   class="h-5 w-5 accent-purple-600"
                   onchange="window.toggleCartPageSelectAll && window.toggleCartPageSelectAll(this.checked)">
            Pilih semua produk
          </label>
          <span class="text-xs font-semibold text-purple-700" data-cart-page-selected-summary>{{ count($cart) }} item dipilih</span>
        </div>

        <div class="mt-4 grid gap-4">
          @foreach($cart as $key => $item)
            @php($itemStock = (int) ($item['available_stock'] ?? $item['stock'] ?? 0))
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex gap-4 items-center">
              {{-- pilih item untuk checkout --}}
              <input type="checkbox"
                     name="selected[]"
                     value="{{ $key }}"
                     checked
                     data-cart-page-selected-checkbox
                     data-cart-page-line-total="{{ (int) $item['price'] * (int) $item['qty'] }}"
                     class="h-5 w-5 accent-purple-600"
                     onchange="window.syncCartPageSelectAll && window.syncCartPageSelectAll()">

              <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-20 w-20 rounded-xl object-cover bg-gray-100">

              <div class="flex-1">
                <div class="font-semibold">{{ $item['name'] }}</div>

                @if(!empty($item['size']))
                  <div class="text-sm text-slate-500">Size: {{ $item['size'] }}</div>
                @endif

                <div class="text-sm text-slate-500">Qty: {{ $item['qty'] }}</div>
                <div class="text-purple-600 font-bold">${{ number_format($item['price'], 2) }}</div>

                {{-- info bawah seperti contoh --}}
                <div class="mt-3 text-xs text-slate-500 space-y-1">
                  <div class="flex justify-between max-w-sm">
                    <span>SKU:</span>
                    <span class="font-semibold text-slate-700">{{ $item['sku'] ?? '-' }}</span>
                  </div>

                  <div class="flex justify-between max-w-sm">
                    <span>Ketersediaan:</span>
                    <span class="font-semibold {{ ($item['in_stock'] ?? true) && $itemStock > 0 ? 'text-green-600' : 'text-red-600' }}">
                      {{ ($item['in_stock'] ?? true) && $itemStock > 0 ? 'Stok Tersedia (' . $itemStock . ' item)' : 'Habis' }}
                    </span>
                  </div>

                  <div class="flex justify-between max-w-sm">
                    <span>Pengiriman:</span>
                    <span class="font-semibold text-slate-700">{{ $item['shipping'] ?? '1-3 Hari Kerja' }}</span>
                  </div>
                </div>
              </div>

              <div class="text-right">
                <div class="text-sm text-slate-500">Subtotal</div>
                <div class="font-bold">${{ number_format($item['price'] * $item['qty'], 2) }}</div>

                {{-- Hapus item (pakai key, bukan id) --}}
                <form method="POST" action="{{ route('cart.remove') }}" class="mt-3">
                  @csrf
                  <input type="hidden" name="key" value="{{ $key }}">
                  <button class="text-sm font-semibold text-red-600 hover:text-red-500">Hapus</button>
                </form>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="text-sm text-slate-500">Total Produk Dipilih</div>
            <div class="text-2xl font-extrabold" data-cart-page-total>{{ \App\Support\Money::rupiah($total) }}</div>
            <div class="text-sm text-slate-500 mt-1" data-cart-page-helper>
              Checkout akan menghitung berdasarkan item yang dicentang.
            </div>
          </div>

          <div class="flex flex-wrap gap-3">
            {{-- kosongkan --}}
            <form method="POST" action="{{ route('cart.clear') }}">
              @csrf
              <button type="submit" class="rounded-xl border border-gray-200 px-4 py-2 font-semibold hover:bg-gray-50">
                Kosongkan
              </button>
            </form>

            {{-- checkout selected --}}
            <button type="submit"
                    data-cart-page-checkout-button
                    class="rounded-xl bg-purple-600 px-4 py-2 font-semibold text-white hover:bg-purple-500 disabled:cursor-not-allowed disabled:bg-slate-300">
              Checkout Selected
            </button>
          </div>
        </div>
      </form>
    @endif
  </div>
@endsection

@push('scripts')
  <script>
    window.formatCartRupiah = window.formatCartRupiah || function (value) {
      const number = Math.max(0, Number(value) || 0);
      return 'Rp. ' + new Intl.NumberFormat('id-ID').format(number);
    };

    window.recalculateCartPageTotal = function () {
      const checkboxes = Array.from(document.querySelectorAll('[data-cart-page-selected-checkbox]'));
      const checkedBoxes = checkboxes.filter(function (checkbox) {
        return checkbox.checked;
      });

      const selectedTotal = checkedBoxes.reduce(function (sum, checkbox) {
        return sum + (Number(checkbox.dataset.cartPageLineTotal) || 0);
      }, 0);

      const totalEl = document.querySelector('[data-cart-page-total]');
      const summaryEl = document.querySelector('[data-cart-page-selected-summary]');
      const helperEl = document.querySelector('[data-cart-page-helper]');
      const checkoutButton = document.querySelector('[data-cart-page-checkout-button]');

      if (totalEl) totalEl.textContent = window.formatCartRupiah(selectedTotal);
      if (summaryEl) summaryEl.textContent = checkedBoxes.length + ' item dipilih';
      if (helperEl) helperEl.textContent = checkedBoxes.length > 0
        ? 'Checkout akan menghitung berdasarkan item yang dicentang.'
        : 'Pilih minimal satu produk untuk checkout.';
      if (checkoutButton) checkoutButton.disabled = checkboxes.length === 0 || checkedBoxes.length === 0;
    };

    window.toggleCartPageSelectAll = function (checked) {
      document.querySelectorAll('[data-cart-page-selected-checkbox]').forEach(function (checkbox) {
        checkbox.checked = checked;
      });
      window.syncCartPageSelectAll && window.syncCartPageSelectAll();
    };

    window.syncCartPageSelectAll = function () {
      const checkboxes = Array.from(document.querySelectorAll('[data-cart-page-selected-checkbox]'));
      const selectAll = document.querySelector('[data-cart-page-select-all]');

      const checkedCount = checkboxes.filter(function (checkbox) {
        return checkbox.checked;
      }).length;

      if (selectAll) {
        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
      }

      window.recalculateCartPageTotal && window.recalculateCartPageTotal();
    };

    document.addEventListener('DOMContentLoaded', function () {
      window.syncCartPageSelectAll && window.syncCartPageSelectAll();

      const cartPageCheckoutForm = document.getElementById('cart-page-checkout-form');
      if (cartPageCheckoutForm) {
        cartPageCheckoutForm.addEventListener('submit', function (event) {
          const hasSelectedItem = Array.from(document.querySelectorAll('[data-cart-page-selected-checkbox]')).some(function (checkbox) {
            return checkbox.checked;
          });

          if (!hasSelectedItem) {
            event.preventDefault();
            alert('Pilih minimal satu produk untuk checkout.');
          }
        });
      }
    });
  </script>
@endpush

