@php
  $cart = session('cart', []);
  $subtotal = 0;
  foreach ($cart as $it) { $subtotal += ((int)$it['price'] * (int)$it['qty']); }
  $total = $subtotal;
@endphp

<div x-show="cartOpen"
     x-cloak
     x-on:ui:close-all.window="cartOpen=false"
     class="fixed inset-0 z-50"
     aria-hidden="true">
  <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-[3px]" x-on:click="cartOpen=false"></div>

  <div class="absolute inset-y-3 left-3 right-3 overflow-hidden rounded-[2rem] bg-slate-100/90 shadow-2xl ring-1 ring-white/35 backdrop-blur-xl sm:left-auto sm:w-full sm:max-w-md"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="translate-x-full opacity-70"
       x-transition:enter-end="translate-x-0 opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="translate-x-0 opacity-100"
       x-transition:leave-end="translate-x-full opacity-70">

    {{-- Struktur tetap: header + list scroll + footer sticky bawah --}}
    <div class="h-full flex flex-col bg-gradient-to-b from-slate-100/95 via-slate-50/88 to-purple-50/60">

      {{-- Header --}}
      <div class="border-b border-white/55 bg-gradient-to-r from-white/85 via-slate-100/80 to-purple-50/70 px-5 py-5">
        <div class="flex items-center justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-purple-600 text-white shadow-lg shadow-purple-500/25">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 6h15l-1.5 9h-12z"></path>
                <path d="M6 6 5 3H2"></path>
                <circle cx="9" cy="20" r="1"></circle>
                <circle cx="18" cy="20" r="1"></circle>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="text-xl font-black tracking-tight text-slate-950">Keranjang Belanja</div>
              <div class="mt-0.5 text-xs font-semibold text-slate-500">{{ collect($cart)->sum('qty') }} item di keranjang</div>
            </div>
          </div>

          <button type="button"
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/70 text-slate-500 shadow-sm transition hover:bg-white hover:text-slate-900"
                  x-on:click="cartOpen=false"
                  aria-label="Tutup keranjang">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 6 6 18"></path><path d="m6 6 12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      {{-- Form checkout selected --}}
      <form id="drawer-checkout-form" method="POST" action="{{ route('checkout.selected') }}">
        @csrf
        <input type="hidden" name="keep_open" value="1">
      </form>

      {{-- List items --}}
      <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
        @if(empty($cart))
          <div class="rounded-[1.5rem] bg-white/78 p-7 text-center shadow-sm ring-1 ring-white/70">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-100 text-purple-700">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 6h15l-1.5 9h-12z"></path><path d="M6 6 5 3H2"></path><circle cx="9" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle>
              </svg>
            </div>
            <div class="mt-4 text-sm font-semibold text-slate-600">Keranjang masih kosong.</div>
            <div class="mt-4">
              <a href="{{ route('dashboard') }}"
                 class="inline-flex items-center justify-center rounded-full bg-purple-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-purple-500/20 transition hover:bg-purple-700"
                 x-on:click="cartOpen=false">
                Kembali belanja
              </a>
            </div>
          </div>
        @else
          <div class="flex items-center justify-between rounded-[1.35rem] bg-white/78 px-4 py-3 shadow-sm ring-1 ring-white/70">
            <label class="inline-flex cursor-pointer items-center gap-3 text-sm font-extrabold text-slate-800">
              <input type="checkbox"
                     checked
                     data-drawer-select-all
                     class="h-5 w-5 rounded border-slate-300 accent-purple-600"
                     onchange="window.toggleDrawerSelectAll && window.toggleDrawerSelectAll(this.checked)">
              Pilih semua produk
            </label>
            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-extrabold text-purple-700" data-drawer-selected-summary>{{ count($cart) }} item dipilih</span>
          </div>

          @foreach($cart as $key => $item)
            @php($itemStock = (int) ($item['available_stock'] ?? $item['stock'] ?? 0))
            <div class="rounded-[1.5rem] bg-white/82 p-4 shadow-sm ring-1 ring-white/75 transition hover:bg-white/92 hover:shadow-md">
              <div class="flex gap-3 items-start">

                {{-- Checkbox checkout --}}
                <input form="drawer-checkout-form"
                       type="checkbox"
                       name="selected[]"
                       value="{{ $key }}"
                       checked
                       data-drawer-selected-checkbox
                       data-drawer-line-total="{{ (int) $item['price'] * (int) $item['qty'] }}"
                       class="mt-2 h-5 w-5 rounded border-slate-300 accent-purple-600"
                       onchange="window.syncDrawerSelectAll && window.syncDrawerSelectAll()">

                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                     class="h-16 w-16 shrink-0 rounded-2xl bg-slate-100 object-cover ring-1 ring-slate-200/70">

                <div class="min-w-0 flex-1">
                  <div class="line-clamp-2 font-extrabold leading-tight text-slate-950">{{ $item['name'] }}</div>

                  <div class="mt-1 space-y-0.5 text-sm text-slate-500">
                    @if(!empty($item['variant']))
                      <div>Pilihan: <span class="font-semibold text-slate-700">{{ $item['variant'] }}</span></div>
                    @endif

                    @if(!empty($item['size']))
                      <div>Ukuran: <span class="font-semibold text-slate-700">{{ $item['size'] }}</span></div>
                    @endif
                  </div>

                  <div class="mt-2 font-black text-purple-600">{{ \App\Support\Money::rupiah((int)$item['price']) }}</div>
                  <div class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold {{ $itemStock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">
                    {{ $itemStock > 0 ? 'Stok tersedia: ' . $itemStock . ' item' : 'Stok Habis' }}
                  </div>

                  <div class="mt-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">

                      {{-- minus --}}
                      <form method="POST" action="{{ route('cart.qty') }}">
                        @csrf
                        <input type="hidden" name="keep_open" value="1">
                        <input type="hidden" name="key" value="{{ $key }}">
                        <input type="hidden" name="delta" value="-1">
                        <button class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-700 shadow-sm ring-1 ring-slate-200/70 transition hover:bg-white" aria-label="Kurangi jumlah"><span class="block -translate-y-[1px]">−</span></button>
                      </form>

                      <div class="w-8 text-center font-extrabold text-slate-950">{{ (int)$item['qty'] }}</div>

                      {{-- plus --}}
                      <form method="POST" action="{{ route('cart.qty') }}">
                        @csrf
                        <input type="hidden" name="keep_open" value="1">
                        <input type="hidden" name="key" value="{{ $key }}">
                        <input type="hidden" name="delta" value="1">
                        <button class="flex h-8 w-8 items-center justify-center rounded-xl shadow-sm ring-1 ring-slate-200/70 transition {{ (int)$item['qty'] >= $itemStock ? 'cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-slate-100 text-slate-700 hover:bg-white' }}" @disabled((int)$item['qty'] >= $itemStock) aria-label="Tambah jumlah"><span class="block -translate-y-[1px]">+</span></button>
                      </form>
                    </div>

                    {{-- remove --}}
                    <form method="POST" action="{{ route('cart.remove') }}">
                      @csrf
                      <input type="hidden" name="keep_open" value="1">
                      <input type="hidden" name="key" value="{{ $key }}">
                      <button class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 shadow-sm ring-1 ring-rose-100 transition hover:bg-rose-100" title="Hapus" aria-label="Hapus">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 translate-x-[0.5px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 16h10l1-16"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        @endif
      </div>

      {{-- Footer --}}
      <div class="border-t border-white/60 bg-white/80 px-5 py-4 shadow-[0_-16px_35px_rgba(15,23,42,0.08)] backdrop-blur-xl">
        <div class="space-y-3 rounded-[1.5rem] bg-white/72 p-4 shadow-sm ring-1 ring-white/75">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Subtotal</span>
            <span class="font-extrabold text-slate-950" data-drawer-subtotal>{{ \App\Support\Money::rupiah($subtotal) }}</span>
          </div>

          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Pengiriman</span>
            <span class="font-bold text-slate-500">Dihitung saat checkout</span>
          </div>

          <div class="flex items-center justify-between text-lg font-black">
            <span class="text-slate-950">Total Sementara</span>
            <span class="text-purple-600" data-drawer-total>{{ \App\Support\Money::rupiah($total) }}</span>
          </div>

          <p class="text-xs leading-relaxed text-slate-500">
            Ongkir akan dihitung setelah kamu memasukkan kode pos tujuan di halaman checkout.
          </p>

          <div class="flex gap-2 pt-1">
            <form method="POST" action="{{ route('cart.clear') }}" class="w-1/3">
              @csrf
              <input type="hidden" name="keep_open" value="1">
              <button class="w-full rounded-2xl bg-slate-100 py-3 font-extrabold text-slate-700 ring-1 ring-slate-200/80 transition hover:bg-white">
                Clear
              </button>
            </form>

            <button form="drawer-checkout-form"
                    type="submit"
                    class="w-2/3 rounded-2xl bg-gradient-to-r from-purple-600 to-violet-600 py-3 font-extrabold text-white shadow-lg shadow-purple-500/25 transition hover:from-purple-700 hover:to-violet-700 disabled:cursor-not-allowed disabled:from-slate-300 disabled:to-slate-300 disabled:shadow-none"
                    data-drawer-checkout-button
                    {{ empty($cart) ? 'disabled' : '' }}>
              Checkout
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@once
  @push('scripts')
    <script>
      window.formatCartRupiah = window.formatCartRupiah || function (value) {
        const number = Math.max(0, Number(value) || 0);
        return 'Rp. ' + new Intl.NumberFormat('id-ID').format(number);
      };

      window.recalculateDrawerCartTotal = function () {
        const checkboxes = Array.from(document.querySelectorAll('[data-drawer-selected-checkbox]'));
        const checkedBoxes = checkboxes.filter(function (checkbox) {
          return checkbox.checked;
        });

        const selectedTotal = checkedBoxes.reduce(function (sum, checkbox) {
          return sum + (Number(checkbox.dataset.drawerLineTotal) || 0);
        }, 0);

        const subtotalEl = document.querySelector('[data-drawer-subtotal]');
        const totalEl = document.querySelector('[data-drawer-total]');
        const summaryEl = document.querySelector('[data-drawer-selected-summary]');
        const checkoutButton = document.querySelector('[data-drawer-checkout-button]');

        if (subtotalEl) subtotalEl.textContent = window.formatCartRupiah(selectedTotal);
        if (totalEl) totalEl.textContent = window.formatCartRupiah(selectedTotal);
        if (summaryEl) summaryEl.textContent = checkedBoxes.length + ' item dipilih';
        if (checkoutButton) checkoutButton.disabled = checkboxes.length === 0 || checkedBoxes.length === 0;
      };

      window.toggleDrawerSelectAll = function (checked) {
        document.querySelectorAll('[data-drawer-selected-checkbox]').forEach(function (checkbox) {
          checkbox.checked = checked;
        });
        window.syncDrawerSelectAll && window.syncDrawerSelectAll();
      };

      window.syncDrawerSelectAll = function () {
        const checkboxes = Array.from(document.querySelectorAll('[data-drawer-selected-checkbox]'));
        const selectAll = document.querySelector('[data-drawer-select-all]');

        const checkedCount = checkboxes.filter(function (checkbox) {
          return checkbox.checked;
        }).length;

        if (selectAll) {
          selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
          selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        window.recalculateDrawerCartTotal && window.recalculateDrawerCartTotal();
      };

      document.addEventListener('DOMContentLoaded', function () {
        window.syncDrawerSelectAll && window.syncDrawerSelectAll();

        const drawerCheckoutForm = document.getElementById('drawer-checkout-form');
        if (drawerCheckoutForm) {
          drawerCheckoutForm.addEventListener('submit', function (event) {
            const hasSelectedItem = Array.from(document.querySelectorAll('[data-drawer-selected-checkbox]')).some(function (checkbox) {
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
@endonce
