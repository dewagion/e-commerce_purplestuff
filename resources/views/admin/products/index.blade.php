@extends('layouts.admin')

@php
  $heading = 'Kelola Produk';
  $subheading = 'Tambah, ubah, dan hapus produk tanpa mengganggu fitur toko.';
@endphp

@section('content')
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex gap-2">
      <input type="text"
             name="q"
             value="{{ $search }}"
             placeholder="Cari nama / SKU produk"
             class="w-80 rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
      <button type="submit"
              class="rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-800">
        Cari
      </button>
    </form>

    <a href="{{ route('admin.products.create') }}"
       class="rounded-xl bg-purple-600 px-4 py-3 font-semibold text-white hover:bg-purple-500">
      + Tambah Produk
    </a>
  </div>

  <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
          <tr>
            <th class="px-4 py-3">Produk</th>
            <th class="px-4 py-3">Kategori</th>
            <th class="px-4 py-3">Artist</th>
            <th class="px-4 py-3">Harga</th>
            <th class="px-4 py-3">Berat</th>
            <th class="px-4 py-3">Stok</th>
            <th class="px-4 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($products as $product)
            <tr>
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->primary_image }}"
                       alt="{{ $product->name }}"
                       class="h-14 w-14 rounded-xl object-cover bg-slate-100">
                  <div>
                    <div class="font-semibold text-slate-900">{{ $product->name }}</div>

                    @if($product->sku)
                      <div class="text-sm text-slate-500">SKU: {{ $product->sku }}</div>
                    @endif
                  </div>
                </div>
              </td>
              <td class="px-4 py-4">{{ $product->category?->name }}</td>
              <td class="px-4 py-4">{{ $product->artist?->name }}</td>
              <td class="px-4 py-4">
                @if(method_exists($product, 'hasActiveDiscount') && $product->hasActiveDiscount())
                  <div class="space-y-1">
                    <div class="text-sm font-bold text-purple-600">{{ \App\Support\Money::rupiah($product->sellingPrice()) }}</div>
                    <div class="text-xs text-slate-400 line-through">{{ \App\Support\Money::rupiah($product->price) }}</div>
                    <span class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700">Diskon</span>
                  </div>
                @else
                  <div class="font-semibold text-slate-800">{{ \App\Support\Money::rupiah($product->price) }}</div>
                @endif

                @if(method_exists($product, 'statusLabel') && $product->statusLabel())
                  <div class="mt-2">
                    <span class="inline-flex rounded-full bg-purple-50 px-2 py-1 text-[11px] font-bold text-purple-700">{{ $product->statusLabel() }}</span>
                  </div>
                @endif
              </td>
              <td class="px-4 py-4">{{ number_format((int) $product->weight) }} gr</td>
              <td class="px-4 py-4">
                @php($availableStock = method_exists($product, 'availableStock') ? $product->availableStock() : ((bool) $product->in_stock ? (int) ($product->stock ?? 0) : 0))
                @if($availableStock > 0)
                  <div class="space-y-1">
                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Tersedia</span>
                    <div class="text-xs font-semibold text-slate-500">{{ number_format($availableStock) }} item</div>
                  </div>
                @else
                  <div class="space-y-1">
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">Habis</span>
                    <div class="text-xs font-semibold text-rose-500">0 item</div>
                  </div>
                @endif
              </td>
              <td class="px-4 py-4">
                <div class="flex gap-2">
                  <a href="{{ route('admin.products.edit', $product) }}"
                     class="rounded-lg bg-amber-500 px-3 py-2 text-white hover:bg-amber-400">
                    Edit
                  </a>

                  <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                        onsubmit="return confirm('Hapus produk ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-lg bg-rose-600 px-3 py-2 text-white hover:bg-rose-500">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                Belum ada produk.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">
    {{ $products->links() }}
  </div>
@endsection