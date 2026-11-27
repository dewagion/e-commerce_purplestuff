@extends('layouts.admin')

@php
  $heading = 'Pesanan & Pembayaran';
  $subheading = 'Kelola verifikasi pembayaran dan status pengiriman.';
@endphp

@section('content')
  <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari nomor order / nama / email"
           class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">

    <select name="payment_status"
            class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
      <option value="">Semua pembayaran</option>
      <option value="pending" @selected($paymentStatus === 'pending')>Pending</option>
      <option value="waiting_verification" @selected($paymentStatus === 'waiting_verification')>Menunggu Verifikasi</option>
      <option value="paid" @selected($paymentStatus === 'paid')>Paid</option>
      <option value="expired" @selected($paymentStatus === 'expired')>Pembayaran Gagal</option>
    </select>

    <select name="status"
            class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
      <option value="">Semua status order</option>
      @foreach(['Menunggu Pembayaran','Menunggu Verifikasi Pembayaran','Diproses','Dikirim','Selesai','Dibatalkan'] as $option)
        <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
      @endforeach
    </select>

    <button type="submit"
            class="rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-800">
      Filter
    </button>
  </form>

  <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
          <tr>
            <th class="px-4 py-3">Order</th>
            <th class="px-4 py-3">Pelanggan</th>
            <th class="px-4 py-3">Pembayaran</th>
            <th class="px-4 py-3">Status Order</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Tanggal</th>
            <th class="px-4 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($orders as $order)
            <tr>
              <td class="px-4 py-4 font-semibold">{{ $order->order_number }}</td>
              <td class="px-4 py-4">
                <div>{{ $order->customer_name }}</div>
                <div class="text-xs text-slate-500">{{ $order->customer_email }}</div>
              </td>
              <td class="px-4 py-4">
                @php
                  $paymentLabel = match($order->payment_status) {
                    'paid' => 'Paid',
                    'waiting_verification' => 'Menunggu Verifikasi',
                    'expired' => 'Pembayaran Gagal',
                    default => 'Pending',
                  };
                @endphp
                {{ $paymentLabel }}
              </td>
              <td class="px-4 py-4">{{ $order->status }}</td>
              <td class="px-4 py-4">{{ \App\Support\Money::rupiah($order->grand_total) }}</td>
              <td class="px-4 py-4">{{ $order->created_at?->format('d M Y H:i') }}</td>
              <td class="px-4 py-4">
                <a href="{{ route('admin.orders.show', $order) }}"
                   class="rounded-lg bg-purple-600 px-3 py-2 text-white hover:bg-purple-500">
                  Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                Belum ada pesanan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">
    {{ $orders->links() }}
  </div>
@endsection