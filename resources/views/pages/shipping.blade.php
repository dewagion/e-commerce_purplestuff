@extends('layouts.app')

@section('content')
<div class="min-h-screen px-4 py-12 sm:px-6 lg:py-16">
  <div class="mx-auto max-w-5xl">
    <section class="overflow-hidden rounded-[2rem] bg-white/82 shadow-2xl shadow-slate-900/10 ring-1 ring-white/60 backdrop-blur-xl">
      <div class="bg-gradient-to-br from-sky-700 via-purple-700 to-purple-900 px-6 py-10 text-white md:px-10">
        <img src="{{ asset('images/logo.png') }}" alt="Purplestuff Merch" class="h-14 w-14 rounded-full object-cover shadow-lg ring-2 ring-white/30">
        <h1 class="mt-5 text-3xl font-extrabold tracking-tight md:text-4xl">Shipping Info</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/82 md:text-base">
          Informasi pengiriman membantu pelanggan memahami estimasi proses order hingga barang dikirim.
        </p>
      </div>

      <div class="grid gap-5 p-6 md:grid-cols-2 md:p-10">
        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <div class="text-xs font-bold uppercase tracking-[.18em] text-purple-600">Estimasi</div>
          <h2 class="mt-3 text-xl font-extrabold text-slate-950">1–7 hari pengiriman</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Estimasi dapat berbeda sesuai alamat pelanggan, kurir, dan ketersediaan produk.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <div class="text-xs font-bold uppercase tracking-[.18em] text-purple-600">Ongkir</div>
          <h2 class="mt-3 text-xl font-extrabold text-slate-950">Dihitung saat checkout</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Sistem dapat menghitung ongkir berdasarkan data checkout dan layanan pengiriman yang tersedia.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <div class="text-xs font-bold uppercase tracking-[.18em] text-purple-600">Resi</div>
          <h2 class="mt-3 text-xl font-extrabold text-slate-950">Diberikan oleh admin</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Nomor resi akan tampil pada detail pesanan setelah admin memproses dan menginput pengiriman.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <div class="text-xs font-bold uppercase tracking-[.18em] text-purple-600">Alamat</div>
          <h2 class="mt-3 text-xl font-extrabold text-slate-950">Pastikan data benar</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Nama penerima, nomor HP, alamat, dan kode pos perlu diisi dengan benar saat checkout.</p>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 border-t border-slate-200/80 px-6 py-6 md:px-10">
        <a href="{{ route('pages.returns') }}" class="ps-btn-primary ps-btn-sm">Returns →</a>
        <a href="{{ route('pages.contact') }}" class="ps-btn-light ps-btn-sm">← Contact Us</a>
      </div>
    </section>
  </div>
</div>
@endsection
