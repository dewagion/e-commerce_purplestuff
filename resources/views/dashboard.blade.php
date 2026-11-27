@extends('layouts.app')

@section('content')
@php
  $normalizeDashboardBanner = fn ($banner) => [
      'badge' => $banner['badge'] ?? '',
      'title' => $banner['title'] ?? '',
      'desc' => $banner['subtitle'] ?? '',
      'img' => $banner['image'] ?? '',
      'ctaText' => $banner['ctaText'] ?? 'Shop Now',
      'ctaLink' => $banner['ctaLink'] ?? route('products.index'),
  ];

  $dashboardSlides = collect($heroBanners ?? [])
      ->map($normalizeDashboardBanner)
      ->filter(fn ($banner) => !empty($banner['img']) && !empty($banner['title']))
      ->values()
      ->all();

  $dashboardCategoryPromos = collect($categoryPromoBanners ?? [])
      ->map($normalizeDashboardBanner)
      ->filter(fn ($banner) => !empty($banner['img']) && !empty($banner['title']))
      ->values()
      ->all();

  $dashboardSinglePromos = collect($singlePromoBanners ?? [])
      ->map($normalizeDashboardBanner)
      ->filter(fn ($banner) => !empty($banner['img']) && !empty($banner['title']))
      ->values()
      ->all();

  $dashboardBestProducts = collect($featuredProducts ?? [])
      ->filter(function ($product) {
          $stock = (int) ($product['available_stock'] ?? $product['stock'] ?? 0);
          return empty($product['is_upcoming']) && !empty($product['in_stock']) && $stock > 0;
      })
      ->sortByDesc(function ($product) {
          $discountScore = !empty($product['has_discount']) ? 100000 : 0;
          $stockScore = min(999, (int) ($product['available_stock'] ?? $product['stock'] ?? 0));
          $priceScore = max(0, 99999999 - (int) ($product['price'] ?? 0));

          return $discountScore + $stockScore + $priceScore;
      })
      ->take(8)
      ->values()
      ->all();
@endphp

<style>
  .ps-dashboard-glass {
    background: rgba(255, 255, 255, 0.62);
    border: 1px solid rgba(255, 255, 255, 0.76);
    box-shadow: 0 18px 44px rgba(15, 23, 42, 0.10);
    backdrop-filter: blur(16px);
  }

  .ps-dashboard-title-accent {
    position: relative;
    display: inline-flex;
    width: fit-content;
    isolation: isolate;
  }

  .ps-dashboard-title-accent::before {
    content: "";
    position: absolute;
    left: -0.45rem;
    right: -0.45rem;
    bottom: 0.12rem;
    height: 0.62rem;
    border-radius: 999px;
    z-index: -1;
    opacity: 0.78;
    background: linear-gradient(90deg, rgba(221, 214, 254, 0.84), rgba(186, 230, 253, 0.60));
  }

  .ps-artist-aurora {
    position: relative;
    overflow: hidden;
    border-radius: 2rem;
    background: rgba(255, 255, 255, 0.32);
    border: 1px solid rgba(255, 255, 255, 0.68);
    box-shadow: 0 22px 58px rgba(15, 23, 42, 0.12);
    backdrop-filter: blur(18px);
  }

  .ps-artist-aurora::before {
    content: "";
    position: absolute;
    inset: -18%;
    opacity: 0.82;
    pointer-events: none;
    background:
      radial-gradient(circle at 16% 24%, rgba(216, 180, 254, 0.56), transparent 28%),
      radial-gradient(circle at 48% 12%, rgba(34, 211, 238, 0.45), transparent 30%),
      radial-gradient(circle at 82% 18%, rgba(191, 219, 254, 0.58), transparent 32%),
      radial-gradient(circle at 15% 86%, rgba(125, 211, 252, 0.52), transparent 30%),
      linear-gradient(110deg, rgba(255, 255, 255, 0.34), rgba(255, 255, 255, 0.14));
    filter: blur(4px);
  }

  .ps-artist-aurora::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
      linear-gradient(90deg, rgba(255, 255, 255, 0.38), transparent 34%, rgba(255, 255, 255, 0.22)),
      radial-gradient(circle at 100% 20%, rgba(15, 23, 42, 0.16), transparent 28%);
  }

  .ps-artist-aurora[data-artist-tone="bts"]::before {
    background:
      radial-gradient(circle at 14% 18%, rgba(216, 180, 254, 0.66), transparent 28%),
      radial-gradient(circle at 48% 12%, rgba(45, 212, 191, 0.50), transparent 31%),
      radial-gradient(circle at 84% 26%, rgba(147, 197, 253, 0.62), transparent 34%),
      radial-gradient(circle at 12% 88%, rgba(30, 64, 175, 0.36), transparent 26%),
      linear-gradient(110deg, rgba(255, 255, 255, 0.34), rgba(255, 255, 255, 0.12));
  }

  .ps-artist-aurora[data-artist-tone="illit"]::before {
    background:
      radial-gradient(circle at 16% 20%, rgba(207, 250, 254, 0.72), transparent 30%),
      radial-gradient(circle at 46% 16%, rgba(186, 230, 253, 0.60), transparent 31%),
      radial-gradient(circle at 84% 28%, rgba(221, 214, 254, 0.54), transparent 34%),
      radial-gradient(circle at 14% 86%, rgba(125, 211, 252, 0.42), transparent 28%),
      linear-gradient(110deg, rgba(255, 255, 255, 0.38), rgba(255, 255, 255, 0.14));
  }

  .ps-artist-aurora[data-artist-tone="the-rose"]::before {
    background:
      radial-gradient(circle at 18% 20%, rgba(251, 207, 232, 0.70), transparent 30%),
      radial-gradient(circle at 48% 14%, rgba(233, 213, 255, 0.58), transparent 31%),
      radial-gradient(circle at 84% 26%, rgba(186, 230, 253, 0.48), transparent 34%),
      radial-gradient(circle at 12% 88%, rgba(244, 114, 182, 0.36), transparent 27%),
      linear-gradient(110deg, rgba(255, 255, 255, 0.36), rgba(255, 255, 255, 0.13));
  }

  .ps-artist-aurora[data-artist-tone="treasure"]::before {
    background:
      radial-gradient(circle at 16% 22%, rgba(191, 219, 254, 0.70), transparent 30%),
      radial-gradient(circle at 48% 14%, rgba(125, 211, 252, 0.54), transparent 31%),
      radial-gradient(circle at 84% 28%, rgba(221, 214, 254, 0.46), transparent 34%),
      radial-gradient(circle at 12% 86%, rgba(37, 99, 235, 0.34), transparent 27%),
      linear-gradient(110deg, rgba(255, 255, 255, 0.36), rgba(255, 255, 255, 0.14));
  }

  .ps-artist-aurora[data-artist-tone="txt"]::before {
    background:
      radial-gradient(circle at 16% 22%, rgba(209, 250, 229, 0.72), transparent 30%),
      radial-gradient(circle at 48% 14%, rgba(186, 230, 253, 0.58), transparent 31%),
      radial-gradient(circle at 84% 28%, rgba(191, 219, 254, 0.50), transparent 34%),
      radial-gradient(circle at 12% 86%, rgba(52, 211, 153, 0.34), transparent 27%),
      linear-gradient(110deg, rgba(255, 255, 255, 0.36), rgba(255, 255, 255, 0.14));
  }

  .ps-artist-pill-title {
    position: relative;
    isolation: isolate;
    border-radius: 999px;
    padding: 0.28rem 0.72rem;
  }

  .ps-artist-pill-title::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(233, 213, 255, 0.86), rgba(186, 230, 253, 0.68));
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
  }

  .ps-product-card {
    transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
  }

  .ps-product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 42px rgba(15, 23, 42, 0.13);
  }

  .ps-product-card img {
    transition: transform 260ms ease;
  }

  .ps-product-card:hover img {
    transform: scale(1.025);
  }

  @media (max-width: 640px) {
    .ps-artist-aurora {
      border-radius: 1.5rem;
    }

    .ps-artist-pill-title {
      padding: 0.22rem 0.62rem;
    }
  }
