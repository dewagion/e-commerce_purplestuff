@extends('layouts.app')

@section('content')
  <div
    class="mx-auto max-w-7xl px-4 sm:px-6 py-8 sm:py-10"
    x-data="{
      open:false, product:null, imgIndex:0, imagePreviewOpen:false,
      qty:1, size:'', variant:'',

      dragging:false, sx:0, dx:0, w:1,

      initSlider(el){
        if (!el) return;
        const updateWidth = () => {
          if (!el.isConnected) return;
          this.w = Math.max(1, el.getBoundingClientRect().width || 1);
        };
        updateWidth();
        window.addEventListener('resize', updateWidth, { passive:true });
      },
      down(x){ this.dragging = true; this.sx = x; this.dx = 0; },
      move(x){
        if(!this.dragging) return;
        this.dx = x - this.sx;
        const max = this.w * 0.35;
        if(this.dx > max) this.dx = max;
        if(this.dx < -max) this.dx = -max;
      },
      up(){
        if(!this.dragging) return;
        const threshold = this.w * 0.15;
        if(this.dx < -threshold) this.nextImg();
        else if(this.dx > threshold) this.prevImg();
        this.dragging = false;
        this.sx = 0;
        this.dx = 0;
      },
      nextImg(){
        const images = this.currentImages();
        if(!(images && images.length > 1)) return;
        this.imgIndex = (this.imgIndex + 1) % images.length;
      },
      prevImg(){
        const images = this.currentImages();
        if(!(images && images.length > 1)) return;
        this.imgIndex = (this.imgIndex - 1 + images.length) % images.length;
      },
      goImg(i){ this.imgIndex = i; },
openImagePreview(index = null){
  const images = this.currentImages();
  if (!images.length) return;
  if (index !== null && index !== undefined) this.imgIndex = index;
  this.imagePreviewOpen = true;
},
closeImagePreview(){ this.imagePreviewOpen = false; },
optionStockKey(v = null, sz = null){
  const variantValue = String(v ?? this.variant ?? '').trim();
  const sizeValue = String(sz ?? this.size ?? '').trim();
  return `${variantValue}||${sizeValue}`;
},
optionStockValue(v = null, sz = null){
  if (!this.product) return 0;
  const optionStocks = this.product.option_stocks || {};
  if (this.product.has_option_stock && Object.keys(optionStocks).length) {
    const direct = optionStocks[this.optionStockKey(v, sz)];
    const fallback = optionStocks['||'];
    return Number((direct ?? fallback ?? 0));
  }
  return Number(this.product.available_stock ?? this.product.stock ?? 0);
},

