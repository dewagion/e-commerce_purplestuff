@extends('layouts.app')

@section('content')
<div class="min-h-screen px-4 py-12 sm:px-6 lg:py-16">
  <div class="mx-auto max-w-5xl">
    <section class="overflow-hidden rounded-[2rem] bg-white/82 shadow-2xl shadow-slate-900/10 ring-1 ring-white/60 backdrop-blur-xl">
      <div class="bg-gradient-to-br from-purple-800 via-purple-700 to-sky-600 px-6 py-10 text-white md:px-10">
        <img src="{{ asset('images/logo.png') }}" alt="Purplestuff Merch" class="h-14 w-14 rounded-full object-cover shadow-lg ring-2 ring-white/30">
        <h1 class="mt-5 text-3xl font-extrabold tracking-tight md:text-4xl">Contact Us</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/82 md:text-base">
          Hubungi kami untuk bantuan pemesanan, produk, pembayaran, pengiriman, atau pertanyaan lain terkait Purple Stuff.
        </p>
      </div>

      <div class="grid gap-5 p-6 md:grid-cols-2 md:p-10">
        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <div class="text-xs font-bold uppercase tracking-[.18em] text-purple-600">Email</div>
          <div class="mt-3 text-lg font-extrabold text-slate-950">purplestuff002@gmail.com</div>
          <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan email untuk pertanyaan detail mengenai pesanan, bukti pembayaran, atau informasi produk.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <div class="text-xs font-bold uppercase tracking-[.18em] text-purple-600">WhatsApp</div>
          <div class="mt-3 text-lg font-extrabold text-slate-950">+6282340064510</div>
          <p class="mt-2 text-sm leading-6 text-slate-600">Jam layanan: Senin–Jumat, 09.00–17.00 WITA. Nomor dapat disesuaikan dengan kontak toko Anda.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80 md:col-span-2">
          <div class="text-xs font-bold uppercase tracking-[.18em] text-purple-600">Catatan Layanan</div>
          <p class="mt-3 text-sm leading-6 text-slate-600">
            Untuk mempercepat pengecekan, sertakan nomor pesanan ketika menghubungi admin. Nomor pesanan dapat dilihat pada halaman Pesanan Saya setelah checkout berhasil dibuat.
          </p>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 border-t border-slate-200/80 px-6 py-6 md:px-10">
        <a href="{{ route('pages.shipping') }}" class="ps-btn-primary ps-btn-sm">Shipping Info →</a>
        <a href="{{ route('dashboard') }}" class="ps-btn-light ps-btn-sm">← Back to Dashboard</a>
      </div>
    </section>
  </div>
</div>
@endsection