</style>

<div class="min-h-screen bg-transparent">

  {{--HERO SLIDER LEBAR--}}
  @if(count($dashboardSlides) > 0)
  <section class="bg-transparent">
    <div
      class="relative w-full overflow-hidden shadow-[0_18px_44px_rgba(15,23,42,0.18)]"
      style="touch-action: pan-y;"
      x-data="{
        i: 0,
        slides: @js($dashboardSlides),
        timer: null,
        w: 1,
        sx: 0,
        dx: 0,
        dragging: false,

        step() { return 100 / this.slides.length; },

        init(el) {
          if (el) {
            const updateWidth = () => {
              if (!el.isConnected) return;
              this.w = Math.max(1, el.getBoundingClientRect().width || 1);
            };
            updateWidth();
            window.addEventListener('resize', updateWidth, { passive:true });
          }
          this.start();
        },

        start() { this.stop(); if (this.slides.length > 1) this.timer = setInterval(() => this.next(), 5200); },
        stop() { if (this.timer) clearInterval(this.timer); this.timer = null; },

        next() { this.i = (this.i + 1) % this.slides.length; this.dx = 0; },
        prev() { this.i = (this.i - 1 + this.slides.length) % this.slides.length; this.dx = 0; },
        go(idx) { this.i = idx; this.dx = 0; },

        down(x) { this.stop(); this.dragging = true; this.sx = x; this.dx = 0; },
        move(x) {
          if (!this.dragging) return;
          this.dx = x - this.sx;
          const max = this.w * 0.35;
          if (this.dx > max) this.dx = max;
          if (this.dx < -max) this.dx = -max;
        },
        up() {
          if (!this.dragging) return;
          const threshold = this.w * 0.15;
          if (this.dx < -threshold) this.next();
          else if (this.dx > threshold) this.prev();

          this.dragging = false;
          this.sx = 0;
          this.dx = 0;
          this.start();
        }
      }"
      x-init="init($el)"
      x-on:mouseenter="stop()"
      x-on:mouseleave="start()"
      x-on:mousedown="down($event.clientX)"
      x-on:mousemove="move($event.clientX)"
      x-on:mouseup="up()"
      x-on:mouseleave="up()"
      x-on:touchstart="down($event.touches[0].clientX)"
      x-on:touchmove="move($event.touches[0].clientX)"
      x-on:touchend="up()"
    >
      <div class="relative h-[360px] sm:h-[480px] lg:h-[560px] select-none overflow-hidden bg-slate-950">
        <div
          class="flex h-full"
          :style="`width:${slides.length * 100}%; transform: translateX(calc(-${i * step()}% + ${dx}px));`"
          :class="dragging ? '' : 'transition-transform duration-500 ease-out'"
        >
          <template x-for="(s, idx) in slides" :key="idx">
            <div class="relative h-full shrink-0" :style="`width:${step()}%`">
              <div class="h-full w-full bg-cover bg-center" :style="`background-image:url('${s.img}')`"></div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-black/5 to-transparent"></div>
              <div class="absolute inset-x-0 bottom-0 flex justify-center px-4 pb-9 sm:pb-12">
                <div class="max-w-4xl text-center text-white">
                  <template x-if="s.badge">
                    <div class="mx-auto inline-flex items-center gap-2 rounded-full bg-white/18 px-4 py-1.5 text-[11px] font-black uppercase tracking-[.18em] shadow-sm backdrop-blur" x-text="s.badge"></div>
                  </template>
                  <h1 class="mt-4 text-3xl font-black tracking-tight drop-shadow sm:text-5xl lg:text-6xl" x-text="s.title"></h1>
                  <template x-if="s.desc">
                    <p class="mx-auto mt-3 max-w-2xl text-sm font-medium leading-relaxed text-white/90 sm:text-base" x-text="s.desc"></p>
                  </template>
                  <div class="mt-6 flex justify-center">
                    <a :href="s.ctaLink || '{{ route('products.index') }}'"
                       class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-black uppercase tracking-wide text-slate-950 shadow-[0_18px_40px_rgba(0,0,0,.22)] transition hover:-translate-y-0.5 hover:bg-purple-50 hover:text-purple-700">
                      <span x-text="s.ctaText || 'Shop Now'"></span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <button type="button"
              class="absolute left-4 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-slate-950 shadow-lg backdrop-blur transition hover:bg-white sm:flex"
              aria-label="Previous"
              x-on:click="prev()"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></button>

      <button type="button"
              class="absolute right-4 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-slate-950 shadow-lg backdrop-blur transition hover:bg-white sm:flex"
              aria-label="Next"
              x-on:click="next()"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></button>

      <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
        <template x-for="(_, idx) in slides" :key="idx">
          <button type="button"
                  class="h-2.5 rounded-full transition-all"
                  :class="i === idx ? 'w-8 bg-white' : 'w-2.5 bg-white/45'"
                  x-on:click="go(idx)"
                  aria-label="Go to slide"></button>
        </template>
      </div>
    </div>
  </section>
  @endif

  {{-- SHOP BY ARTIST --}}
  <section id="artist" class="mt-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
      <div class="ps-section-panel ps-dashboard-glass relative overflow-hidden p-5 sm:p-7">
        <div class="pointer-events-none absolute left-1/2 top-4 h-20 w-[82%] -translate-x-1/2 rounded-full bg-gradient-to-r from-purple-100/55 via-cyan-100/60 to-blue-100/45 blur-2xl"></div>
        <div class="relative z-10 flex flex-col items-center text-center">
          <h2 class="ps-dashboard-title-accent text-2xl font-extrabold text-slate-950">Shop by Artist</h2>
        </div>

        <div class="relative z-10 mt-7 flex flex-wrap items-center justify-center gap-3 sm:gap-4">
          @foreach($artists as $a)
            <a href="{{ route('artist.show', ['artist' => $a['slug']]) }}"
               class="ps-chip px-5 py-3 text-sm font-extrabold text-slate-800 hover:text-purple-700">
              {{ $a['name'] }}
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- CATEGORY --}}
  <section id="category" class="mt-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
      <div class="ps-section-panel ps-dashboard-glass relative overflow-hidden p-5 sm:p-7">
        <div class="pointer-events-none absolute left-1/2 top-5 h-20 w-[84%] -translate-x-1/2 rounded-full bg-gradient-to-r from-cyan-100/58 via-white/50 to-purple-100/55 blur-2xl"></div>
        <div class="relative z-10 flex flex-col items-center text-center">
          <h2 class="ps-dashboard-title-accent text-2xl font-extrabold text-slate-950">Shop by Category</h2>
        </div>

        <div class="relative z-10 mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8">
          @foreach($categories as $cat)
            @php
              $bgClass = $cat['bg'] ?? 'bg-purple-100';
              $isGradient = str_contains($bgClass, 'from-') || str_contains($bgClass, 'to-');
            @endphp

            <a href="{{ route('dashboard', ['category' => $cat['slug']]) }}#products"
               class="ps-category-card group flex min-h-[150px] flex-col items-center justify-center gap-3 px-3 py-5 text-center {{ $cat['active'] ? 'ring-2 ring-purple-400' : '' }}">
              <div class="flex h-20 w-20 items-center justify-center rounded-full {{ $isGradient ? 'bg-gradient-to-r text-white' : 'text-slate-700' }} {{ $bgClass }} shadow-inner transition group-hover:scale-105">
                @include('partials.category-icon', [
                  'icon' => $cat['icon'] ?? 'star',
                  'class' => 'h-8 w-8'
                ])
              </div>

              <div class="text-sm font-extrabold {{ $cat['active'] ? 'text-purple-600' : 'text-slate-800' }}">
                {{ $cat['name'] }}
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- PROMO BANNER TAMBAHAN --}}
  @if(!empty($dashboardCategoryPromos) || !empty($dashboardSinglePromos))
    <section id="promo-banners" class="mt-8">
      <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6">
        @if(!empty($dashboardCategoryPromos))
          <div class="relative">
            <div class="pointer-events-none absolute inset-x-6 top-12 hidden h-[68%] rounded-[2rem] bg-gradient-to-r from-purple-200/18 via-cyan-200/24 to-blue-200/18 blur-2xl md:block"></div>
            <div class="relative grid gap-2 sm:gap-3 md:grid-cols-3">
            @foreach($dashboardCategoryPromos as $promo)
              <a href="{{ $promo['ctaLink'] }}"
                 class="group block text-center transition duration-300 hover:-translate-y-1">
                <h3 class="mb-3 line-clamp-2 min-h-[2.35rem] px-1 text-center text-xl font-black uppercase leading-tight tracking-wide text-slate-950 sm:text-2xl">
                  {{ $promo['title'] }}
                </h3>

                <div class="relative mx-auto aspect-[3/4] w-full overflow-hidden rounded-[0.9rem] bg-slate-100 shadow-[0_16px_34px_rgba(15,23,42,0.16)] ring-1 ring-slate-900/10 transition duration-300 group-hover:shadow-[0_20px_42px_rgba(126,34,206,0.20)]">
                  <img src="{{ $promo['img'] }}"
                       alt="{{ $promo['title'] }}"
                       class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
                </div>

                <div class="mt-3 flex justify-center">
                  <span class="inline-flex items-center justify-center rounded-full bg-slate-950 px-5 py-2.5 text-xs font-extrabold text-white shadow-sm transition group-hover:bg-purple-700 sm:text-sm">
                    {{ $promo['ctaText'] ?: 'Shop Now' }}
                  </span>
                </div>
              </a>
            @endforeach
            </div>
          </div>
        @endif

        @if(!empty($dashboardSinglePromos))
          <div class="space-y-6">
            @foreach($dashboardSinglePromos as $promo)
              <a href="{{ $promo['ctaLink'] }}"
                 class="group grid overflow-hidden rounded-[2rem] bg-white/80 shadow-[0_20px_50px_rgba(15,23,42,0.14)] ring-1 ring-white/80 backdrop-blur transition duration-300 hover:-translate-y-1 hover:shadow-[0_26px_62px_rgba(126,34,206,0.20)] lg:grid-cols-[0.92fr_1.08fr]">
                <div class="relative min-h-[320px] overflow-hidden bg-slate-950 lg:min-h-[430px]">
                  <img src="{{ $promo['img'] }}"
                       alt="{{ $promo['title'] }}"
                       class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-950/45 via-transparent to-white/5"></div>
                </div>

                <div class="relative flex min-h-[320px] items-center bg-gradient-to-br from-white/92 via-purple-50/72 to-cyan-50/80 p-7 sm:p-10 lg:min-h-[430px]">
                  <div class="max-w-xl">
                    @if(!empty($promo['badge']))
                      <div class="inline-flex rounded-full bg-purple-100 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-purple-700">
                        {{ $promo['badge'] }}
                      </div>
                    @endif
                    <h3 class="mt-5 text-3xl font-black tracking-tight text-slate-950 sm:text-5xl">
                      {{ $promo['title'] }}
                    </h3>
                    @if(!empty($promo['desc']))
                      <p class="mt-4 text-sm font-medium leading-7 text-slate-600 sm:text-base">
                        {{ $promo['desc'] }}
                      </p>
                    @endif
                    <span class="mt-7 inline-flex items-center justify-center rounded-full bg-purple-600 px-7 py-3 text-sm font-extrabold text-white shadow-lg shadow-purple-600/20 transition group-hover:bg-slate-950">
                      {{ $promo['ctaText'] ?: 'Shop Now' }}
                    </span>
                  </div>
                </div>
              </a>
            @endforeach
          </div>
        @endif
      </div>
    </section>
  @endif

  {{-- Wrapper Alpine (modal state) --}}
  <div
    x-data="{
          showAll:false,
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
  this.dragging = false; this.sx = 0; this.dx = 0;
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
    const variantFallback = optionStocks[this.optionStockKey(v, '')];
    const sizeFallback = optionStocks[this.optionStockKey('', sz)];
    const fallback = optionStocks['||'];
    return Number((direct ?? variantFallback ?? sizeFallback ?? fallback ?? 0));
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
selectionStockFor(p, v = '', sz = ''){
  if (!p) return 0;
  if (p?.is_upcoming || p?.product_status === 'upcoming') return 0;
  const optionStocks = p.option_stocks || {};
  const hasOptionStock = Boolean(p.has_option_stock) && Object.keys(optionStocks).length > 0;

  if (hasOptionStock) {
    const key = `${String(v ?? '').trim()}||${String(sz ?? '').trim()}`;
    const variantKey = `${String(v ?? '').trim()}||`;
    const sizeKey = `||${String(sz ?? '').trim()}`;
    return Number(optionStocks[key] ?? optionStocks[variantKey] ?? optionStocks[sizeKey] ?? optionStocks['||'] ?? 0);
  }

  return Number(p.available_stock ?? p.stock ?? 0);
},
firstAvailableSelection(p){
  const type = p?.type ?? 'accessory';
  const variantCandidates = Array.isArray(p?.variants) && p.variants.length ? p.variants : [''];
  const sizeCandidates = (type === 'apparel' && Array.isArray(p?.sizes) && p.sizes.length) ? p.sizes : [''];
  const preferredVariant = String(p?.default_variant ?? '').trim();
  const preferredSize = String(p?.default_size ?? '').trim();

  if (preferredVariant !== '' || preferredSize !== '') {
    const preferredStock = this.selectionStockFor(p, preferredVariant, preferredSize);
    if (preferredStock > 0) {
      return { variant: preferredVariant, size: preferredSize, stock: preferredStock };
    }
  }

  for (const v of variantCandidates) {
    for (const s of sizeCandidates) {
      const stock = this.selectionStockFor(p, v, s);
      if (stock > 0) {
        return { variant: v, size: s, stock };
      }
    }
  }

  const fallbackVariant = preferredVariant || variantCandidates[0] || '';
  const fallbackSize = preferredSize || sizeCandidates[0] || '';

  return {
    variant: fallbackVariant,
    size: fallbackSize,
    stock: this.selectionStockFor(p, fallbackVariant, fallbackSize),
  };
},
          quickAdd(p){
            if (p?.is_upcoming || p?.product_status === 'upcoming') return;
            p.is_upcoming = Boolean(p.is_upcoming) || p.product_status === 'upcoming';
            p.stock = Number(p.stock ?? p.available_stock ?? 0);
            p.available_stock = p.is_upcoming ? 0 : Number(p.available_stock ?? (p.in_stock ? p.stock : 0));
            p.in_stock = !p.is_upcoming && Boolean(p.in_stock) && p.available_stock > 0;
            if (!p.in_stock) return;
            p.type = p.type ?? 'accessory';
            p.sizes = p.sizes ?? [];
            p.variants = p.variants ?? [];
            p.option_stocks = p.option_stocks ?? {};
            p.option_prices = p.option_prices ?? {};
            p.option_media = p.option_media ?? {};
            p.has_option_stock = Boolean(p.has_option_stock) && Object.keys(p.option_stocks).length > 0;

            const selectedOption = this.firstAvailableSelection(p);
            const size = selectedOption.size;
            const variant = selectedOption.variant;
            const quickStock = selectedOption.stock;

            if (quickStock <= 0) return;

            this.$refs.q_id.value = p.id ?? (p.name ?? '');
            this.$refs.q_name.value = p.name ?? '';
            this.$refs.q_price.value = p.price ?? 0;
            this.$refs.q_image.value = p.image ?? '';
            this.$refs.q_type.value = p.type ?? 'accessory';
            this.$refs.q_size.value = size;
            this.$refs.q_variant.value = variant;
            this.$refs.q_qty.value = 1;

            this.$refs.quickAddForm.submit();
          },

          openProduct(p, focusBuy=false){
            p.type = p.type ?? 'accessory';
            p.sizes = p.sizes ?? [];
            p.variants = p.variants ?? [];
            p.option_stocks = p.option_stocks ?? {};
            p.option_prices = p.option_prices ?? {};
            p.option_media = p.option_media ?? {};
            p.has_option_stock = Boolean(p.has_option_stock) && Object.keys(p.option_stocks).length > 0;

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

            const selectedOption = this.firstAvailableSelection(p);
            this.variant = selectedOption.variant;
            this.size = selectedOption.size;
            this.qty = selectedOption.stock > 0 ? 1 : 0;
            this.refreshOptionState();

            this.open = true;

            this.$nextTick(() => {
              if (this.$refs.modalBody) this.$refs.modalBody.scrollTop = 0;
              if (focusBuy && this.$refs.buySection) {
                this.$refs.buySection.scrollIntoView({ behavior:'smooth', block:'start' });
              }
            });
          }
        }"
    x-cloak
  >

    <form x-ref="quickAddForm" method="POST" action="{{ route('cart.add') }}" class="hidden">
      @csrf
      <input x-ref="q_id" type="hidden" name="id">
      <input x-ref="q_name" type="hidden" name="name">
      <input x-ref="q_price" type="hidden" name="price">
      <input x-ref="q_image" type="hidden" name="image">
      <input x-ref="q_type" type="hidden" name="type">
      <input x-ref="q_size" type="hidden" name="size">
      <input x-ref="q_variant" type="hidden" name="variant">
      <input x-ref="q_qty" type="hidden" name="qty" value="1">
    </form>

    @if(!empty($dashboardBestProducts))
      <section id="best-picks" class="mt-8 scroll-mt-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
          <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-purple-950 to-cyan-800 p-4 text-white shadow-[0_22px_54px_rgba(15,23,42,0.20)] ring-1 ring-white/20 sm:p-6">
            <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-cyan-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-10 h-60 w-60 rounded-full bg-purple-400/18 blur-3xl"></div>
            <div class="pointer-events-none absolute left-8 top-16 h-20 w-[52%] rounded-full bg-white/8 blur-2xl"></div>

            <div class="relative z-10 flex items-center justify-between gap-4">
              <div>
                <div class="text-[10px] font-black uppercase tracking-[.18em] text-cyan-100/90">Featured</div>
                <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">Best Picks</h2>
              </div>
              <a href="{{ route('products.index') }}"
                 class="inline-flex items-center gap-2 rounded-full bg-white/95 px-4 py-2 text-sm font-black text-slate-950 shadow-sm transition hover:-translate-y-0.5 hover:bg-purple-50 hover:text-purple-700">
                Katalog
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </a>
            </div>

            <div class="relative z-10 mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
              @foreach($dashboardBestProducts as $p)
                <button type="button"
                  class="group rounded-[1.25rem] bg-white p-3 text-left text-slate-900 shadow-[0_12px_28px_rgba(0,0,0,0.12)] ring-1 ring-white/70 transition hover:-translate-y-1 hover:shadow-[0_18px_40px_rgba(0,0,0,0.18)]"
                  x-on:click="openProduct(@js($p))"
                >
                  <div class="relative aspect-square overflow-hidden rounded-[.95rem] bg-white ring-1 ring-purple-100/80">
                    <img class="absolute inset-0 h-full w-full object-contain p-2 transition duration-500 group-hover:scale-105"
                         src="{{ $p['image'] ?? ($p['images'][0] ?? asset('images/placeholder.jpg')) }}"
                         alt="{{ $p['name'] ?? 'Product' }}">

                    @if(!empty($p['has_discount']))
                      <span class="absolute left-2 top-2 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-amber-700 shadow-sm">
                        Diskon{{ !empty($p['discount_percentage']) ? ' '.$p['discount_percentage'].'%' : '' }}
                      </span>
                    @endif
                  </div>

                  <div class="mt-3">
                    @if(!empty($p['artist']))
                      <div class="text-[10px] font-black uppercase tracking-wide text-slate-500">{{ $p['artist'] }}</div>
                    @endif
                    <div class="mt-1 line-clamp-2 min-h-[2.5rem] text-xs font-black leading-5 text-slate-950 sm:text-sm">{{ $p['name'] }}</div>
                    <div class="mt-2 flex flex-wrap items-baseline gap-1.5">
                      <span class="text-sm font-black text-purple-600 sm:text-base">{{ !empty($p['has_price_range']) ? 'Mulai ' : '' }}{{ \App\Support\Money::rupiah($p['price']) }}</span>
                      @if(!empty($p['has_discount']) && !empty($p['original_price']))
                        <span class="text-[10px] font-bold text-slate-400 line-through sm:text-xs">{{ \App\Support\Money::rupiah($p['original_price']) }}</span>
                      @endif
                    </div>
                  </div>
                </button>
              @endforeach
            </div>
          </div>
        </div>
      </section>
    @endif

    {{-- ARTIST COLLECTION TAMBAHAN --}}
    @if(!empty($artistProductGroups))
      <section id="artist-collections" class="mt-8 scroll-mt-24">
        <div class="mx-auto max-w-7xl space-y-7 px-4 sm:px-6">
          @foreach($artistProductGroups as $group)
            @php
              $artistTone = \Illuminate\Support\Str::slug($group['slug'] ?: $group['name']);
              if (str_contains($artistTone, 'bts')) {
                $artistTone = 'bts';
              } elseif (str_contains($artistTone, 'illit')) {
                $artistTone = 'illit';
              } elseif (str_contains($artistTone, 'rose')) {
                $artistTone = 'the-rose';
              } elseif (str_contains($artistTone, 'treasure')) {
                $artistTone = 'treasure';
              } elseif (str_contains($artistTone, 'tomorrow') || str_contains($artistTone, 'txt')) {
                $artistTone = 'txt';
              }
            @endphp

            <div class="ps-artist-aurora" data-artist-tone="{{ $artistTone }}" id="artist-group-{{ $group['slug'] ?: \Illuminate\Support\Str::slug($group['name']) }}">
              <div class="relative z-10 flex items-center justify-between gap-4 px-5 py-5 sm:px-7">
                @if(!empty($group['slug']))
                  <a href="{{ route('artist.show', ['artist' => $group['slug']]) }}"
                     class="ps-artist-pill-title group inline-flex min-w-0 items-center gap-2 text-2xl font-extrabold tracking-tight text-slate-950 transition hover:text-purple-700 sm:text-3xl">
                    <span class="truncate">{{ $group['name'] }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                  </a>
                @else
                  <h2 class="ps-artist-pill-title text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                    {{ $group['name'] }}
                  </h2>
                @endif

                <div class="flex shrink-0 items-center gap-2">
                  <button type="button"
                          class="ps-btn-soft flex h-10 w-10 items-center justify-center p-0 bg-white/70"
                          onclick="this.closest('[id^=artist-group-]').querySelector('[data-artist-slider]').scrollBy({ left: -340, behavior: 'smooth' })"
                          aria-label="Geser kiri">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                  </button>
                  <button type="button"
                          class="ps-btn-soft flex h-10 w-10 items-center justify-center p-0 bg-white/70"
                          onclick="this.closest('[id^=artist-group-]').querySelector('[data-artist-slider]').scrollBy({ left: 340, behavior: 'smooth' })"
                          aria-label="Geser kanan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                  </button>
                </div>
              </div>

              <div data-artist-slider class="relative z-10 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth px-5 pb-7 [scrollbar-width:thin] sm:px-7">
                @foreach($group['products'] as $p)
                  <button type="button"
                    class="ps-product-card w-[220px] shrink-0 snap-start text-left text-slate-900 sm:w-[245px]"
                    x-on:click="openProduct(@js($p))"
                  >
                    <div class="ps-product-media">
                      <img class="h-full w-full object-contain bg-white"
                           src="{{ $p['image'] ?? ($p['images'][0] ?? asset('images/placeholder.jpg')) }}"
                           alt="{{ $p['name'] ?? 'Product' }}">

                      @include('partials.product-stock-overlay', ['product' => $p])
                      @include('partials.wishlist-button', ['productId' => $p['id'], 'wishlisted' => !empty($p['wishlisted']), 'context' => 'dashboard-card'])
                    </div>

                    <div class="relative z-10 px-4 pb-5 pt-1">
                      @if(!empty($p['category']))
                        <div class="text-[11px] font-extrabold uppercase tracking-wide text-slate-500">{{ $p['category'] }}</div>
                      @endif
                      <div class="mt-1 line-clamp-2 min-h-[2.5rem] text-sm font-extrabold leading-5 text-slate-950">{{ $p['name'] }}</div>
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
                            <span class="text-lg font-black text-purple-600">{{ !empty($p['has_price_range']) ? 'Mulai ' : '' }}{{ \App\Support\Money::rupiah($p['price']) }}</span>
                            <span class="text-xs font-bold text-slate-400 line-through">{{ \App\Support\Money::rupiah($p['original_price']) }}</span>
                          </div>
                        @else
                          <div class="text-lg font-black text-purple-600">{{ !empty($p['has_price_range']) ? 'Mulai ' : '' }}{{ \App\Support\Money::rupiah($p['price']) }}</div>
                        @endif
                      </div>
                    </div>
                  </button>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @endif

    {{-- PRODUCTS + MODAL --}}
    <section id="products" class="mt-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="ps-dashboard-glass relative overflow-hidden rounded-[2rem] bg-white/68 p-5 shadow-[0_18px_44px_rgba(15,23,42,0.10)] ring-1 ring-white/75 backdrop-blur sm:p-7">
          <div class="pointer-events-none absolute -right-20 -top-24 h-52 w-52 rounded-full bg-purple-200/38 blur-3xl"></div>
          <div class="pointer-events-none absolute -left-24 bottom-10 h-52 w-52 rounded-full bg-cyan-200/34 blur-3xl"></div>

          <div class="relative z-10 flex items-center justify-between gap-4">
            <div>
              <div class="text-[11px] font-black uppercase tracking-[.18em] text-purple-600">Catalog</div>
              <h2 class="ps-dashboard-title-accent mt-1 text-3xl font-extrabold tracking-tight text-slate-950">
                {{ ($selected ?? 'all') === 'all'
                    ? 'All Products'
                    : (collect($categories)->firstWhere('slug', $selected)['name'] ?? 'All Products') }}
              </h2>
            </div>
            <a href="{{ route('products.index') }}"
               class="hidden rounded-full bg-white/85 px-4 py-2 text-sm font-black text-slate-800 shadow-sm ring-1 ring-slate-200/70 transition hover:bg-purple-600 hover:text-white sm:inline-flex">
              Katalog
            </a>
          </div>

          <div class="relative z-10 mt-7 grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
            @foreach($featuredProducts as $p)
              @if($loop->index === ($dashboardProductLimit ?? 6))
                <div x-ref="moreProductsStart" class="col-span-full h-0"></div>
              @endif

              <button type="button"
                class="ps-product-card min-w-0 text-left relative group text-slate-900"
                x-on:click="openProduct(@js($p))"
                @if($loop->index >= ($dashboardProductLimit ?? 6)) x-show="showAll" x-cloak @endif
              >
                <div class="ps-product-media">
                  <img class="w-full h-full object-contain bg-white"
                       src="{{ $p['image'] ?? ($p['images'][0] ?? asset('images/placeholder.jpg')) }}"
                       alt="{{ $p['name'] ?? 'Product' }}">

                  @include('partials.product-stock-overlay', ['product' => $p])

                  @include('partials.wishlist-button', ['productId' => $p['id'], 'wishlisted' => !empty($p['wishlisted']), 'context' => 'dashboard-card'])

                  <div class="hidden sm:block absolute inset-x-4 bottom-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                    <div
                      class="w-full rounded-2xl bg-gradient-to-r from-purple-600/95 to-violet-600/95 text-white font-extrabold py-3 flex items-center justify-center gap-2 cursor-pointer pointer-events-auto shadow-lg shadow-purple-500/25 backdrop-blur transition hover:from-purple-700 hover:to-violet-700"
                      x-on:click.stop="openProduct(@js($p), {{ !empty($p['is_upcoming']) ? 'false' : 'true' }})"
                    >
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
                      {{ !empty($p['is_upcoming']) ? 'Upcoming' : 'Tambah ke Keranjang' }}
                    </div>
                  </div>
                </div>

                <div class="relative z-10 px-4 pb-5 pt-1">
                  @if(!empty($p['artist']))
                    <div class="text-[11px] font-extrabold uppercase tracking-wide text-slate-500">{{ $p['artist'] }}</div>
                  @endif
                  <div class="mt-1 line-clamp-2 min-h-[2.5rem] text-sm font-extrabold leading-5 text-slate-950">{{ $p['name'] }}</div>
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
                        <span class="text-lg font-black text-purple-600">{{ !empty($p['has_price_range']) ? 'Mulai ' : '' }}{{ \App\Support\Money::rupiah($p['price']) }}</span>
                        <span class="text-xs font-bold text-slate-400 line-through">{{ \App\Support\Money::rupiah($p['original_price']) }}</span>
                      </div>
                    @else
                      <div class="text-lg font-black text-purple-600">{{ !empty($p['has_price_range']) ? 'Mulai ' : '' }}{{ \App\Support\Money::rupiah($p['price']) }}</div>
                    @endif
                  </div>
                </div>
              </button>
            @endforeach
          </div>

          @if($hasMoreDashboardProducts ?? false)
            <div class="mt-8 flex justify-center">
              <button type="button"
                      x-show="!showAll"
                      x-cloak
                      class="ps-btn-soft ps-btn-sm"
                      x-on:click="
                        showAll = true;
                        $nextTick(() => {
                          if ($refs.moreProductsStart) {
                            $refs.moreProductsStart.scrollIntoView({ behavior: 'smooth', block: 'start' });
                          }
                        });
                      ">
                <span>View All</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </button>
            </div>
          @endif
        </div>
      </div>
    </section>

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
          <button class="absolute right-4 top-4 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-white/85 text-slate-500 shadow-sm ring-1 ring-slate-200/80 backdrop-blur transition hover:bg-white hover:text-slate-900" x-on:click="open=false; imagePreviewOpen=false"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg></button>

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
                     x-on:touchend="up()"
                >
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
                        aria-label="Prev"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></button>

                      <button type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/90 shadow flex items-center justify-center hover:bg-white"
                        x-on:click.stop="nextImg()"
                        aria-label="Next"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></button>
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
                        aria-label="Select image"
                      >
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

                    <button type="button"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/85 text-slate-700 shadow-sm ring-1 ring-slate-200/80 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:text-rose-500 hover:shadow-md"
                            x-on:click.stop="@auth
                              if (!product?.id) return;
                              const form = document.createElement('form');
                              form.method = 'POST';
                              form.action = '{{ url('/wishlist') }}/' + product.id + '/toggle';
                              const token = document.createElement('input');
                              token.type = 'hidden';
                              token.name = '_token';
                              token.value = '{{ csrf_token() }}';
                              form.appendChild(token);
                              document.body.appendChild(form);
                              form.submit();
                            @else
                              window.openAuthModal('login', '{{ route('wishlist.index') }}');
                            @endauth"
                            aria-label="Wishlist">
                      <svg xmlns="http://www.w3.org/2000/svg"
                           viewBox="0 0 24 24"
                           class="h-5 w-5"
                           :class="product?.wishlisted ? 'scale-110 text-rose-500 drop-shadow-[0_2px_6px_rgba(244,63,94,0.35)]' : 'text-slate-700'"
                           :fill="product?.wishlisted ? 'currentColor' : 'none'"
                           stroke="currentColor"
                           stroke-width="1.8"
                           stroke-linecap="round"
                           stroke-linejoin="round">
                        <path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/>
                      </svg>
                    </button>
                  </div>
                </div>

                <div x-ref="buySection" class="space-y-4">
                  <template x-if="product && product.variants && product.variants.length">
                    <div>
                      <div class="text-sm font-semibold">Pilihan</div>
                      <select
                        class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-purple-200"
                        x-model="variant"
                        x-on:change="refreshOptionState()"
                      >
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
                      <button type="button" class="h-10 w-10 rounded-xl border border-gray-200 hover:bg-gray-50 flex items-center justify-center leading-none" x-on:click="qty = Math.max((currentInStock() ? 1 : 0), qty-1)"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -translate-y-[0.5px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg></button>
                      <div class="w-10 text-center font-semibold" x-text="qty"></div>
                      <button type="button" class="h-10 w-10 rounded-xl border border-gray-200 hover:bg-gray-50 flex items-center justify-center leading-none" x-on:click="qty = Math.min(currentStock(), qty+1)"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -translate-y-[0.5px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg></button>
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
                      </svg><span x-text="isUpcoming() ? 'Upcoming' : 'Tambah ke Keranjang'"></span>
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