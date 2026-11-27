@extends('layouts.app')

@section('content')
<div class="min-h-screen px-4 py-12 sm:px-6 lg:py-16">
  <div class="mx-auto max-w-5xl">
    <section class="overflow-hidden rounded-[2rem] bg-white/82 shadow-2xl shadow-slate-900/10 ring-1 ring-white/60 backdrop-blur-xl">
      <div class="bg-gradient-to-br from-slate-950 via-purple-900 to-sky-700 px-6 py-10 text-white md:px-10">
        <img src="{{ asset('images/logo.png') }}" alt="Purplestuff Merch" class="h-14 w-14 rounded-full object-cover shadow-lg ring-2 ring-white/30">
        <h1 class="mt-5 text-3xl font-extrabold tracking-tight md:text-4xl">FAQ</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/82 md:text-base">
          Pertanyaan umum untuk membantu pelanggan memahami cara belanja di Purple Stuff.
        </p>
      </div>

      <div class="space-y-4 p-6 md:p-10">
        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <h2 class="text-lg font-extrabold text-slate-950">Bagaimana cara checkout?</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Pilih produk, masukkan ke keranjang, pilih item yang ingin dibeli, lalu lanjutkan checkout dan lengkapi data penerima.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <h2 class="text-lg font-extrabold text-slate-950">Apa arti Ready Stock, Pre-Order, dan Upcoming?</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Ready Stock berarti tersedia, Pre-Order berarti pembelian dapat dilakukan dengan proses tunggu, sedangkan Upcoming merupakan label produk yang belum bisa dibeli.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <h2 class="text-lg font-extrabold text-slate-950">Kapan pembayaran diverifikasi?</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Setelah pelanggan upload bukti transfer, admin akan memeriksa bukti pembayaran dan mengubah status menjadi dibayar jika valid.</p>
        </div>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
          <h2 class="text-lg font-extrabold text-slate-950">Di mana melihat status pesanan?</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Status pesanan dapat dilihat melalui menu Pesanan Saya setelah pelanggan login.</p>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 border-t border-slate-200/80 px-6 py-6 md:px-10">
        <a href="{{ route('pages.contact') }}" class="ps-btn-primary ps-btn-sm">Contact Us →</a>
        <a href="{{ route('dashboard') }}" class="ps-btn-light ps-btn-sm">← Back to Dashboard</a>
      </div>
    </section>
  </div>
</div>
@endsection