optionMapValue(mapName, v = null, sz = null){
  if (!this.product) return null;
  const source = this.product[mapName] || {};
  if (!source || !Object.keys(source).length) return null;
  return source[this.optionStockKey(v, sz)] ?? source[this.optionStockKey(v, '')] ?? source[this.optionStockKey('', sz)] ?? source['||'] ?? null;
},
optionPriceData(v = null, sz = null){
  if (!this.product?.option_pricing_active) return null;
  return this.optionMapValue('option_prices', v, sz);
},
currentPrice(){
  const data = this.optionPriceData(this.variant, this.size);
  return Number(data?.price ?? this.product?.default_price ?? this.product?.price ?? 0);
},
currentOriginalPrice(){
  const data = this.optionPriceData(this.variant, this.size);
  return Number(data?.original_price ?? this.product?.default_original_price ?? this.product?.original_price ?? 0);
},
currentHasDiscount(){
  const data = this.optionPriceData(this.variant, this.size);
  if (data) return Boolean(data.has_discount && data.original_price);
  return Boolean((this.product?.default_has_discount ?? this.product?.has_discount) && (this.product?.default_original_price ?? this.product?.original_price));
},
currentDiscountPercentage(){
  const data = this.optionPriceData(this.variant, this.size);
  return Number(data?.discount_percentage ?? this.product?.discount_percentage ?? 0);
},
currentOptionImage(){
  if (!this.product?.option_media_active) return null;
  return this.optionMapValue('option_media', this.variant, this.size);
},
baseImages(){
  if (!this.product) return [];
  if (Array.isArray(this.product.images) && this.product.images.length) return this.product.images.filter(Boolean);
  return this.product.image ? [this.product.image] : [];
},
currentImages(){
  const images = this.baseImages();
  const optionImage = this.currentOptionImage();
  if (!optionImage) return images;
  return [optionImage, ...images.filter((im) => im !== optionImage)];
},
refreshOptionState(){
  this.imgIndex = 0;
  this.syncQtyWithStock();
},
formatRupiah(value){
  return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
},
isUpcoming(){ return Boolean(this.product?.is_upcoming) || this.product?.product_status === 'upcoming'; },
currentStock(){ return this.isUpcoming() ? 0 : Math.max(0, this.optionStockValue(this.variant, this.size)); },
currentInStock(){ return !this.isUpcoming() && Boolean(this.product?.in_stock) && this.currentStock() > 0; },
syncQtyWithStock(){
  const max = this.currentStock();
  if (max <= 0) { this.qty = 0; return; }
  this.qty = Math.min(max, Math.max(1, Number(this.qty || 1)));
},

      openProduct(p){
        p.type = p.type ?? 'accessory';
        p.sizes = p.sizes ?? [];
        p.variants = p.variants ?? [];

        if (!Array.isArray(p.images)) {
          if (typeof p.images === 'string' && p.images.trim() !== '') p.images = [p.images];
          else p.images = (p.image ? [p.image] : []);
        } else {
          p.images = p.images.filter(Boolean);
          if (!p.images.length && p.image) p.images = [p.image];
        }

        p.sku = p.sku ?? '-';
        p.is_upcoming = Boolean(p.is_upcoming) || p.product_status === 'upcoming';
        p.stock = Number(p.stock ?? p.available_stock ?? 0);
        p.available_stock = p.is_upcoming ? 0 : Number(p.available_stock ?? (p.in_stock ? p.stock : 0));
        p.in_stock = !p.is_upcoming && Boolean(p.in_stock) && p.available_stock > 0;
        p.shipping = p.shipping ?? '1-3 Hari Kerja';
        p.desc = p.desc ?? 'Merch fandom K-Pop dengan kualitas premium.';
        p.category = p.category ?? 'Merch';
        p.artist = p.artist ?? null;
        p.artist_slug = p.artist_slug ?? null;

        this.product = p;
        this.imgIndex = 0;
        this.variant = (p.variants.length) ? p.variants[0] : '';
        this.size = (p.type === 'apparel' && p.sizes.length) ? p.sizes[0] : '';
        this.qty = p.in_stock ? 1 : 0;
        this.open = true;

        this.$nextTick(() => {
          if (this.$refs.modalBody) this.$refs.modalBody.scrollTop = 0;
        });
      }
    }"
    x-cloak
  >
    {{-- Header --}}
    <div class="ps-section-panel p-5 sm:p-7">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
          <h1 class="text-3xl font-extrabold text-slate-900">Wishlist</h1>
          <p class="mt-2 text-sm leading-6 text-slate-700 sm:text-base">
            Produk yang kamu simpan akan muncul di sini. Kamu bisa membuka detail produk atau menghapusnya kapan saja.
          </p>
        </div>

        <div class="shrink-0">
          <a href="{{ route('dashboard') }}"
             class="ps-btn-soft ps-btn-sm">
            ← Back to Dashboard
          </a>
        </div>
      </div>
    </div>

    {{-- Empty --}}
    @if(empty($products))
      <div class="mt-6 rounded-3xl border border-white/15 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/>
          </svg>
        </div>

        <h2 class="mt-4 text-xl font-bold text-slate-900">Wishlist masih kosong</h2>
        <p class="mt-2 text-sm text-slate-500">
          Tambahkan produk favoritmu ke wishlist agar lebih mudah ditemukan nanti.
        </p>

        <div class="mt-6">
          <a href="{{ route('dashboard') }}"
             class="ps-btn-soft ps-btn-sm">
            Jelajahi Produk
          </a>
        </div>
      </div>
    @else
      {{-- Grid Produk --}}
      <div class="mt-7 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 sm:gap-7">
        @foreach($products as $p)
          <div class="ps-product-card">
            <div class="ps-product-media">
              <img class="h-full w-full object-contain bg-white"
                   src="{{ $p['image'] ?? ($p['images'][0] ?? asset('images/placeholder.jpg')) }}"
                   alt="{{ $p['name'] ?? 'Product' }}">

              @include('partials.product-stock-overlay', ['product' => $p])

              <form method="POST"
                    action="{{ route('wishlist.toggle', $p['id']) }}"
                    class="absolute right-3 top-3">
                @csrf
                <button type="submit"
                        class="flex h-11 w-11 items-center justify-center rounded-full bg-white/95 shadow hover:bg-white"
                        aria-label="Hapus dari wishlist">
                  <svg xmlns="http://www.w3.org/2000/svg"
                       viewBox="0 0 24 24"
                       class="h-5 w-5 text-rose-500"
                       fill="currentColor"
                       stroke="currentColor"
                       stroke-width="1.8"
                       stroke-linecap="round"
                       stroke-linejoin="round">
                    <path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/>
                  </svg>
                </button>
              </form>
            </div>

            <div class="relative z-10 px-4 pb-5 pt-1 sm:px-5">
              @if(!empty($p['artist']))
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                  {{ $p['artist'] }}
                </div>
              @endif

              <div class="mt-1 line-clamp-2 min-h-[2.8rem] text-base font-extrabold leading-6 text-slate-950">
                {{ $p['name'] }}
              </div>

              @if(!empty($p['status_label']) || !empty($p['has_discount']))
                <div class="mt-2 flex flex-wrap gap-1.5">
                  @if(!empty($p['status_label']))
                    <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-sky-700">{{ $p['status_label'] }}</span>
                  @endif
                  @if(!empty($p['has_discount']))
                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-amber-700">Diskon{{ !empty($p['discount_percentage']) ? ' '.$p['discount_percentage'].'%' : '' }}</span>
                  @endif
                </div>
              @endif

              <div class="mt-2">
                @if(!empty($p['has_discount']) && !empty($p['original_price']))
                  <div class="flex flex-wrap items-baseline gap-2">
                    <span class="text-xl font-black text-purple-600">{{ !empty($p['has_price_range']) ? 'Mulai ' : '' }}{{ \App\Support\Money::rupiah($p['price']) }}</span>
                    <span class="text-xs font-bold text-slate-400 line-through">{{ \App\Support\Money::rupiah($p['original_price']) }}</span>
                  </div>
                @else
                  <div class="text-xl font-black text-purple-600">{{ !empty($p['has_price_range']) ? 'Mulai ' : '' }}{{ \App\Support\Money::rupiah($p['price']) }}</div>
                @endif
              </div>

              <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                <button type="button"
                        x-on:click="openProduct(@js($p))"
                        class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                  Lihat Produk
                </button>

                <form method="POST" action="{{ route('wishlist.toggle', $p['id']) }}" class="w-full">
                  @csrf
                  <button type="submit"
                          class="inline-flex w-full items-center justify-center rounded-2xl bg-rose-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-400">
                    Hapus
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- MODAL DETAIL PRODUK --}}
        <template x-teleport="body">
    <div
          x-show="open"
          x-cloak
          x-effect="
            if (open) {
              document.documentElement.classList.add('overflow-hidden');
              document.body.classList.add('overflow-hidden');
            } else {
              document.documentElement.classList.remove('overflow-hidden');
              document.body.classList.remove('overflow-hidden');
            }
          "
          class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
        >
      <div class="fixed inset-0 bg-black/75 backdrop-blur-[2px]" x-on:click="open=false; imagePreviewOpen=false"></div>

      <div class="relative z-10 w-[92vw] max-w-4xl max-h-[88vh] rounded-[1.8rem] bg-white shadow-2xl overflow-hidden">
        <button class="absolute right-4 top-4 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-white/85 text-slate-500 shadow-sm ring-1 ring-slate-200/80 backdrop-blur transition hover:bg-white hover:text-slate-900" x-on:click="open=false; imagePreviewOpen=false">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18"/>
            <path d="M6 6l12 12"/>
          </svg>
        </button>

        <div class="max-h-[88vh] overflow-y-auto overscroll-contain" x-ref="modalBody">
          <div class="grid grid-cols-1 md:grid-cols-2">
          <div class="p-6">
            <div class="rounded-[1.6rem] overflow-hidden bg-gradient-to-br from-white via-slate-50 to-purple-50/60 relative select-none shadow-inner ring-1 ring-purple-100/80"
                 x-init="initSlider($el)"
                 style="touch-action: pan-y;"
                 x-on:mousedown="down($event.clientX)"
                 x-on:mousemove="move($event.clientX)"
                 x-on:mouseup="up()"
                 x-on:mouseleave="up()"
                 x-on:touchstart="down($event.touches[0].clientX)"
                 x-on:touchmove="move($event.touches[0].clientX)"
                 x-on:touchend="up()">
              <div class="flex h-[360px] w-full"
                   :style="`transform: translateX(calc(-${imgIndex * 100}% + ${dx}px));`"
                   :class="dragging ? '' : 'transition-transform duration-300 ease-out'">
                <template x-for="(im, idx) in currentImages()" :key="im + idx">
                  <div class="w-full h-[360px] shrink-0">
                    <img class="w-full h-full object-contain cursor-zoom-in bg-white p-2" :src="im" :alt="product?.name" x-on:click.stop="openImagePreview(idx)">
                  </div>
                </template>
              </div>

  <button type="button"
    class="ps-btn-soft ps-btn-xs absolute bottom-3 right-3 shadow-md"
    x-on:click.stop="openImagePreview(imgIndex)"
  >Lihat detail</button>


              <template x-if="currentImages().length > 1">
                <div>
                  <button type="button"
                    class="absolute left-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/90 shadow flex items-center justify-center hover:bg-white"
                    x-on:click.stop="prevImg()"
                    aria-label="Prev">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M15 18l-6-6 6-6"/>
                    </svg>
                  </button>

                  <button type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/90 shadow flex items-center justify-center hover:bg-white"
                    x-on:click.stop="nextImg()"
                    aria-label="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9 18l6-6-6-6"/>
                    </svg>
                  </button>
                </div>
              </template>
            </div>

            <template x-if="currentImages().length > 1">
              <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
                <template x-for="(im, idx) in currentImages()" :key="im + idx">
                  <button type="button"
                    class="h-14 w-14 rounded-xl overflow-hidden border"
                    :class="imgIndex === idx ? 'border-purple-500' : 'border-transparent opacity-80 hover:opacity-100'"
                    x-on:click="goImg(idx)"
                    aria-label="Select image">
                    <img :src="im" class="h-full w-full object-contain bg-white p-1" alt="">
                  </button>
                </template>
              </div>
            </template>
          </div>

          <div class="p-6 space-y-5">
            <div class="rounded-[1.6rem] bg-gradient-to-br from-slate-50/95 via-white/95 to-purple-50/70 p-4 shadow-sm ring-1 ring-slate-100/90">
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <template x-if="product?.artist_slug">
                    <a class="block text-lg font-extrabold text-slate-950 hover:text-purple-700 transition"
                       :href="`{{ url('/artist') }}/${product.artist_slug}`">
                      <span x-text="product?.artist ?? ''"></span>
                    </a>
                  </template>

                  <div class="mt-1 line-clamp-2 text-base font-bold leading-6 text-slate-900" x-text="product?.name"></div>

                  <template x-if="product?.status_label || currentHasDiscount()">
                    <div class="mt-3 flex flex-wrap gap-2">
                      <template x-if="product?.status_label">
                        <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-[11px] font-black uppercase tracking-wide text-sky-700" x-text="product.status_label"></span>
                      </template>
                      <template x-if="currentHasDiscount()">
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-[11px] font-black uppercase tracking-wide text-amber-700" x-text="currentDiscountPercentage() ? ('Diskon ' + currentDiscountPercentage() + '%') : 'Diskon'"></span>
                      </template>
                    </div>
                  </template>

                  <template x-if="currentHasDiscount() && currentOriginalPrice()">
                    <div class="mt-3 text-sm font-bold text-slate-400 line-through"
                         x-text="formatRupiah(currentOriginalPrice())"></div>
                  </template>

                  <div class="mt-1 text-3xl font-black tracking-tight text-purple-600"
                       x-text="product ? formatRupiah(currentPrice()) : ''"></div>

                  <div class="mt-3 inline-flex rounded-full bg-purple-100/90 px-3 py-1 text-xs font-extrabold text-purple-700"
                       x-text="product?.category"></div>
                </div>

                <form method="POST" :action="product?.id ? '{{ url('/wishlist') }}/' + product.id + '/toggle' : '#'" class="shrink-0">
                  @csrf
                  <button type="submit"
                          class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/85 text-rose-500 shadow-sm ring-1 ring-slate-200/80 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:shadow-md"
                          aria-label="Hapus dari wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/>
                    </svg>
                  </button>
                </form>
              </div>
            </div>

            <div class="space-y-4">
              <template x-if="product && product.variants && product.variants.length">
                <div>
                  <div class="text-sm font-semibold">Pilihan</div>
                  <select
                    class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-purple-200"
                    x-model="variant">
                    <template x-for="v in product.variants" :key="v">
                      <option :value="v" x-text="v"></option>
                    </template>
                  </select>
                </div>
              </template>

              <template x-if="product && product.type === 'apparel'">
                <div>
                  <div class="text-sm font-semibold">Pilih Ukuran</div>
                  <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="s in product.sizes" :key="s">
                      <button type="button"
                        class="px-4 py-2 rounded-xl border font-semibold text-sm"
                        :class="size === s ? 'border-purple-600 text-purple-700' : 'border-gray-200 text-slate-700 hover:bg-gray-50'"
                        x-on:click="size = s; refreshOptionState()"
                        x-text="s"></button>
                    </template>
                  </div>
                </div>
              </template>

              <div>
                <div class="text-sm font-semibold">Jumlah</div>
                <div class="mt-2 flex items-center gap-3">
                  <button type="button"
                          class="h-10 w-10 rounded-xl border border-gray-200 hover:bg-gray-50 flex items-center justify-center leading-none"
                          x-on:click="qty = Math.max((currentInStock() ? 1 : 0), qty-1)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -translate-y-[0.5px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M5 12h14"/>
                    </svg>
                  </button>
                  <div class="w-10 text-center font-semibold" x-text="qty"></div>
                  <button type="button"
                          class="h-10 w-10 rounded-xl border border-gray-200 hover:bg-gray-50 flex items-center justify-center leading-none"
                          x-on:click="qty = Math.min(currentStock(), qty+1)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -translate-y-[0.5px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 5v14"/>
                      <path d="M5 12h14"/>
                    </svg>
                  </button>
                </div>
              </div>

              <form method="POST" action="{{ route('cart.add') }}" class="pt-2">
                @csrf
                <input type="hidden" name="id" :value="product?.id ?? (product?.name ?? '')">
                <input type="hidden" name="name" :value="product?.name">
                <input type="hidden" name="price" :value="currentPrice()">
                <input type="hidden" name="image" :value="product?.image">
                <input type="hidden" name="type" :value="product?.type">
                <input type="hidden" name="size" :value="size">
                <input type="hidden" name="qty" :value="qty">
                <input type="hidden" name="variant" :value="variant">

                <button type="submit"
                            :disabled="!currentInStock() || qty < 1"
                            :class="isUpcoming() ? 'w-full rounded-2xl bg-sky-100 text-sky-700 font-extrabold py-3.5 flex items-center justify-center gap-2 cursor-not-allowed shadow-inner ring-1 ring-sky-200/80' : ((!currentInStock() || qty < 1) ? 'w-full rounded-2xl bg-slate-200 text-slate-500 font-extrabold py-3.5 flex items-center justify-center gap-2 cursor-not-allowed shadow-inner' : 'w-full rounded-2xl bg-gradient-to-r from-purple-600 to-violet-600 text-white font-extrabold py-3.5 flex items-center justify-center gap-2 shadow-lg shadow-purple-500/25 transition hover:from-purple-700 hover:to-violet-700 hover:-translate-y-0.5 active:translate-y-0')">
                  <svg xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.8"
                      stroke-linecap="round"
                      stroke-linejoin="round">
                    <path d="M6 6h15l-1.5 9h-12z"></path>
                    <path d="M6 6 5 3H2"></path>
                    <circle cx="9" cy="20" r="1"></circle>
                    <circle cx="18" cy="20" r="1"></circle>
                  </svg>
                  Tambah ke Keranjang
                </button>

                @if(session('error'))
                  <div class="mt-3 text-sm text-red-600 font-semibold">{{ session('error') }}</div>
                @endif
              </form>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-3 text-sm">
                  <div class="rounded-2xl bg-emerald-50/80 px-4 py-3 ring-1 ring-emerald-100">
                    <div class="flex items-center justify-between gap-3">
                      <span class="inline-flex items-center gap-2 font-bold text-slate-600">
                        <span class="h-2.5 w-2.5 rounded-full" :class="isUpcoming() ? 'bg-sky-500' : (currentInStock() ? 'bg-emerald-500' : 'bg-rose-500')"></span>
                        Ketersediaan
                      </span>
                      <span class="text-right font-extrabold" :class="isUpcoming() ? 'text-sky-700' : (currentInStock() ? 'text-emerald-700' : 'text-rose-600')" x-text="isUpcoming() ? 'Upcoming' : (currentInStock() ? ('Stok tersedia (' + currentStock() + ' item)') : 'Stok habis')"></span>
                    </div>
                  </div>

                  <div class="rounded-2xl bg-sky-50/80 px-4 py-3 ring-1 ring-sky-100">
                    <div class="flex items-center justify-between gap-3">
                      <span class="inline-flex items-center gap-2 font-bold text-slate-600">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-100 text-sky-700">↗</span>
                        Pengiriman
                      </span>
                      <span class="text-right font-extrabold text-slate-800" x-text="product?.shipping ?? '1-3 Hari Kerja'"></span>
                    </div>
                  </div>

                  <template x-if="variant">
                    <div class="rounded-2xl bg-purple-50/80 px-4 py-3 ring-1 ring-purple-100">
                      <div class="flex items-center justify-between gap-3">
                        <span class="font-bold text-slate-600">Pilihan</span>
                        <span class="text-right font-extrabold text-slate-800" x-text="variant"></span>
                      </div>
                    </div>
                  </template>

                  <template x-if="size">
                    <div class="rounded-2xl bg-violet-50/80 px-4 py-3 ring-1 ring-violet-100">
                      <div class="flex items-center justify-between gap-3">
                        <span class="font-bold text-slate-600">Ukuran</span>
                        <span class="text-right font-extrabold text-slate-800" x-text="size"></span>
                      </div>
                    </div>
                  </template>
                </div>
          </div>
        </div>

        <template x-if="product?.desc">
          <section class="mx-6 mb-6 rounded-2xl bg-slate-50/95 p-5 shadow-inner ring-1 ring-slate-100 sm:mx-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <h3 class="text-base font-extrabold text-slate-950">Deskripsi Produk</h3>
              <span class="rounded-full bg-purple-100 px-3 py-1 text-[11px] font-bold text-purple-700">Detail Produk</span>
            </div>
            <div class="mt-3 max-h-[280px] overflow-y-auto pr-2 text-sm leading-7 text-slate-700 whitespace-pre-line" x-text="product?.desc"></div>
          </section>
        </template>
      </div>
      </div>
    </div>

                </template>

        {{-- LIGHTBOX DETAIL FOTO PRODUK --}}
        <template x-teleport="body">
          <div
            x-show="imagePreviewOpen"
            x-transition.opacity
            class="fixed inset-0 z-[2147483647] flex flex-col bg-black/95 px-4 py-5 sm:px-6 sm:py-6"
            x-on:click.self="closeImagePreview()"
            x-on:keydown.escape.window="closeImagePreview()"
            x-on:wheel.stop
            x-on:touchmove.stop
            style="display: none;"
          >
            <button type="button"
              class="absolute right-4 top-4 z-30 flex h-11 w-11 items-center justify-center rounded-full bg-white/95 text-slate-800 shadow-lg ring-1 ring-white/40 transition hover:scale-105 hover:bg-white active:scale-95"
              x-on:click="closeImagePreview()"
              aria-label="Tutup detail foto">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
            </button>

            <div class="mx-auto flex h-full w-full max-w-6xl min-h-0 flex-col gap-4">
              <div class="shrink-0 pr-14 text-white">
                <div class="text-xs font-bold uppercase tracking-[.18em] text-white/60" x-text="product?.artist ?? 'Detail Foto Produk'"></div>
                <div class="mt-1 line-clamp-2 text-base font-extrabold sm:text-lg" x-text="product?.name ?? ''"></div>
              </div>

              <div class="relative flex min-h-0 flex-1 items-center justify-center overflow-hidden rounded-[1.75rem] bg-white/8 p-3 shadow-2xl ring-1 ring-white/10 sm:p-5">
                <img
                  class="max-h-full max-w-full object-contain"
                  :src="currentImages()[imgIndex] || product?.image"
                  :alt="product?.name ?? 'Detail foto produk'"
                >

                <template x-if="currentImages().length > 1">
                  <div>
                    <button type="button"
                      class="absolute left-3 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/92 text-slate-900 shadow-lg transition hover:scale-105 hover:bg-white active:scale-95"
                      x-on:click.stop="prevImg()"
                      aria-label="Foto sebelumnya">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>

                    <button type="button"
                      class="absolute right-3 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/92 text-slate-900 shadow-lg transition hover:scale-105 hover:bg-white active:scale-95"
                      x-on:click.stop="nextImg()"
                      aria-label="Foto berikutnya">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                  </div>
                </template>
              </div>

              <template x-if="currentImages().length > 1">
                <div class="mx-auto flex max-w-full shrink-0 gap-2 overflow-x-auto rounded-2xl bg-white/10 p-2 ring-1 ring-white/10">
                  <template x-for="(im, idx) in currentImages()" :key="'preview-' + im + idx">
                    <button type="button"
                      class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border bg-white transition"
                      :class="imgIndex === idx ? 'border-purple-400 ring-2 ring-purple-300' : 'border-white/30 opacity-75 hover:opacity-100'"
                      x-on:click="goImg(idx)"
                      aria-label="Pilih foto detail">
                      <img :src="im" class="h-full w-full object-contain p-1" alt="">
                    </button>
                  </template>
                </div>
              </template>
            </div>
          </div>
        </template>

    </div>
  </div>
@endsection