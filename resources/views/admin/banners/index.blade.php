@extends('layouts.admin')

@php
  $heading = 'Kelola Banner Dashboard';
  $subheading = 'Atur banner yang tampil di dashboard dari admin.';

  $selectedProducts = collect(old('target_product_ids', $banner->target_product_ids_list ?? []))
      ->map(fn ($id) => (string) $id)
      ->values()
      ->all();

  $targetType = old('target_type', $banner->resolved_target_type ?? 'products');

  if (!$isEdit && old('target_type') === null) {
      $targetType = 'products';
  }

  if ($targetType === 'manual') {
      $targetType = 'products';
  }

  if (!in_array($targetType, ['all', 'category', 'artist', 'products', 'custom'], true)) {
      $targetType = 'products';
  }

  $targetCategorySlug = old('target_category_slug', $banner->target_category_slug ?? '');
  $targetArtistSlug = old('target_artist_slug', $banner->target_artist_slug ?? '');
  $dashboardSection = old('dashboard_section', $banner->resolved_dashboard_section ?? 'hero');
  $dashboardSectionOptions = $dashboardSectionOptions ?? \App\Models\Banner::dashboardSectionOptions();
  $isCategoryGridEdit = $isEdit && $dashboardSection === 'category_grid';

  if ($dashboardSection === 'category_grid') {
      $targetType = 'category';
  }

  $quickCards = old('quick_cards', []);
@endphp

