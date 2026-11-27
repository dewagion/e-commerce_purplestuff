@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
  <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 sm:p-8">
    <div class="text-xl font-extrabold text-slate-900">Reset Password</div>
    <div class="mt-1 text-sm text-slate-500">Masukkan password baru untuk akun kamu.</div>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div>
        <label class="text-sm font-semibold">Email</label>
        <input name="email" type="email" value="{{ old('email', $email) }}"
               class="mt-2 w-full rounded-2xl bg-slate-100 border border-transparent focus:border-purple-300 focus:ring-purple-200 px-4 py-3"
               required>
        @error('email') <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="text-sm font-semibold">Password Baru</label>
        <input name="password" type="password"
               class="mt-2 w-full rounded-2xl bg-slate-100 border border-transparent focus:border-purple-300 focus:ring-purple-200 px-4 py-3"
               required>
        @error('password') <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="text-sm font-semibold">Konfirmasi Password</label>
        <input name="password_confirmation" type="password"
               class="mt-2 w-full rounded-2xl bg-slate-100 border border-transparent focus:border-purple-300 focus:ring-purple-200 px-4 py-3"
               required>
      </div>

      <button class="w-full rounded-2xl bg-black text-white font-semibold py-3 hover:bg-black/90">
        Simpan Password
      </button>
    </form>
  </div>
</div>
@endsection