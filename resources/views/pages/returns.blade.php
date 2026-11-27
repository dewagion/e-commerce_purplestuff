@extends('layouts.app')

@section('content')
<div class="min-h-screen px-4 py-12 sm:px-6 lg:py-16">
  <div class="mx-auto max-w-5xl">
    <section class="overflow-hidden rounded-[2rem] bg-white/82 shadow-2xl shadow-slate-900/10 ring-1 ring-white/60 backdrop-blur-xl">
      <div class="bg-gradient-to-br from-purple-800 via-fuchsia-700 to-sky-600 px-6 py-10 text-white md:px-10">
        <img src="{{ asset('images/logo.png') }}" alt="Purplestuff Merch" class="h-14 w-14 rounded-full object-cover shadow-lg ring-2 ring-white/30">
        <h1 class="mt-5 text-3xl font-extrabold tracking-tight md:text-4xl">Returns</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/82 md:text-base">
          Kebijakan pengembalian dibuat agar pelanggan memahami ketentuan sebelum mengajukan bantuan kepada admin.
        </p>
      </div>

      <div class="p-6 md:p-10">
        <div class="space-y-4">
          <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
            <h2 class="text-lg font-extrabold text-slate-950">1. Batas pengajuan</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pengajuan pengembalian maksimal 7 hari setelah barang diterima pelanggan.</p>
          </div>
          <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
            <h2 class="text-lg font-extrabold text-slate-950">2. Kondisi barang</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Barang harus dalam kondisi baru, tidak digunakan, dan kemasan masih lengkap.</p>
          </div>
          <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
            <h2 class="text-lg font-extrabold text-slate-950">3. Bukti pendukung</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Jika barang rusak atau cacat produksi, pelanggan perlu menyertakan foto/video sebagai bukti.</p>
          </div>
          <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/80">
            <h2 class="text-lg font-extrabold text-slate-950">4. Proses bantuan</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Hubungi Contact Us dengan menyertakan nomor pesanan agar admin dapat membantu pengecekan.</p>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 border-t border-slate-200/80 px-6 py-6 md:px-10">
        <a href="{{ route('pages.faq') }}" class="ps-btn-primary ps-btn-sm">FAQ →</a>
        <a href="{{ route('pages.shipping') }}" class="ps-btn-light ps-btn-sm">← Shipping Info</a>
      </div>
    </section>
  </div>
</div>
@endsection