@section('content')
  @if(session('success'))
    <div class="mb-5 rounded-2xl border border-green-100 bg-green-50 px-5 py-4 text-sm font-semibold text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-5 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4 text-sm text-rose-700">
      <div class="font-bold">Ada data yang perlu diperbaiki:</div>
      <ul class="mt-2 list-disc space-y-1 pl-5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="grid gap-6 xl:grid-cols-[520px_1fr]">
    <div class="rounded-3xl bg-white p-6 shadow-sm">
      <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">{{ $isEdit ? 'Edit Banner' : 'Tambah Banner' }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $isCategoryGridEdit ? 'Atur satu kartu dari banner 3 kolom.' : 'Pilih jenis banner, isi data yang diperlukan, lalu simpan.' }}</p>
      </div>

      <form method="POST"
            action="{{ $isEdit ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
            enctype="multipart/form-data"
            class="space-y-5">
        @csrf
        @if($isEdit)
          @method('PUT')
        @endif

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Penempatan di Dashboard</label>
          <select name="dashboard_section"
                  id="dashboard-section"
                  data-is-edit="{{ $isEdit ? '1' : '0' }}"
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
            @foreach($dashboardSectionOptions as $value => $label)
              <option value="{{ $value }}" @selected($dashboardSection === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        @if(!$isEdit)
          <div id="category-grid-create-fields" class="hidden rounded-2xl border border-purple-100 bg-purple-50/70 p-4">
            <input type="hidden" name="use_quick_category_grid" value="1">
            <div class="mb-4 flex items-center justify-between gap-3">
              <div>
                <div class="text-base font-bold text-slate-900">3 Kartu Kategori</div>
                
              </div>
              
            </div>

            <div class="space-y-4">
              @for($i = 0; $i < 3; $i++)
                @php
                  $quickCard = $quickCards[$i] ?? [];
                  $quickTitle = $quickCard['title'] ?? '';
                  $quickCategory = $quickCard['target_category_slug'] ?? '';
                @endphp
                <div class="rounded-2xl border border-purple-100 bg-white p-4">
                  <div class="mb-3 text-sm font-bold text-slate-900">Kartu {{ $i + 1 }}</div>
                  <div class="grid gap-3">
                    <div>
                      <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Judul</label>
                      <input type="text"
                             name="quick_cards[{{ $i }}][title]"
                             value="{{ $quickTitle }}"
                             class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200"
                             placeholder="Contoh: Lightstick">
                    </div>

                    <div>
                      <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Kategori Tujuan</label>
                      <select name="quick_cards[{{ $i }}][target_category_slug]"
                              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                          <option value="{{ $category->slug }}" @selected($quickCategory === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div>
                      <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Gambar</label>
                      <input type="file"
                             name="quick_cards[{{ $i }}][image_file]"
                             accept=".jpg,.jpeg,.png,.webp,image/*"
                             class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-2 file:font-bold file:text-purple-700 hover:file:bg-purple-100">
                    </div>
                  </div>
                </div>
              @endfor
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Teks Tombol</label>
                <input type="text"
                       name="cta_text"
                       value="{{ old('cta_text', 'Shop Now') }}"
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200"
                       placeholder="Shop Now">
              </div>

              <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Urutan Awal</label>
                <input type="number"
                       name="sort_order"
                       value="{{ old('sort_order', 0) }}"
                       min="0"
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
              </div>
            </div>

            <label class="mt-4 inline-flex w-full items-center gap-3 rounded-xl border border-purple-100 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
              <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
              Aktifkan 3 kartu ini
            </label>
          </div>
        @endif

        <div id="standard-banner-fields" class="space-y-5">
          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">{{ $isCategoryGridEdit ? 'Judul Kartu' : 'Judul Banner' }}</label>
            <input type="text"
                   name="title"
                   value="{{ old('title', $banner->title) }}"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200"
                   placeholder="Contoh: New Collection atau Lightstick">
          </div>

          <div class="standard-detail-field {{ $isCategoryGridEdit ? 'hidden' : '' }}">
            <label class="mb-2 block text-sm font-semibold text-slate-700">Subjudul</label>
            <textarea name="subtitle"
                      rows="3"
                      class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200"
                      placeholder="Tulis keterangan singkat banner.">{{ old('subtitle', $banner->subtitle) }}</textarea>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="standard-detail-field {{ $isCategoryGridEdit ? 'hidden' : '' }}">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Badge</label>
              <input type="text"
                     name="badge_text"
                     value="{{ old('badge_text', $banner->badge_text) }}"
                     class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200"
                     placeholder="Contoh: New Drop">
            </div>

            <div>
              <label class="mb-2 block text-sm font-semibold text-slate-700">Teks Tombol</label>
              <input type="text"
                     name="cta_text"
                     value="{{ old('cta_text', $banner->cta_text ?: 'Shop Now') }}"
                     class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200"
                     placeholder="Shop Now">
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-[1fr_auto]">
            <div>
              <label class="mb-2 block text-sm font-semibold text-slate-700">Urutan Tampil</label>
              <input type="number"
                     name="sort_order"
                     value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
                     min="0"
                     class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
            </div>

            <div class="flex items-end">
              <label class="inline-flex w-full items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true))>
                Aktifkan
              </label>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">{{ $isCategoryGridEdit ? 'Gambar Kartu' : 'Gambar Banner' }}</label>
            <input type="file"
                   name="image_file"
                   accept=".jpg,.jpeg,.png,.webp,image/*"
                   class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:font-semibold file:text-purple-700 hover:file:bg-purple-100">
          </div>

          @if($isEdit && $banner->image)
            <div>
              <label class="mb-2 block text-sm font-semibold text-slate-700">{{ $isCategoryGridEdit ? 'Gambar Kartu Saat Ini' : 'Banner Saat Ini' }}</label>
              <img src="{{ $banner->image }}"
                   alt="{{ $banner->title }}"
                   class="h-44 w-full rounded-2xl border border-slate-200 object-cover">
            </div>
          @endif

          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="mb-4 text-base font-bold text-slate-900">{{ $isCategoryGridEdit ? 'Kategori Tujuan Kartu' : 'Tujuan Banner' }}</div>

            <div id="target-type-wrapper" class="{{ $isCategoryGridEdit ? 'hidden' : '' }}">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Jenis Tujuan</label>
              <select name="target_type"
                      id="banner-target-type"
                      class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
                <option value="products" @selected($targetType === 'products')>Produk pilihan</option>
                <option value="all" @selected($targetType === 'all')>Semua produk</option>
                <option value="category" @selected($targetType === 'category')>Kategori tertentu</option>
                <option value="artist" @selected($targetType === 'artist')>Artist tertentu</option>
                <option value="custom" @selected($targetType === 'custom')>Link custom</option>
              </select>
            </div>

            <div id="target-category-section" class="mt-4 {{ $isCategoryGridEdit || $targetType === 'category' ? '' : 'hidden' }}">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Kategori</label>
              <select name="target_category_slug"
                      class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                  <option value="{{ $category->slug }}" @selected($targetCategorySlug === $category->slug)>{{ $category->name }}</option>
                @endforeach
              </select>
            </div>

            <div id="target-artist-section" class="mt-4 {{ !$isCategoryGridEdit && $targetType === 'artist' ? '' : 'hidden' }}">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Artist</label>
              <select name="target_artist_slug"
                      class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
                <option value="">Semua artist</option>
                @foreach($artists as $artist)
                  <option value="{{ $artist->slug }}" @selected($targetArtistSlug === $artist->slug)>{{ $artist->name }}</option>
                @endforeach
              </select>
            </div>

            <div id="target-custom-section" class="mt-4 {{ !$isCategoryGridEdit && $targetType === 'custom' ? '' : 'hidden' }}">
              <label class="mb-2 block text-sm font-semibold text-slate-700">Link Custom</label>
              <input type="text"
                     name="cta_link"
                     value="{{ old('cta_link', $banner->cta_link) }}"
                     class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200"
                     placeholder="Contoh: /products?category=album">
            </div>

            <div id="target-products-section" class="mt-4 {{ !$isCategoryGridEdit && $targetType === 'products' ? '' : 'hidden' }}">
              <div class="mb-3 flex items-center justify-between gap-3">
                <label class="text-sm font-semibold text-slate-700">Pilih Produk</label>
                <span id="banner-selected-count" class="text-xs font-semibold text-purple-600">{{ count($selectedProducts) }} produk dipilih</span>
              </div>

              <input type="text"
                     id="banner-product-search"
                     placeholder="Cari produk..."
                     class="mb-3 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-purple-300 focus:ring-2 focus:ring-purple-200">

              <div id="banner-product-list" class="max-h-72 space-y-3 overflow-y-auto pr-1">
                @foreach($products as $product)
                  @php
                    $searchText = strtolower(trim(
                      ($product->name ?? '') . ' ' .
                      ($product->artist?->name ?? '') . ' ' .
                      ($product->category?->name ?? '') . ' ' .
                      ($product->sku ?? '')
                    ));
                  @endphp

                  <label class="banner-product-item flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 hover:bg-slate-50"
                         data-search="{{ $searchText }}">
                    <input type="checkbox"
                           class="banner-product-checkbox"
                           name="target_product_ids[]"
                           value="{{ $product->id }}"
                           @checked(in_array((string) $product->id, $selectedProducts, true))>

                    <img src="{{ $product->primary_image }}"
                         alt="{{ $product->name }}"
                         class="h-14 w-14 rounded-xl bg-slate-100 object-cover">

                    <div class="min-w-0 flex-1">
                      <div class="truncate font-semibold text-slate-900">{{ $product->name }}</div>
                      <div class="text-xs text-slate-500">{{ $product->artist?->name ?? '-' }}</div>
                      <div class="text-xs text-slate-400">{{ $product->category?->name ?? '-' }}</div>
                    </div>
                  </label>
                @endforeach

                <div id="banner-product-empty" class="hidden rounded-xl border border-dashed border-slate-300 bg-white px-4 py-8 text-center text-sm text-slate-500">
                  Produk tidak ditemukan.
                </div>
              </div>
            </div>
          </div>
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-purple-600 px-4 py-3 font-semibold text-white hover:bg-purple-500">
          {{ $isEdit ? ($isCategoryGridEdit ? 'Simpan Kartu' : 'Simpan Perubahan') : 'Tambah Banner' }}
        </button>

        @if($isEdit)
          <a href="{{ route('admin.banners.index') }}"
             class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Batal Edit
          </a>
        @endif
      </form>
    </div>

    <div class="space-y-4">
      @forelse($banners as $row)
        @php
          $rowTargetType = $row->resolved_target_type;
          $rowProductIds = $row->target_product_ids_list;
          $rowTargetLabel = 'Semua produk';
          $rowSection = $row->resolved_dashboard_section;
          $rowSectionLabel = $dashboardSectionOptions[$rowSection] ?? 'Hero Slider Utama';

          if ($rowTargetType === 'category') {
              $rowTargetLabel = 'Kategori: ' . ($categories->firstWhere('slug', $row->target_category_slug)?->name ?? $row->target_category_slug ?? 'Semua kategori');
          } elseif ($rowTargetType === 'artist') {
              $rowTargetLabel = 'Artist: ' . ($artists->firstWhere('slug', $row->target_artist_slug)?->name ?? $row->target_artist_slug ?? 'Semua artist');
          } elseif ($rowTargetType === 'products') {
              $rowTargetLabel = count($rowProductIds) . ' produk pilihan';
          } elseif ($rowTargetType === 'custom') {
              $rowTargetLabel = 'Link custom';
          }
        @endphp

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
          <div class="grid gap-0 lg:grid-cols-[230px_1fr]">
            <div>
              <img src="{{ $row->image }}"
                   alt="{{ $row->title }}"
                   class="h-full min-h-[210px] w-full bg-slate-100 object-cover">
            </div>

            <div class="p-5">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <div class="text-xs font-bold uppercase tracking-wide text-purple-600">{{ $rowSectionLabel }}</div>
                  <h3 class="mt-2 text-xl font-bold text-slate-900">{{ $row->title }}</h3>
                  @if($row->subtitle)
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $row->subtitle }}</p>
                  @endif
                </div>

                <div class="flex flex-wrap gap-2">
                  <span class="rounded-full {{ $row->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-xs font-semibold">
                    {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                  <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Urutan {{ $row->sort_order }}</span>
                </div>
              </div>

              <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                <span class="font-semibold text-slate-900">Tujuan:</span> {{ $rowTargetLabel }}
              </div>

              <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('admin.banners.edit', $row) }}"
                   class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-400">
                  {{ $rowSection === 'category_grid' ? 'Atur Kartu' : 'Edit' }}
                </a>

                <a href="{{ $row->cta_link ?: route('products.index') }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="rounded-xl border border-purple-200 px-4 py-2.5 text-sm font-semibold text-purple-700 hover:bg-purple-50">
                  Preview
                </a>

                <form method="POST" action="{{ route('admin.banners.destroy', $row) }}" onsubmit="return confirm('Hapus banner ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-500">
                    Hapus
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
          <div class="text-lg font-bold text-slate-700">Belum ada banner</div>
          <div class="mt-2 text-sm text-slate-500">Tambahkan banner dari form kiri.</div>
        </div>
      @endforelse

      <div class="pt-2">
        {{ $banners->links() }}
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const dashboardSectionInput = document.getElementById('dashboard-section');
      const quickFields = document.getElementById('category-grid-create-fields');
      const standardFields = document.getElementById('standard-banner-fields');
      const isEdit = dashboardSectionInput && dashboardSectionInput.dataset.isEdit === '1';

      const targetTypeInput = document.getElementById('banner-target-type');
      const categorySection = document.getElementById('target-category-section');
      const artistSection = document.getElementById('target-artist-section');
      const customSection = document.getElementById('target-custom-section');
      const productsSection = document.getElementById('target-products-section');

      const standardDetailFields = Array.from(document.querySelectorAll('.standard-detail-field'));
      const targetTypeWrapper = document.getElementById('target-type-wrapper');
      const bannerTitleLabel = document.getElementById('banner-title-label');
      const bannerImageLabel = document.getElementById('banner-image-label');
      const bannerCurrentImageLabel = document.getElementById('banner-current-image-label');
      const bannerTargetTitle = document.getElementById('banner-target-title');

      const syncBannerMode = () => {
        const section = dashboardSectionInput ? dashboardSectionInput.value : 'hero';
        const isCategoryGridCreate = !isEdit && section === 'category_grid';
        const isCategoryGridEdit = isEdit && section === 'category_grid';

        if (quickFields) quickFields.classList.toggle('hidden', !isCategoryGridCreate);
        if (standardFields) standardFields.classList.toggle('hidden', isCategoryGridCreate);

        standardDetailFields.forEach(field => field.classList.toggle('hidden', isCategoryGridEdit));
        if (targetTypeWrapper) targetTypeWrapper.classList.toggle('hidden', isCategoryGridEdit);

        if (isCategoryGridEdit && targetTypeInput) {
          targetTypeInput.value = 'category';
        }

        if (bannerTitleLabel) bannerTitleLabel.textContent = isCategoryGridEdit ? 'Judul Kartu' : 'Judul Banner';
        if (bannerImageLabel) bannerImageLabel.textContent = isCategoryGridEdit ? 'Gambar Kartu' : 'Gambar Banner';
        if (bannerCurrentImageLabel) bannerCurrentImageLabel.textContent = isCategoryGridEdit ? 'Gambar Kartu Saat Ini' : 'Banner Saat Ini';
        if (bannerTargetTitle) bannerTargetTitle.textContent = isCategoryGridEdit ? 'Kategori Tujuan Kartu' : 'Tujuan Banner';

        syncTargetSections();
      };

      const syncTargetSections = () => {
        const section = dashboardSectionInput ? dashboardSectionInput.value : 'hero';
        const isCategoryGridEdit = isEdit && section === 'category_grid';
        const type = isCategoryGridEdit ? 'category' : (targetTypeInput ? targetTypeInput.value : 'products');

        if (categorySection) categorySection.classList.toggle('hidden', type !== 'category');
        if (artistSection) artistSection.classList.toggle('hidden', type !== 'artist' || isCategoryGridEdit);
        if (customSection) customSection.classList.toggle('hidden', type !== 'custom' || isCategoryGridEdit);
        if (productsSection) productsSection.classList.toggle('hidden', type !== 'products' || isCategoryGridEdit);
      };

      if (dashboardSectionInput) {
        dashboardSectionInput.addEventListener('change', syncBannerMode);
        syncBannerMode();
      }

      if (targetTypeInput) {
        targetTypeInput.addEventListener('change', syncTargetSections);
        syncTargetSections();
      }

      const searchInput = document.getElementById('banner-product-search');
      const items = Array.from(document.querySelectorAll('.banner-product-item'));
      const emptyBox = document.getElementById('banner-product-empty');
      const selectedCountBox = document.getElementById('banner-selected-count');
      const checkboxes = Array.from(document.querySelectorAll('.banner-product-checkbox'));

      const updateSelectedCount = () => {
        const selected = checkboxes.filter(checkbox => checkbox.checked).length;
        if (selectedCountBox) selectedCountBox.textContent = selected + ' produk dipilih';
      };

      checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateSelectedCount));
      updateSelectedCount();

      if (searchInput) {
        const filterProducts = () => {
          const keyword = (searchInput.value || '').toLowerCase().trim();
          let visibleCount = 0;

          items.forEach(item => {
            const matched = keyword === '' || (item.dataset.search || '').includes(keyword);
            item.style.display = matched ? 'flex' : 'none';
            if (matched) visibleCount++;
          });

          if (emptyBox) emptyBox.classList.toggle('hidden', visibleCount !== 0);
        };

        searchInput.addEventListener('input', filterProducts);
        filterProducts();
      }
    });
  </script>
@endsection
