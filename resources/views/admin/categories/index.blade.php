@extends('layouts.admin')

@php
  $heading = 'Kelola Kategori';
  $subheading = 'Tambah, edit, dan hapus kategori beserta icon-nya.';

  $bgOptions = [
    'bg-purple-100' => 'Ungu Lembut',
    'bg-rose-100' => 'Pink Lembut',
    'bg-blue-100' => 'Biru Lembut',
    'bg-green-100' => 'Hijau Lembut',
    'bg-yellow-100' => 'Kuning Lembut',
    'bg-pink-100' => 'Pink Pastel',
    'bg-fuchsia-100' => 'Fuchsia Lembut',
    'bg-slate-100' => 'Abu Lembut',

    'from-fuchsia-500 to-violet-500' => 'Gradient Ungu',
    'from-pink-500 to-rose-500' => 'Gradient Pink',
    'from-sky-500 to-blue-500' => 'Gradient Biru',
    'from-emerald-500 to-green-500' => 'Gradient Hijau',
    'from-amber-400 to-yellow-500' => 'Gradient Kuning',
  ];

  $selectedIcon = old('icon', $category->icon ?: 'star');
  $selectedBg = old('bg', $category->bg ?: 'bg-purple-100');
  $selectedName = old('name', $category->name ?: '');
@endphp

