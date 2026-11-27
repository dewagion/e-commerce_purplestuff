@extends('layouts.admin')

@php
  $heading = 'Kelola Artist';
  $subheading = 'Tambah, edit, dan hapus artist.';
@endphp

@section('content')
  <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <div class="rounded-2xl bg-white p-6 shadow-sm">
      <h2 class="text-lg font-bold">{{ $isEdit ? 'Edit Artist' : 'Tambah Artist' }}</h2>

      <form method="POST" action="{{ $isEdit ? route('admin.artists.update', $artist) : route('admin.artists.store') }}" class="mt-5 space-y-4">
        @csrf
        @if($isEdit)
          @method('PUT')
        @endif

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Artist</label>
          <input type="text" name="name" value="{{ old('name', $artist->name) }}"
                 class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" required>
        </div>

        <div class="flex gap-3">
          <button type="submit"
                  class="w-full rounded-xl bg-purple-600 px-4 py-3 font-semibold text-white hover:bg-purple-500">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Artist' }}
          </button>

          @if($isEdit)
            <a href="{{ route('admin.artists.index') }}"
               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-center font-semibold text-slate-700 hover:bg-slate-50">
              Batal
            </a>
          @endif
        </div>
      </form>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
              <th class="px-4 py-3">Nama</th>
              <th class="px-4 py-3">Slug</th>
              <th class="px-4 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($artists as $row)
              <tr>
                <td class="px-4 py-4 font-semibold">{{ $row->name }}</td>
                <td class="px-4 py-4 text-slate-500">{{ $row->slug }}</td>
                <td class="px-4 py-4">
                  <div class="flex gap-2">
                    <a href="{{ route('admin.artists.edit', $row) }}"
                       class="rounded-lg bg-amber-500 px-3 py-2 text-white hover:bg-amber-400">
                      Edit
                    </a>

                    <form method="POST" action="{{ route('admin.artists.destroy', $row) }}"
                          onsubmit="return confirm('Hapus artist ini?')">
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
                <td colspan="3" class="px-4 py-10 text-center text-slate-500">Belum ada artist.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-4">
        {{ $artists->links() }}
      </div>
    </div>
  </div>
@endsection