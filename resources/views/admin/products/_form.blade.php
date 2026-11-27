@php
  $isEdit = $product->exists;
  $currentImages = $product->relationLoaded('images') ? $product->images : collect();
  $currentGalleryImages = $currentImages->reject(fn ($image) => $image->path === $product->primary_image)->values();
  $initialStockRows = old('option_stock_rows', $optionStockRows ?? []);

  if (!is_array($initialStockRows) || count($initialStockRows) === 0) {
      $initialStockRows = [[
          'variant' => '',
          'size' => '',
          'stock' => old('stock', $product->exists ? (int) ($product->stock ?? 0) : 0),
          'price' => null,
          'discount_price' => null,
          'discount_active' => false,
          'image_path' => null,
      ]];
  }

  $initialStockTotal = collect($initialStockRows)->sum(fn ($row) => max(0, (int) ($row['stock'] ?? 0)));
  if ($initialStockTotal <= 0) {
      $initialStockTotal = (int) old('stock', $product->exists ? ($product->stock ?? 0) : 0);
  }
@endphp

<div class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_420px]">
  <div class="min-w-0 space-y-6">
    <div class="rounded-2xl bg-white p-6 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900">Informasi Utama</h2>

      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Produk</label>
          <input type="text" name="name" value="{{ old('name', $product->name) }}"
                 class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" required>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Artist</label>
          <select name="artist_id"
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" required>
            <option value="">Pilih artist</option>
            @foreach($artists as $artist)
              <option value="{{ $artist->id }}" @selected(old('artist_id', $product->artist_id) == $artist->id)>
                {{ $artist->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Kategori</label>
          <select name="category_id"
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" required>
            <option value="">Pilih kategori</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                {{ $category->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">SKU</label>
          <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                 class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200"
                 placeholder="Contoh: K-001">
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Type</label>
          <select name="type"
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" required>
            <option value="accessory" @selected(old('type', $product->type ?: 'accessory') === 'accessory')>Accessory</option>
            <option value="apparel" @selected(old('type', $product->type) === 'apparel')>Apparel</option>
          </select>
        </div>

        <div class="sm:col-span-2 rounded-2xl border border-purple-100 bg-purple-50/50 p-4">
          <label class="mb-2 block text-sm font-bold text-slate-800">Status Tampilan Produk</label>
          <select name="product_status"
                  class="w-full rounded-xl border border-purple-100 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
            <option value="ready" @selected(old('product_status', $product->product_status ?: 'ready') === 'ready')>Ready Stock / Normal</option>
            <option value="pre_order" @selected(old('product_status', $product->product_status ?: 'ready') === 'pre_order')>Pre-Order</option>
            <option value="upcoming" @selected(old('product_status', $product->product_status ?: 'ready') === 'upcoming')>Upcoming</option>
          </select>
          <p class="mt-2 text-xs leading-relaxed text-slate-500">
            Label Pre-Order atau Upcoming akan tampil di atas harga pada kartu produk dan detail produk. Pengaturan ini hanya label tampilan, ketersediaan beli tetap mengikuti stok.
          </p>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Harga</label>
          <input type="number" name="price" value="{{ old('price', $product->price) }}"
                 class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" required>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <label class="block text-sm font-bold text-slate-800">Harga Diskon</label>
              <p class="mt-1 text-xs leading-relaxed text-slate-500">Aktifkan jika produk sedang promo. Harga diskon akan dipakai di cart dan checkout.</p>
            </div>
            <label class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-bold text-purple-700 shadow-sm">
              <input type="checkbox" name="discount_active" value="1" class="rounded border-purple-200 text-purple-600 focus:ring-purple-200"
                     @checked(old('discount_active', $product->discount_active ?? false))>
              Tampilkan
            </label>
          </div>
          <input type="number" name="discount_price" min="0" value="{{ old('discount_price', $product->discount_price) }}"
                 class="mt-3 w-full rounded-xl border border-amber-100 bg-white px-4 py-3 font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-purple-200"
                 placeholder="Contoh: 650000">
          <p class="mt-2 text-xs leading-relaxed text-slate-500">Kosongkan atau nonaktifkan checkbox jika produk tidak diskon. Harga diskon sebaiknya lebih kecil dari harga normal.</p>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Berat (gram)</label>
          <input type="number" name="weight" value="{{ old('weight', $product->weight ?: 0) }}" min="1"
                 class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" required>
        </div>

        <div class="sm:col-span-2">
          <label class="mb-2 block text-sm font-semibold text-slate-700">Deskripsi</label>
          <textarea name="desc" rows="5"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">{{ old('desc', $product->desc) }}</textarea>
        </div>

        <div class="sm:col-span-2">
          <label class="mb-2 block text-sm font-semibold text-slate-700">Info Shipping</label>
          <input type="text" name="shipping" value="{{ old('shipping', $product->shipping) }}"
                 class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
        </div>
      </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm" data-stock-manager>
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h2 class="text-lg font-bold text-slate-900">Opsi & Stok Produk</h2>
          <p class="mt-1 text-sm leading-relaxed text-slate-500">
            Atur pilihan, ukuran, dan stok dalam satu tempat agar tidak perlu mengisi stok di sisi kanan form.
          </p>
        </div>
        <div class="rounded-2xl bg-purple-50 px-4 py-3 text-right">
          <p class="text-xs font-bold uppercase tracking-wide text-purple-600">Total stok</p>
          <div data-total-stock-display
               class="mt-1 min-w-24 rounded-xl border border-purple-100 bg-white px-4 py-2 text-center text-xl font-black text-purple-700">
            {{ $initialStockTotal }}
          </div>
          <input type="hidden" name="stock" value="{{ $initialStockTotal }}" data-total-stock-value required>
        </div>
      </div>

      <div class="mt-5 grid gap-3 rounded-2xl border border-violet-100 bg-violet-50/60 p-4 md:grid-cols-2">
        <label class="flex items-start gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-violet-100/70">
          <input type="checkbox" name="option_pricing_active" value="1" class="mt-1 rounded border-violet-200 text-violet-600 focus:ring-violet-200"
                 @checked(old('option_pricing_active', $product->option_pricing_active ?? false))>
          <span>
            <span class="block text-sm font-black text-slate-900">Aktifkan harga berbeda per pilihan</span>
            <span class="mt-1 block text-xs leading-relaxed text-slate-500">Jika aktif, harga pada tabel stok akan dipakai sesuai variant/ukuran yang dipilih user.</span>
          </span>
        </label>

        <label class="flex items-start gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-violet-100/70">
          <input type="checkbox" name="option_media_active" value="1" class="mt-1 rounded border-violet-200 text-violet-600 focus:ring-violet-200"
                 @checked(old('option_media_active', $product->option_media_active ?? false))>
          <span>
            <span class="block text-sm font-black text-slate-900">Aktifkan gambar berbeda per pilihan</span>
            <span class="mt-1 block text-xs leading-relaxed text-slate-500">Jika aktif, gambar detail produk akan mengikuti variant/ukuran yang dipilih user.</span>
          </span>
        </label>
      </div>

      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <label class="mb-2 block text-sm font-bold text-slate-800">Variants / Pilihan Produk</label>
          <textarea name="variants_text" rows="4" data-option-source
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200"
                    placeholder="Black&#10;White&#10;Purple">{{ old('variants_text', $variantsText) }}</textarea>
          <p class="mt-2 text-xs leading-relaxed text-slate-500">Satu baris = satu pilihan. Kosongkan jika produk tidak punya warna/tipe.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <label class="mb-2 block text-sm font-bold text-slate-800">Sizes / Ukuran</label>
          <textarea name="sizes_text" rows="4" data-option-source
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200"
                    placeholder="S&#10;M&#10;L">{{ old('sizes_text', $sizesText) }}</textarea>
          <p class="mt-2 text-xs leading-relaxed text-slate-500">Satu baris = satu ukuran. Kosongkan jika produk tidak punya ukuran.</p>
        </div>
      </div>

      <div class="mt-5 rounded-2xl border border-purple-100 bg-purple-50/50 p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <label class="block text-sm font-bold text-slate-900">Stok Per Item</label>
            <p class="mt-1 text-xs leading-relaxed text-slate-600">
              Tabel stok otomatis mengikuti isi variants dan sizes di atas.
            </p>
          </div>
          <span class="inline-flex w-fit items-center rounded-full bg-white px-4 py-2 text-xs font-bold text-purple-700 ring-1 ring-purple-100">
            Otomatis
          </span>
        </div>

        <div class="mt-4 grid gap-3 rounded-xl bg-white p-3 md:grid-cols-[1fr_auto] md:items-end">
          <div>
            <label class="mb-1 block text-xs font-bold text-slate-600">Isi cepat stok semua baris</label>
            <input type="number" min="0" value="0" data-bulk-stock-value
                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-purple-200">
          </div>
          <button type="button" data-apply-bulk-stock
                  class="rounded-xl border border-purple-100 bg-purple-50 px-4 py-2 text-xs font-bold text-purple-700 hover:bg-purple-100">
            Terapkan ke Semua
          </button>
        </div>

        <p class="mt-2 hidden rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100" data-stock-action-status></p>

        <div class="mt-4 overflow-x-auto rounded-xl border border-purple-100 bg-white">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-3 py-3">Pilihan</th>
                <th class="px-3 py-3">Ukuran</th>
                <th class="px-3 py-3">Stok</th>
                <th class="px-3 py-3">Harga Opsi</th>
                <th class="px-3 py-3">Diskon Opsi</th>
                <th class="px-3 py-3">Gambar Opsi</th>
              </tr>
            </thead>
            <tbody data-stock-rows class="divide-y divide-slate-100">
              @foreach($initialStockRows as $index => $row)
                @php
                  $variantValue = trim((string) ($row['variant'] ?? ''));
                  $sizeValue = trim((string) ($row['size'] ?? ''));
                  $stockValue = max(0, (int) ($row['stock'] ?? 0));
                  $priceValue = isset($row['price']) && (int) $row['price'] > 0 ? (int) $row['price'] : '';
                  $discountPriceValue = isset($row['discount_price']) && (int) $row['discount_price'] > 0 ? (int) $row['discount_price'] : '';
                  $discountActiveValue = !empty($row['discount_active']);
                  $imagePathValue = trim((string) ($row['image_path'] ?? ''));
                @endphp
                <tr data-stock-row>
                  <td class="px-3 py-3">
                    <span class="font-semibold text-slate-800">{{ $variantValue !== '' ? $variantValue : 'Semua pilihan' }}</span>
                    <input type="hidden" name="option_stock_rows[{{ $index }}][variant]" value="{{ $variantValue }}" data-row-variant>
                  </td>
                  <td class="px-3 py-3">
                    <span class="font-semibold text-slate-800">{{ $sizeValue !== '' ? $sizeValue : 'Semua ukuran' }}</span>
                    <input type="hidden" name="option_stock_rows[{{ $index }}][size]" value="{{ $sizeValue }}" data-row-size>
                  </td>
                  <td class="px-3 py-3">
                    <input type="number" min="0" name="option_stock_rows[{{ $index }}][stock]" value="{{ $stockValue }}" data-row-stock
                           class="w-28 rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-purple-200">
                  </td>
                  <td class="px-3 py-3">
                    <input type="number" min="0" name="option_stock_rows[{{ $index }}][price]" value="{{ $priceValue }}" data-row-price
                           class="w-36 rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-purple-200" placeholder="Ikuti harga utama">
                  </td>
                  <td class="px-3 py-3">
                    <div class="flex min-w-48 items-center gap-2">
                      <label class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-100">
                        <input type="checkbox" name="option_stock_rows[{{ $index }}][discount_active]" value="1" data-row-discount-active class="rounded border-amber-200 text-amber-600 focus:ring-amber-200" @checked($discountActiveValue)>
                        Aktif
                      </label>
                      <input type="number" min="0" name="option_stock_rows[{{ $index }}][discount_price]" value="{{ $discountPriceValue }}" data-row-discount-price
                             class="w-32 rounded-xl border border-amber-100 px-3 py-2 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-amber-100" placeholder="Harga diskon">
                    </div>
                  </td>
                  <td class="px-3 py-3">
                    <div class="min-w-56 space-y-2">
                      <input type="hidden" name="option_stock_rows[{{ $index }}][image_path]" value="{{ $imagePathValue }}" data-row-image-path>
                      @if($imagePathValue !== '')
                        <img src="{{ $imagePathValue }}" alt="Gambar opsi" class="h-14 w-14 rounded-xl border border-slate-200 bg-white object-contain p-1" data-row-image-preview>
                      @else
                        <img src="" alt="Gambar opsi" class="hidden h-14 w-14 rounded-xl border border-slate-200 bg-white object-contain p-1" data-row-image-preview>
                      @endif
                      <input type="file" name="option_stock_rows[{{ $index }}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/*" data-row-image-file
                             class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 file:mr-2 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-1.5 file:font-semibold file:text-purple-700 hover:file:bg-purple-100">
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-3 rounded-xl bg-white p-3 text-xs leading-relaxed text-slate-600">
          <p>Jika variants atau sizes diubah, baris stok akan menyesuaikan otomatis. Gunakan <span class="font-bold text-slate-800">Terapkan ke Semua</span> hanya jika semua baris memakai jumlah stok yang sama.</p>
        </div>

        <textarea name="option_stocks_text" class="hidden" data-option-stocks-text>{{ old('option_stocks_text', $optionStocksText ?? '') }}</textarea>
      </div>

      <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
        <div class="text-sm font-bold text-emerald-800">Status produk otomatis mengikuti total stok</div>
        <p class="mt-1 text-xs leading-relaxed text-emerald-700">
          Jika total stok lebih dari 0, produk otomatis tersedia. Jika total stok 0, produk otomatis habis, turun ke bawah, dan tidak bisa dimasukkan keranjang.
        </p>
      </div>
    </div>
  </div>

  <div class="min-w-0 space-y-6">
    <div class="rounded-2xl bg-white p-6 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900">Media Produk</h2>

      <div class="mt-5 space-y-5">
        @if($product->primary_image)
          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">Primary Image Saat Ini</label>
            <img src="{{ $product->primary_image }}"
                 alt="{{ $product->name }}"
                 class="h-36 w-36 rounded-2xl border border-slate-200 object-cover bg-slate-50">
          </div>
        @endif

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Upload Primary Image</label>
          <div class="rounded-xl border border-slate-200 bg-white p-3" data-admin-file-upload>
            <input type="file"
                   id="primary-image-file"
                   name="primary_image_file"
                   accept=".jpg,.jpeg,.png,.webp,image/*"
                   class="sr-only"
                   data-admin-file-input>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
              <label for="primary-image-file"
                     class="inline-flex shrink-0 cursor-pointer items-center justify-center rounded-lg bg-purple-50 px-4 py-2 text-sm font-bold text-purple-700 hover:bg-purple-100">
                Pilih Gambar
              </label>
              <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-semibold text-slate-700" data-admin-file-name>Belum ada file dipilih</div>
                <p class="mt-1 text-xs text-slate-500">Upload gambar utama langsung dari komputer. Maksimal 3 MB.</p>
              </div>
            </div>
          </div>
        </div>

        @if($currentGalleryImages->count() > 0)
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <label class="mb-2 block text-sm font-semibold text-slate-700">Gallery Saat Ini</label>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
              @foreach($currentGalleryImages as $image)
                <label class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-2">
                  <img src="{{ $image->path }}"
                       alt="Gallery image"
                       class="h-24 w-full rounded-lg object-cover bg-slate-50">
                  <span class="mt-2 flex items-center gap-2 rounded-lg bg-rose-50 px-2 py-1 text-xs font-bold text-rose-700 ring-1 ring-rose-100">
                    <input type="checkbox"
                           name="remove_gallery_images[]"
                           value="{{ $image->path }}"
                           class="rounded border-rose-200 text-rose-600 focus:ring-rose-200">
                    Hapus
                  </span>
                </label>
              @endforeach
            </div>
            <p class="mt-3 text-xs leading-relaxed text-slate-500">Centang Hapus hanya pada gambar gallery yang ingin dihilangkan. Gambar utama tetap diatur dari Primary Image.</p>
          </div>
        @endif

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700">Upload Gallery Images</label>
          @if($isEdit)
            <label class="mb-3 flex items-start gap-3 rounded-2xl border border-amber-100 bg-amber-50/70 p-3">
              <input type="checkbox"
                     name="replace_gallery"
                     value="1"
                     class="mt-1 rounded border-amber-200 text-amber-600 focus:ring-amber-200">
              <span>
                <span class="block text-sm font-black text-slate-900">Ganti semua gallery lama dengan gambar baru</span>
                <span class="mt-1 block text-xs leading-relaxed text-slate-500">Jika dicentang, semua gallery lama akan dibersihkan lalu diganti dengan file gallery yang Anda upload. Jika tidak dicentang, upload baru hanya ditambahkan.</span>
              </span>
            </label>
          @endif
          <div class="rounded-xl border border-slate-200 bg-white p-3" data-admin-file-upload>
            <input type="file"
                   id="gallery-image-files"
                   name="gallery_image_files[]"
                   multiple
                   accept=".jpg,.jpeg,.png,.webp,image/*"
                   class="sr-only"
                   data-admin-file-input>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
              <label for="gallery-image-files"
                     class="inline-flex shrink-0 cursor-pointer items-center justify-center rounded-lg bg-purple-50 px-4 py-2 text-sm font-bold text-purple-700 hover:bg-purple-100">
                Pilih Gallery
              </label>
              <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-semibold text-slate-700" data-admin-file-name>Belum ada file dipilih</div>
                <p class="mt-1 text-xs text-slate-500">Bisa pilih banyak gambar sekaligus untuk slider dan thumbnails produk.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm">
      <div class="flex gap-3">
        <button type="submit"
                class="w-full rounded-xl bg-purple-600 px-4 py-3 font-semibold text-white hover:bg-purple-500">
          {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' }}
        </button>

        <a href="{{ route('admin.products.index') }}"
           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-center font-semibold text-slate-700 hover:bg-slate-50">
          Batal
        </a>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const parseLines = (value) => String(value || '')
    .split(/\r\n|\r|\n/)
    .map((line) => line.trim())
    .filter(Boolean);

  const normalize = (value) => {
    const cleaned = String(value || '').trim();
    return cleaned === '-' ? '' : cleaned;
  };

  const rowKey = (variant, size) => `${normalize(variant)}||${normalize(size)}`;
  const label = (value, emptyLabel) => normalize(value) || emptyLabel;

  const showStockStatus = (manager, message, tone = 'success') => {
    const status = manager.querySelector('[data-stock-action-status]');
    if (!status) return;

    status.textContent = message;
    status.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'ring-emerald-100', 'bg-amber-50', 'text-amber-700', 'ring-amber-100');

    if (tone === 'warning') {
      status.classList.add('bg-amber-50', 'text-amber-700', 'ring-amber-100');
    } else {
      status.classList.add('bg-emerald-50', 'text-emerald-700', 'ring-emerald-100');
    }

    window.clearTimeout(manager.__stockStatusTimer);
    manager.__stockStatusTimer = window.setTimeout(() => {
      status.classList.add('hidden');
    }, 2600);
  };

  const initStockManager = (manager) => {
    if (!manager || manager.dataset.stockManagerReady === '1') return;
    manager.dataset.stockManagerReady = '1';

    const root = manager.closest('form') || document;
    const variantBox = root.querySelector('[name="variants_text"]');
    const sizeBox = root.querySelector('[name="sizes_text"]');
    const tableBody = manager.querySelector('[data-stock-rows]');
    const totalStockInput = root.querySelector('[data-total-stock-value]') || root.querySelector('[name="stock"]');
    const totalStockDisplay = manager.querySelector('[data-total-stock-display]');
    const bulkInput = manager.querySelector('[data-bulk-stock-value]');
    const bulkButton = manager.querySelector('[data-apply-bulk-stock]');
    const hiddenText = manager.querySelector('[data-option-stocks-text]');
    let syncTimer = null;

    if (!tableBody) return;

    const currentRows = () => Array.from(tableBody.querySelectorAll('[data-stock-row]')).map((row) => {
      return {
        variant: normalize(row.querySelector('[data-row-variant]')?.value || ''),
        size: normalize(row.querySelector('[data-row-size]')?.value || ''),
        stock: Math.max(0, Number(row.querySelector('[data-row-stock]')?.value || 0) || 0),
        price: Math.max(0, Number(row.querySelector('[data-row-price]')?.value || 0) || 0),
        discountPrice: Math.max(0, Number(row.querySelector('[data-row-discount-price]')?.value || 0) || 0),
        discountActive: Boolean(row.querySelector('[data-row-discount-active]')?.checked),
        imagePath: String(row.querySelector('[data-row-image-path]')?.value || '').trim(),
      };
    });

    const currentStockMap = () => {
      const map = new Map();
      currentRows().forEach((row) => {
        map.set(rowKey(row.variant, row.size), row);
      });
      return map;
    };

    const updateTotalStock = () => {
      const total = currentRows().reduce((sum, row) => sum + Math.max(0, Number(row.stock) || 0), 0);

      if (totalStockInput) {
        totalStockInput.value = total;
      }

      if (totalStockDisplay) {
        totalStockDisplay.textContent = total;
      }

      if (hiddenText) {
        hiddenText.value = currentRows().map((row) => {
          return `${row.variant || '-'} | ${row.size || '-'} | ${Math.max(0, Number(row.stock) || 0)}`;
        }).join('\n');
      }
    };

    const renderRows = (rows) => {
      tableBody.innerHTML = '';

      rows.forEach((row, index) => {
        const variant = normalize(row.variant);
        const size = normalize(row.size);
        const stock = Math.max(0, Number(row.stock) || 0);
        const price = Math.max(0, Number(row.price ?? row.priceValue ?? 0) || 0);
        const discountPrice = Math.max(0, Number(row.discountPrice ?? row.discount_price ?? 0) || 0);
        const discountActive = Boolean(row.discountActive ?? row.discount_active ?? false);
        const imagePath = String(row.imagePath ?? row.image_path ?? '').trim();

        const tr = document.createElement('tr');
        tr.setAttribute('data-stock-row', '');
        tr.innerHTML = `
          <td class="px-3 py-3">
            <span class="font-semibold text-slate-800"></span>
            <input type="hidden" name="option_stock_rows[${index}][variant]" data-row-variant>
          </td>
          <td class="px-3 py-3">
            <span class="font-semibold text-slate-800"></span>
            <input type="hidden" name="option_stock_rows[${index}][size]" data-row-size>
          </td>
          <td class="px-3 py-3">
            <input type="number" min="0" name="option_stock_rows[${index}][stock]" data-row-stock
                   class="w-28 rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-purple-200">
          </td>
          <td class="px-3 py-3">
            <input type="number" min="0" name="option_stock_rows[${index}][price]" data-row-price
                   class="w-36 rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-purple-200" placeholder="Ikuti harga utama">
          </td>
          <td class="px-3 py-3">
            <div class="flex min-w-48 items-center gap-2">
              <label class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-100">
                <input type="checkbox" name="option_stock_rows[${index}][discount_active]" value="1" data-row-discount-active class="rounded border-amber-200 text-amber-600 focus:ring-amber-200">
                Aktif
              </label>
              <input type="number" min="0" name="option_stock_rows[${index}][discount_price]" data-row-discount-price
                     class="w-32 rounded-xl border border-amber-100 px-3 py-2 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-amber-100" placeholder="Harga diskon">
            </div>
          </td>
          <td class="px-3 py-3">
            <div class="min-w-56 space-y-2">
              <input type="hidden" name="option_stock_rows[${index}][image_path]" data-row-image-path>
              <img src="" alt="Gambar opsi" class="hidden h-14 w-14 rounded-xl border border-slate-200 bg-white object-contain p-1" data-row-image-preview>
              <input type="file" name="option_stock_rows[${index}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/*" data-row-image-file
                     class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 file:mr-2 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-1.5 file:font-semibold file:text-purple-700 hover:file:bg-purple-100">
            </div>
          </td>
        `;

        const labelEls = tr.querySelectorAll('span');
        const hiddenVariant = tr.querySelector('[data-row-variant]');
        const hiddenSize = tr.querySelector('[data-row-size]');
        const stockInput = tr.querySelector('[data-row-stock]');
        const priceInput = tr.querySelector('[data-row-price]');
        const discountActiveInput = tr.querySelector('[data-row-discount-active]');
        const discountPriceInput = tr.querySelector('[data-row-discount-price]');
        const imagePathInput = tr.querySelector('[data-row-image-path]');
        const imagePreview = tr.querySelector('[data-row-image-preview]');

        labelEls[0].textContent = label(variant, 'Semua pilihan');
        labelEls[1].textContent = label(size, 'Semua ukuran');
        hiddenVariant.value = variant;
        hiddenSize.value = size;
        stockInput.value = stock;
        priceInput.value = price > 0 ? price : '';
        discountActiveInput.checked = discountActive;
        discountPriceInput.value = discountPrice > 0 ? discountPrice : '';
        imagePathInput.value = imagePath;

        if (imagePath) {
          imagePreview.src = imagePath;
          imagePreview.classList.remove('hidden');
        }

        tableBody.appendChild(tr);
      });

      updateTotalStock();
    };

    const rowsSignature = (rows) => rows
      .map((row) => `${normalize(row.variant)}||${normalize(row.size)}||${Math.max(0, Number(row.stock) || 0)}`)
      .join('@@');

    const buildRowsFromOptions = (showFeedback = false) => {
      const beforeRows = currentRows();
      const variants = parseLines(variantBox?.value || '');
      const sizes = parseLines(sizeBox?.value || '');
      const variantList = variants.length ? variants : [''];
      const sizeList = sizes.length ? sizes : [''];
      const oldStock = currentStockMap();
      const rows = [];
      const totalCombinations = variantList.length * sizeList.length;
      const existingTotal = Number(totalStockInput?.value || 0) || 0;
      const bulkDefaultStock = Math.max(0, Number(bulkInput?.value || 0) || 0);

      variantList.forEach((rawVariant) => {
        sizeList.forEach((rawSize) => {
          const variant = normalize(rawVariant);
          const size = normalize(rawSize);
          const exactKey = rowKey(variant, size);
          const fallbackVariantKey = rowKey(variant, '');
          const fallbackSizeKey = rowKey('', size);
          const oldRow = oldStock.get(exactKey) || oldStock.get(fallbackVariantKey) || oldStock.get(fallbackSizeKey) || null;
          let qty = oldRow ? Math.max(0, Number(oldRow.stock) || 0) : 0;

          if (!oldRow && bulkDefaultStock > 0) {
            qty = bulkDefaultStock;
          } else if (!oldRow && totalCombinations === 1 && existingTotal > 0) {
            qty = existingTotal;
          }

          rows.push({
            variant,
            size,
            stock: qty,
            price: oldRow ? oldRow.price : 0,
            discountPrice: oldRow ? oldRow.discountPrice : 0,
            discountActive: oldRow ? oldRow.discountActive : false,
            imagePath: oldRow ? oldRow.imagePath : '',
          });
        });
      });

      renderRows(rows);

      const beforeSignature = rowsSignature(beforeRows);
      const afterSignature = rowsSignature(rows);
      if (showFeedback) {
        if (beforeSignature === afterSignature) {
          showStockStatus(manager, `Baris stok sudah sesuai. Total baris tetap ${rows.length}.`, 'warning');
        } else {
          showStockStatus(manager, `Baris stok berhasil diperbarui. Total baris: ${rows.length}.`);
        }
      }
    };

    const applyBulkStock = () => {
      const qty = Math.max(0, Number(bulkInput?.value || 0) || 0);
      const stockInputs = tableBody.querySelectorAll('[data-row-stock]');

      if (stockInputs.length === 0) {
        buildRowsFromOptions(false);
      }

      tableBody.querySelectorAll('[data-row-stock]').forEach((input) => {
        input.value = qty;
      });

      updateTotalStock();
      showStockStatus(manager, `Stok semua baris berhasil diubah menjadi ${qty}.`);
    };

    manager.__stockManagerBuildRows = buildRowsFromOptions;
    manager.__stockManagerApplyBulk = applyBulkStock;
    manager.__stockManagerUpdateTotal = updateTotalStock;

    const scheduleBuildRows = () => {
      window.clearTimeout(syncTimer);
      syncTimer = window.setTimeout(() => buildRowsFromOptions(false), 250);
    };

    [variantBox, sizeBox].forEach((box) => {
      if (!box) return;
      box.addEventListener('input', scheduleBuildRows);
      box.addEventListener('change', () => buildRowsFromOptions(false));
    });

    if (bulkButton) {
      bulkButton.addEventListener('click', function (event) {
        event.preventDefault();
        applyBulkStock();
      });
    }

    tableBody.addEventListener('input', function (event) {
      if (event.target && event.target.matches('[data-row-stock], [data-row-price], [data-row-discount-price], [data-row-discount-active]')) {
        updateTotalStock();
      }
    });

    tableBody.addEventListener('change', function (event) {
      if (event.target && event.target.matches('[data-row-stock], [data-row-price], [data-row-discount-price], [data-row-discount-active]')) {
        updateTotalStock();
      }
    });

    root.addEventListener('submit', function () {
      if (tableBody.querySelectorAll('[data-stock-row]').length === 0) {
        buildRowsFromOptions(false);
      }
      updateTotalStock();
    });

    updateTotalStock();
  };

  const initAllStockManagers = () => {
    document.querySelectorAll('[data-stock-manager]').forEach(initStockManager);
  };

  const initAdminFileUploads = () => {
    document.querySelectorAll('[data-admin-file-upload]').forEach((wrapper) => {
      const input = wrapper.querySelector('[data-admin-file-input]');
      const nameBox = wrapper.querySelector('[data-admin-file-name]');

      if (!input || !nameBox || input.dataset.fileUploadReady === '1') return;
      input.dataset.fileUploadReady = '1';

      input.addEventListener('change', function () {
        const files = Array.from(input.files || []);

        if (files.length === 0) {
          nameBox.textContent = 'Belum ada file dipilih';
          nameBox.classList.remove('text-purple-700');
          return;
        }

        if (files.length === 1) {
          nameBox.textContent = files[0].name;
        } else {
          nameBox.textContent = files.length + ' file dipilih';
        }

        nameBox.classList.add('text-purple-700');
      });
    });
  };

  const initAllProductFormScripts = () => {
    initAllStockManagers();
    initAdminFileUploads();
  };

  document.addEventListener('click', function (event) {
    const bulkTrigger = event.target.closest('[data-apply-bulk-stock]');
    if (bulkTrigger) {
      const manager = bulkTrigger.closest('[data-stock-manager]');
      if (manager && typeof manager.__stockManagerApplyBulk === 'function') {
        event.preventDefault();
        manager.__stockManagerApplyBulk();
      }
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllProductFormScripts, { once: true });
  } else {
    initAllProductFormScripts();
  }
})();
</script>