@section('content')
  <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
    <div class="rounded-2xl bg-white p-6 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900">
        {{ $isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}
      </h2>

      <form method="POST"
            action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
            class="mt-5 space-y-4">
        @csrf
        @if($isEdit)
          @method('PUT')
        @endif

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Kategori</label>
          <input type="text"
                 id="categoryNameInput"
                 name="name"
                 value="{{ $selectedName }}"
                 class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200"
                 required>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Icon</label>
          <select id="categoryIconSelect"
                  name="icon"
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200"
                  required>
            @foreach($iconOptions as $icon)
              <option value="{{ $icon }}" @selected($selectedIcon === $icon)>
                {{ ucfirst($icon) }}
              </option>
            @endforeach
          </select>
          <p class="mt-2 text-xs text-slate-500">
            Pilih icon yang sudah tersedia agar SVG selalu muncul dengan aman di frontend.
          </p>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Background Icon</label>
          <select id="categoryBgSelect"
                  name="bg"
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
            @foreach($bgOptions as $value => $label)
              <option value="{{ $value }}" @selected($selectedBg === $value)>
                {{ $label }}
              </option>
            @endforeach
          </select>
          <p class="mt-2 text-xs text-slate-500">
            Gunakan warna lembut atau gradient agar tampilan kategori tetap konsisten.
          </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <div class="mb-3 text-sm font-semibold text-slate-700">Preview Kategori</div>

          <div class="flex items-center gap-4">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-sm">
              <div id="categoryPreviewCircle"
                   class="flex h-12 w-12 items-center justify-center rounded-full {{ $selectedBg }} text-slate-700 {{ str_contains($selectedBg, 'from-') ? 'bg-gradient-to-r text-white' : '' }}">
                <span id="categoryPreviewIcon" class="flex items-center justify-center">
                  @include('partials.category-icon', [
                    'icon' => $selectedIcon,
                    'class' => 'h-6 w-6'
                  ])
                </span>
              </div>
            </div>

            <div>
              <div id="categoryPreviewName" class="font-semibold text-slate-900">
                {{ $selectedName ?: 'Nama Kategori' }}
              </div>
              <div class="text-sm text-slate-500">
                Icon: <span id="categoryPreviewIconText">{{ $selectedIcon }}</span>
              </div>
              <div class="text-xs text-slate-400">
                BG: <span id="categoryPreviewBgText">{{ $selectedBg }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button type="submit"
                  class="w-full rounded-xl bg-purple-600 px-4 py-3 font-semibold text-white hover:bg-purple-500">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Kategori' }}
          </button>

          @if($isEdit)
            <a href="{{ route('admin.categories.index') }}"
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
              <th class="px-4 py-3">Icon</th>
              <th class="px-4 py-3">Preview</th>
              <th class="px-4 py-3">BG</th>
              <th class="px-4 py-3">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100">
            @forelse($categories as $row)
              <tr>
                <td class="px-4 py-4 font-semibold text-slate-900">{{ $row->name }}</td>
                <td class="px-4 py-4 text-slate-500">{{ $row->slug }}</td>
                <td class="px-4 py-4">{{ $row->icon }}</td>

                <td class="px-4 py-4">
                  <div class="flex h-12 w-12 items-center justify-center rounded-full {{ $row->bg ?: 'bg-purple-100' }} text-slate-700 {{ str_contains($row->bg ?? '', 'from-') ? 'bg-gradient-to-r text-white' : '' }}">
                    @include('partials.category-icon', [
                      'icon' => $row->icon,
                      'class' => 'h-5 w-5'
                    ])
                  </div>
                </td>

                <td class="px-4 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold {{ str_contains($row->bg ?? '', 'from-') ? 'text-white bg-gradient-to-r ' . $row->bg : 'text-slate-700 ' . ($row->bg ?: 'bg-purple-100') }}">
                    Preview
                  </span>
                </td>

                <td class="px-4 py-4">
                  <div class="flex gap-2">
                    <a href="{{ route('admin.categories.edit', $row) }}"
                       class="rounded-lg bg-amber-500 px-3 py-2 text-white hover:bg-amber-400">
                      Edit
                    </a>

                    <form method="POST"
                          action="{{ route('admin.categories.destroy', $row) }}"
                          onsubmit="return confirm('Hapus kategori ini?')">
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
                <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                  Belum ada kategori.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-4">
        {{ $categories->links() }}
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const nameInput = document.getElementById('categoryNameInput');
      const iconSelect = document.getElementById('categoryIconSelect');
      const bgSelect = document.getElementById('categoryBgSelect');

      const previewName = document.getElementById('categoryPreviewName');
      const previewIcon = document.getElementById('categoryPreviewIcon');
      const previewIconText = document.getElementById('categoryPreviewIconText');
      const previewBgText = document.getElementById('categoryPreviewBgText');
      const previewCircle = document.getElementById('categoryPreviewCircle');

      if (!nameInput || !iconSelect || !bgSelect || !previewName || !previewIcon || !previewCircle) {
        return;
      }

      const iconSvg = {
        bag: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12l-1 12H7L6 7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7a3 3 0 116 0"/></svg>',
        star: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3.5 2.78 5.63 6.22.91-4.5 4.39 1.06 6.2L12 17.77 6.44 20.63l1.06-6.2L3 10.04l6.22-.91L12 3.5z"/></svg>',
        album: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="5" y="5" width="14" height="14" rx="2" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke-width="1.8"/></svg>',
        hoodie: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9a4 4 0 118 0l2 2v8H6v-8l2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 13h4"/></svg>',
        bolt: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 2 5 14h6l-1 8 8-12h-6l1-8z"/></svg>',
        card: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="4" y="6" width="16" height="12" rx="2" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14l2-2 2 2 3-3 2 2"/></svg>',
        shirt: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 4l3 2 3-2 4 3-2 4h-2v9H9v-9H7L5 7l4-3z"/></svg>',
        tshirt: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 5l4 2 4-2 3 3-2 4h-2v7H9v-7H7L5 8l3-3z"/></svg>',
        fallback: '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="8" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l2.5 2.5"/></svg>'
      };

      function updateCategoryPreview() {
        const name = nameInput.value.trim() || 'Nama Kategori';
        const icon = iconSelect.value || 'star';
        const bg = bgSelect.value || 'bg-purple-100';
        const isGradient = bg.includes('from-');

        previewName.textContent = name;
        previewIcon.innerHTML = iconSvg[icon] || iconSvg.fallback;
        previewIconText.textContent = icon;
        previewBgText.textContent = bg;

        previewCircle.className = [
          'flex h-12 w-12 items-center justify-center rounded-full',
          bg,
          isGradient ? 'bg-gradient-to-r text-white' : 'text-slate-700'
        ].join(' ');
      }

      nameInput.addEventListener('input', updateCategoryPreview);
      iconSelect.addEventListener('change', updateCategoryPreview);
      bgSelect.addEventListener('change', updateCategoryPreview);

      updateCategoryPreview();
    });
  </script>
@endsection
