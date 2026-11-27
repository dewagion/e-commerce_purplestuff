@extends('layouts.admin')

@php
  $heading = 'Dashboard Admin';
  $subheading = 'Ringkasan penjualan, pesanan, dan performa toko.';
@endphp

@section('content')
  <div class="space-y-6">
    {{-- Statistik Utama --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Total Produk</div>
        <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalProducts }}</div>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Total Pelanggan</div>
        <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalCustomers }}</div>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Total Pesanan</div>
        <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalOrders }}</div>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Omzet Total</div>
        <div class="mt-2 text-3xl font-extrabold text-purple-700">
          Rp. {{ number_format($totalRevenue, 0, ',', '.') }}
        </div>
      </div>
    </div>

    {{-- Statistik Tambahan --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Omzet Dibayar</div>
        <div class="mt-2 text-2xl font-extrabold text-slate-900">
          Rp. {{ number_format($paidRevenue, 0, ',', '.') }}
        </div>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Omzet Bulan Ini</div>
        <div class="mt-2 text-2xl font-extrabold text-slate-900">
          Rp. {{ number_format($monthRevenue, 0, ',', '.') }}
        </div>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Pending Bayar</div>
        <div class="mt-2 text-2xl font-extrabold text-amber-500">
          {{ $pendingPaymentCount }}
        </div>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">Verifikasi / Dikirim</div>
        <div class="mt-2 text-2xl font-extrabold text-slate-900">
          {{ $waitingVerificationCount }} / {{ $shippingCount }}
        </div>
      </div>
    </div>

    {{-- Rekap Penjualan --}}
    <div class="overflow-hidden rounded-[1.6rem] bg-white shadow-sm ring-1 ring-slate-100">
      <div class="border-b border-slate-100 bg-gradient-to-r from-purple-50 via-white to-sky-50 p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
          <div>
            <div class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-purple-700">
              Rekap {{ $salesRecap['label'] }}
            </div>
            <h2 class="mt-3 text-xl font-extrabold text-slate-950">Rekap Penjualan</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">
              Pilih periode cepat atau tentukan rentang tanggal sendiri. Data yang tampil di dashboard akan sama dengan data yang dicetak pada PDF.
            </p>
          </div>

          <a href="{{ route('admin.recap.pdf', $salesRecap['pdf_query'] ?? ['range' => $salesRecap['range']]) }}"
             class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-800">
            Download PDF
          </a>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
          <a href="{{ route('admin.dashboard', ['range' => 'weekly']) }}"
             class="rounded-2xl px-4 py-2 text-sm font-bold transition {{ $salesRecap['range'] === 'weekly' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white/80 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">
            Mingguan
          </a>

          <a href="{{ route('admin.dashboard', ['range' => 'monthly']) }}"
             class="rounded-2xl px-4 py-2 text-sm font-bold transition {{ $salesRecap['range'] === 'monthly' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white/80 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">
            Bulanan
          </a>

          <a href="{{ route('admin.dashboard', ['range' => 'yearly']) }}"
             class="rounded-2xl px-4 py-2 text-sm font-bold transition {{ $salesRecap['range'] === 'yearly' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white/80 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">
            Tahunan
          </a>

          <span class="rounded-2xl px-4 py-2 text-sm font-bold {{ $salesRecap['range'] === 'custom' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white/60 text-slate-500 ring-1 ring-slate-200' }}">
            Rentang Bebas
          </span>
        </div>

        <form action="{{ route('admin.dashboard') }}" method="GET" class="mt-5 rounded-[1.35rem] bg-white/80 p-4 shadow-sm ring-1 ring-slate-200">
          <input type="hidden" name="range" value="custom">

          <div class="grid gap-3 lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end">
            <label class="block">
              <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal Mulai</span>
              <input type="date" name="start_date" value="{{ $salesRecap['start_input'] }}"
                     class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
            </label>

            <label class="block">
              <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal Akhir</span>
              <input type="date" name="end_date" value="{{ $salesRecap['end_input'] }}"
                     class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
            </label>

            <button type="submit"
                    class="rounded-2xl bg-purple-600 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-purple-700">
              Terapkan Kalender
            </button>

            <a href="{{ route('admin.dashboard', ['range' => 'monthly']) }}"
               class="rounded-2xl bg-slate-100 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-200">
              Reset
            </a>
          </div>

          <div class="mt-3 flex flex-col gap-2 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <span class="font-bold text-slate-900">Periode aktif:</span>
              {{ $salesRecap['start']->translatedFormat('d M Y') }} - {{ $salesRecap['end']->translatedFormat('d M Y') }}
            </div>
            <div class="text-xs text-slate-500">
              {{ $salesRecap['range_note'] ?? 'Gunakan kalender untuk rekap sesuai tanggal yang dibutuhkan.' }}
            </div>
          </div>
        </form>
      </div>

      <div class="p-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="text-sm text-slate-500">Periode</div>
            <div class="mt-2 text-lg font-bold text-slate-900">
              {{ $salesRecap['start']->translatedFormat('d M Y') }} - {{ $salesRecap['end']->translatedFormat('d M Y') }}
            </div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="text-sm text-slate-500">Total Pesanan</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900">
              {{ $salesRecap['order_count'] }}
            </div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="text-sm text-slate-500">Item Terjual</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900">
              {{ $salesRecap['items_sold'] }}
            </div>
          </div>

          <div class="rounded-2xl border border-purple-100 bg-purple-50 p-4">
            <div class="text-sm text-purple-700">Omzet Rekap</div>
            <div class="mt-2 text-2xl font-extrabold text-purple-700">
              Rp. {{ number_format($salesRecap['revenue_total'], 0, ',', '.') }}
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <div class="text-sm text-slate-500">Pesanan Dibayar</div>
            <div class="mt-2 text-xl font-bold text-green-600">{{ $salesRecap['paid_count'] }}</div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <div class="text-sm text-slate-500">Pending Pembayaran</div>
            <div class="mt-2 text-xl font-bold text-amber-500">{{ $salesRecap['pending_count'] }}</div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <div class="text-sm text-slate-500">Menunggu Verifikasi</div>
            <div class="mt-2 text-xl font-bold text-orange-500">{{ $salesRecap['verification_count'] }}</div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <div class="text-sm text-slate-500">Diproses</div>
            <div class="mt-2 text-xl font-bold text-sky-600">{{ $salesRecap['processed_count'] }}</div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <div class="text-sm text-slate-500">Dikirim / Selesai</div>
            <div class="mt-2 text-xl font-bold text-indigo-600">{{ $salesRecap['shipped_count'] }}</div>
          </div>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-[.7fr_1.3fr]">
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="text-sm text-slate-500">Rata-rata Nilai Order</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900">
              Rp. {{ number_format($salesRecap['average_order'], 0, ',', '.') }}
            </div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="text-sm text-slate-500">Komposisi Omzet</div>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
              <div>
                <div class="text-xs text-slate-500">Subtotal Produk</div>
                <div class="font-extrabold text-slate-900">Rp. {{ number_format($salesRecap['subtotal_total'], 0, ',', '.') }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-500">Pengiriman</div>
                <div class="font-extrabold text-slate-900">Rp. {{ number_format($salesRecap['shipping_total'], 0, ',', '.') }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-500">Omzet Dibayar</div>
                <div class="font-extrabold text-green-600">Rp. {{ number_format($salesRecap['paid_revenue'], 0, ',', '.') }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
          <div class="rounded-2xl border border-slate-100 bg-white">
            <div class="border-b border-slate-100 px-4 py-3">
              <h3 class="font-bold text-slate-900">Produk Terlaris pada Periode Ini</h3>
              <p class="mt-1 text-xs text-slate-500">Produk dengan jumlah terjual paling tinggi pada periode aktif.</p>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Terjual</th>
                    <th class="px-4 py-3">Omzet</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  @forelse($salesRecap['top_products'] as $item)
                    <tr>
                      <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->product_name }}</td>
                      <td class="px-4 py-3">{{ $item->sold_qty }}</td>
                      <td class="px-4 py-3">Rp. {{ number_format((int) $item->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="px-4 py-6 text-center text-slate-500">
                        Belum ada data penjualan pada periode ini.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-white">
            <div class="border-b border-slate-100 px-4 py-3">
              <h3 class="font-bold text-slate-900">Rekap Harian Periode Ini</h3>
              <p class="mt-1 text-xs text-slate-500">Ringkasan per tanggal dari periode yang sedang dipilih.</p>
            </div>

            <div class="max-h-80 overflow-y-auto">
              <table class="min-w-full text-sm">
                <thead class="sticky top-0 bg-slate-50 text-left text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Pesanan</th>
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3">Omzet</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  @forelse($salesRecap['daily_recap'] as $day)
                    <tr>
                      <td class="px-4 py-3 font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($day->sale_date)->translatedFormat('d M Y') }}</td>
                      <td class="px-4 py-3">{{ (int) $day->total_orders }}</td>
                      <td class="px-4 py-3">{{ (int) $day->total_items }}</td>
                      <td class="px-4 py-3 font-bold text-purple-700">Rp. {{ number_format((int) $day->revenue_total, 0, ',', '.') }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                        Belum ada data penjualan harian pada periode ini.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Grafik Penjualan + Produk Terlaris --}}
    <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-900">Laporan Penjualan 7 Hari Terakhir</h2>
        </div>

        <div class="mt-6">
          <div class="flex h-72 items-end gap-3">
            @foreach($salesByDay as $row)
              @php
                $height = max(8, (int) round(($row['revenue'] / $maxRevenue) * 220));
              @endphp

              <div class="flex-1">
                <div class="flex h-60 items-end">
                  <div class="w-full rounded-t-2xl bg-purple-500" style="height: {{ $height }}px;"></div>
                </div>
                <div class="mt-2 text-center text-xs font-semibold text-slate-600">
                  {{ $row['date']->translatedFormat('d M') }}
                </div>
                <div class="mt-1 text-center text-[11px] text-slate-500">
                  {{ $row['orders'] }} order
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="mt-6 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-slate-500">
                <th class="py-3 pr-4">Tanggal</th>
                <th class="py-3 pr-4">Jumlah Pesanan</th>
                <th class="py-3">Omzet</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @foreach($salesByDay as $row)
                <tr>
                  <td class="py-3 pr-4 font-medium text-slate-900">{{ $row['date']->translatedFormat('d M Y') }}</td>
                  <td class="py-3 pr-4">{{ $row['orders'] }}</td>
                  <td class="py-3">Rp. {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Produk Terlaris</h2>

        <div class="mt-4 space-y-3">
          @forelse($topProducts as $item)
            <div class="rounded-xl border border-slate-100 p-4">
              <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
              <div class="mt-1 text-sm text-slate-500">Terjual: {{ $item->sold_qty }}</div>
              <div class="mt-1 text-sm font-semibold text-purple-700">
                Rp. {{ number_format((int) $item->total_revenue, 0, ',', '.') }}
              </div>
            </div>
          @empty
            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">
              Belum ada data penjualan.
            </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Pesanan Terbaru --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900">Pesanan Terbaru</h2>

      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="py-3 pr-4">Order</th>
              <th class="py-3 pr-4">Pelanggan</th>
              <th class="py-3 pr-4">Pembayaran</th>
              <th class="py-3 pr-4">Status</th>
              <th class="py-3 pr-4">Total</th>
              <th class="py-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($latestOrders as $order)
              <tr>
                <td class="py-3 pr-4 font-semibold text-slate-900">{{ $order->order_number }}</td>
                <td class="py-3 pr-4">{{ $order->customer_name }}</td>
                <td class="py-3 pr-4">{{ $order->payment_status }}</td>
                <td class="py-3 pr-4">{{ $order->status }}</td>
                <td class="py-3 pr-4">Rp. {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                <td class="py-3">
                  <a href="{{ route('admin.orders.show', $order) }}"
                     class="rounded-lg bg-slate-900 px-3 py-2 text-white hover:bg-slate-800">
                    Detail
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="py-6 text-center text-slate-500">
                  Belum ada pesanan terbaru.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection