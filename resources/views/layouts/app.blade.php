<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>{{ $title ?? 'Purple Stuff' }}</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

  <script src="https://cdn.tailwindcss.com?v={{ config('app.version', '1') }}"></script>

  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    window.openAuthModal = function (tab = 'login', redirect = window.location.href) {
      document.dispatchEvent(new CustomEvent('ui:close-all'));

      document.dispatchEvent(new CustomEvent('auth:open', {
        detail: { tab, redirect }
      }));
    };
  </script>

  <style>[x-cloak]{display:none!important;}</style>

  <style>
    .card-img { aspect-ratio: 4/3; object-fit: cover; width: 100%; height: 100%; }

    /* PurpleStuff storefront visual layer.
       Hanya class tampilan: tidak mengubah route, controller, cart, stok, checkout, atau logic fitur. */
    .ps-glass-panel {
      background: rgba(255,255,255,.62);
      border: 0;
      box-shadow: 0 12px 32px rgba(15,23,42,.08);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    .ps-section-panel {
      background: rgba(255,255,255,.58);
      border: 0;
      border-radius: 2rem;
      box-shadow: 0 16px 40px rgba(15,23,42,.08);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    .ps-product-card {
      position: relative;
      overflow: hidden;
      border-radius: 1.65rem;
      background: rgba(255,255,255,.94);
      border: 0;
      box-shadow: 0 14px 34px rgba(15,23,42,.08);
      transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
    }
    .ps-product-card::before {
      content: '';
      position: absolute;
      inset: 0;
      pointer-events: none;
      border-radius: inherit;
      background: linear-gradient(135deg, rgba(168,85,247,.16), rgba(59,130,246,.06) 42%, rgba(255,255,255,0) 75%);
      opacity: .75;
    }
    .ps-product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 22px 54px rgba(15,23,42,.14);
      background: rgba(255,255,255,.98);
    }

    .ps-product-media {
      position: relative;
      aspect-ratio: 1/1;
      margin: .75rem;
      overflow: hidden;
      border-radius: 1.25rem;
      background: linear-gradient(135deg, rgba(250,245,255,.95), rgba(255,255,255,.95));
      border: 1px solid rgba(168,85,247,.18);
      box-shadow: inset 0 0 0 8px rgba(216,180,254,.42);
    }

    .ps-product-media img {
      background: transparent !important;
      transition: transform .28s ease;
    }
    .ps-product-card:hover .ps-product-media img {
      transform: scale(1.025);
    }

    .ps-chip {
      border-radius: 9999px;
      background: rgba(255,255,255,.9);
      border: 1px solid rgba(226,232,240,.95);
      box-shadow: 0 10px 24px rgba(15,23,42,.06);
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .ps-chip:hover {
      transform: translateY(-2px);
      border-color: rgba(168,85,247,.35);
      box-shadow: 0 16px 32px rgba(15,23,42,.1);
    }

    .ps-category-card {
      border-radius: 1.5rem;
      background: rgba(255,255,255,.9);
      border: 0;
      box-shadow: 0 14px 34px rgba(15,23,42,.08);
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .ps-category-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 46px rgba(15,23,42,.12);
    }


    .ps-btn-light,
    .ps-btn-soft,
    .ps-btn-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .45rem;
      border-radius: 9999px;
      font-weight: 800;
      transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease, opacity .18s ease;
      text-decoration: none;
    }

    .ps-btn-light {
      background: rgba(229,236,244,.56);
      color: rgb(15,23,42);
      border: 1.5px solid rgba(255,255,255,.28);
      box-shadow: 0 10px 24px rgba(15,23,42,.10), inset 0 1px 0 rgba(255,255,255,.10);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    .ps-btn-light:hover {
      transform: translateY(-1px);
      box-shadow: 0 16px 32px rgba(15,23,42,.14), inset 0 1px 0 rgba(255,255,255,.12);
      background: rgba(229,236,244,.68);
      color: rgb(15,23,42);
    }

    .ps-btn-soft {
      background: rgba(222,231,240,.44);
      color: rgb(15,23,42);
      border: 1.5px solid rgba(255,255,255,.22);
      box-shadow: 0 8px 20px rgba(15,23,42,.08), inset 0 1px 0 rgba(255,255,255,.08);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    .ps-btn-soft:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.10);
      background: rgba(222,231,240,.58);
      color: rgb(15,23,42);
    }

    .ps-btn-primary {
      color: white;
      background: linear-gradient(135deg, rgb(124,58,237), rgb(147,51,234));
      box-shadow: 0 12px 28px rgba(124,58,237,.28);
    }
    .ps-btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 36px rgba(124,58,237,.34);
      filter: saturate(1.03);
    }

    .ps-btn-md { padding: .8rem 1.35rem; font-size: .92rem; }
    .ps-btn-sm { padding: .7rem 1.1rem; font-size: .86rem; }
    .ps-btn-xs { padding: .55rem .95rem; font-size: .76rem; }
    .ps-btn-light:focus-visible,
    .ps-btn-soft:focus-visible,
    .ps-btn-primary:focus-visible {
      outline: 3px solid rgba(168,85,247,.35);
      outline-offset: 3px;
    }



    /* Checkout / Orders / Payment visual system v3
       Hanya class tampilan. Tidak mengubah route, controller, cart, stok, checkout, atau logic fitur. */
    /* Checkout / payment / orders visible flow theme.
       Hanya tampilan: tidak mengubah form, route, controller, timer, ongkir, upload, atau stok. */
    .ps-flow-page-title {
      display:inline-flex;
      align-items:center;
      gap:.65rem;
      border-radius:9999px;
      padding:.7rem 1.05rem;
      background:rgba(30,41,59,.10);
      border:1px solid rgba(255,255,255,.34);
      box-shadow:0 14px 34px rgba(15,23,42,.10), inset 0 1px 0 rgba(255,255,255,.16);
      backdrop-filter:blur(16px);
      -webkit-backdrop-filter:blur(16px);
    }

    .ps-flow-card {
      position:relative;
      overflow:hidden;
      border-radius:2rem;
      background:rgba(241,245,249,.74);
      border:1px solid rgba(100,116,139,.14);
      box-shadow:0 18px 46px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.28);
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
    }
    .ps-flow-card::before {
      content:'';
      position:absolute;
      top:0;
      left:1.25rem;
      right:1.25rem;
      height:5px;
      border-radius:0 0 999px 999px;
      background:linear-gradient(90deg, rgba(124,58,237,.95), rgba(56,189,248,.72), rgba(255,255,255,0));
      pointer-events:none;
    }
    .ps-flow-card h2 {
      display:flex;
      align-items:center;
      gap:.55rem;
    }
    .ps-flow-card h2::before {
      content:'';
      width:.62rem;
      height:.62rem;
      border-radius:999px;
      background:linear-gradient(135deg, rgb(124,58,237), rgb(56,189,248));
      box-shadow:0 0 0 4px rgba(168,85,247,.10);
      flex:0 0 auto;
    }

    .ps-flow-subcard {
      position:relative;
      border-radius:1.5rem;
      background:rgba(255,255,255,.62);
      border:1px solid rgba(100,116,139,.12);
      box-shadow:0 10px 24px rgba(15,23,42,.07), inset 0 1px 0 rgba(255,255,255,.24);
      backdrop-filter:blur(12px);
      -webkit-backdrop-filter:blur(12px);
    }

    .ps-flow-product-row {
      position:relative;
      display:flex;
      gap:1rem;
      border-radius:1.5rem;
      background:rgba(255,255,255,.62);
      border:1px solid rgba(100,116,139,.14);
      box-shadow:0 12px 28px rgba(15,23,42,.075), inset 0 1px 0 rgba(255,255,255,.28);
      padding:1rem;
      backdrop-filter:blur(14px);
      -webkit-backdrop-filter:blur(14px);
    }
    .ps-flow-product-row::before {
      content:'';
      position:absolute;
      left:0;
      top:1rem;
      bottom:1rem;
      width:4px;
      border-radius:999px;
      background:linear-gradient(180deg, rgba(124,58,237,.7), rgba(56,189,248,.55));
    }

    .ps-flow-input {
      width:100%;
      border-radius:1.05rem;
      border:1px solid rgba(100,116,139,.20);
      background:rgba(255,255,255,.76);
      padding:.88rem 1rem;
      outline:none;
      box-shadow:inset 0 1px 0 rgba(255,255,255,.36), 0 8px 18px rgba(15,23,42,.035);
      transition:box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .ps-flow-input:focus {
      border-color:rgba(168,85,247,.52);
      background:rgba(255,255,255,.96);
      box-shadow:0 0 0 4px rgba(168,85,247,.14), inset 0 1px 0 rgba(255,255,255,.42);
    }

    .ps-flow-pill {
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:9999px;
      padding:.48rem .85rem;
      font-size:.75rem;
      font-weight:900;
      color:rgb(91,33,182);
      background:rgba(243,232,255,.82);
      border:1px solid rgba(192,132,252,.45);
      box-shadow:0 9px 20px rgba(88,28,135,.09);
    }

    .ps-flow-primary-button {
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:.5rem;
      width:100%;
      border-radius:1.1rem;
      background:linear-gradient(135deg, rgb(109,40,217), rgb(147,51,234));
      color:white;
      font-weight:900;
      padding:.95rem 1rem;
      box-shadow:0 18px 36px rgba(124,58,237,.30);
      transition:transform .18s ease, box-shadow .18s ease, filter .18s ease, opacity .18s ease;
    }
    .ps-flow-primary-button:hover { transform:translateY(-1px); box-shadow:0 22px 44px rgba(124,58,237,.35); filter:saturate(1.04); }
    .ps-flow-primary-button:disabled { cursor:not-allowed; opacity:.45; transform:none; box-shadow:none; }

    .ps-flow-secondary-button {
      border-radius:1.1rem;
      background:linear-gradient(135deg, rgba(109,40,217,.96), rgba(147,51,234,.96));
      color:white;
      font-weight:900;
      box-shadow:0 16px 32px rgba(124,58,237,.25);
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .ps-flow-secondary-button:hover { transform:translateY(-1px); box-shadow:0 20px 38px rgba(124,58,237,.30); }

    .ps-flow-total-box {
      border-radius:1.45rem;
      background:rgba(250,245,255,.78);
      border:1px solid rgba(192,132,252,.34);
      box-shadow:0 10px 24px rgba(88,28,135,.07), inset 0 1px 0 rgba(255,255,255,.32);
    }

    .ps-flow-alert-success { border-radius:1.25rem; border:1px solid rgba(74,222,128,.38); background:rgba(220,252,231,.86); color:rgb(21,128,61); box-shadow:0 10px 22px rgba(21,128,61,.06); }
    .ps-flow-alert-danger { border-radius:1.25rem; border:1px solid rgba(252,165,165,.48); background:rgba(254,242,242,.88); color:rgb(185,28,28); box-shadow:0 10px 22px rgba(185,28,28,.06); }
    .ps-flow-alert-amber { border-radius:1.25rem; border:1px solid rgba(251,191,36,.44); background:rgba(255,251,235,.88); color:rgb(180,83,9); box-shadow:0 10px 22px rgba(180,83,9,.06); }
    .ps-flow-alert-info { border-radius:1.25rem; border:1px solid rgba(125,211,252,.44); background:rgba(240,249,255,.88); color:rgb(3,105,161); box-shadow:0 10px 22px rgba(3,105,161,.06); }

  </style>

  @stack('head')
</head>

@php
  // Admin yang menekan “Lihat Toko” tetap dikenali sebagai admin preview.
  // Ini tidak mengubah guard pelanggan, hanya untuk tampilan storefront agar admin tidak terlihat logout.
  $storefrontUser = auth('web')->user() ?: auth('admin')->user();
  $isAdminStorePreview = auth('admin')->check() && !auth('web')->check();
@endphp

<body class="text-gray-900"
      x-data="{
        cartOpen: {{ (session('cart_open') || request()->routeIs('cart.index')) ? 'true' : 'false' }},
        profileOpen: {{ session('profile_open') ? 'true' : 'false' }},

        openCart() {
          this.profileOpen = false;
          this.cartOpen = true;
        },

        openProfileDrawer() {
          this.cartOpen = false;
          this.profileOpen = true;
        },

        closeAllDrawers() {
          this.cartOpen = false;
          this.profileOpen = false;
        },

        openLoginModal(tab = 'login', redirect = window.location.href) {
          this.closeAllDrawers();
          window.openAuthModal(tab, redirect);
        }
      }"
      x-on:ui:close-all.window="closeAllDrawers()">

  <div class="fixed inset-0 -z-20 bg-cover bg-center bg-no-repeat"
       style="background-image:url('{{ asset('images/bg-site.jpg') }}');">
  </div>

  <div class="fixed inset-0 -z-10 bg-white/25"></div>

  <div class="relative z-10 min-h-screen">

  @if($isAdminStorePreview)
    <div class="bg-slate-950 px-4 py-2 text-center text-sm text-white">
      Anda sedang melihat toko sebagai admin.
      <a href="{{ route('admin.dashboard') }}" class="ml-2 font-semibold text-purple-200 underline underline-offset-2 hover:text-white">
        Kembali ke Admin Dashboard
      </a>
    </div>
  @endif

<header
  x-data="{
    mobileSearchOpen: false,
    mobileMenuOpen: false,
    shopMenuOpen: false,
    artistMenuOpen: false,
    shopMenuPinned: false,
    artistMenuPinned: false,
    isTop: true,
    handleScroll() {
      this.isTop = window.scrollY < 10;
    }
  }"
  x-init="
    handleScroll();
    window.addEventListener('scroll', () => handleScroll(), { passive: true });
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 1024) {
        mobileSearchOpen = false;
        mobileMenuOpen = false;
      }
    });
  "
  :class="isTop
    ? 'bg-transparent border-transparent shadow-none'
    : 'bg-white/78 backdrop-blur-xl border-black/5 shadow-sm'"
  class="sticky top-0 z-50 border-b transition-all duration-300"
>
  @php
    $cartCount = collect(session('cart', []))->sum('qty');
    $wishlistCount = auth('web')->check() ? auth('web')->user()->wishlistProducts()->count() : 0;

    $headerArtists = collect($artists ?? [])->map(function ($artist) {
        return is_array($artist)
            ? ['name' => $artist['name'] ?? '', 'slug' => $artist['slug'] ?? '']
            : ['name' => $artist->name ?? '', 'slug' => $artist->slug ?? ''];
    })->filter(fn ($artist) => filled($artist['name'] ?? '') && filled($artist['slug'] ?? ''))->values();

    if ($headerArtists->isEmpty() && class_exists(\App\Models\Artist::class) && \Illuminate\Support\Facades\Schema::hasTable('artists')) {
        $headerArtists = \App\Models\Artist::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($artist) => ['name' => $artist->name, 'slug' => $artist->slug])
            ->filter(fn ($artist) => filled($artist['name'] ?? '') && filled($artist['slug'] ?? ''))
            ->values();
    }
  @endphp

  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    <div class="flex h-16 items-center gap-3 lg:h-[72px]">
      
      <div x-show="!mobileSearchOpen" x-cloak class="flex w-full items-center gap-3">
        
        <a href="{{ route('dashboard') }}" class="group inline-flex shrink-0 items-center gap-3" aria-label="Purple Stuff Home">
          <img src="{{ asset('images/logo.png') }}" alt="Purple Stuff" class="h-10 w-10 object-contain sm:h-11 sm:w-11">
          <span class="hidden text-lg font-semibold tracking-tight text-slate-950 sm:inline-flex lg:text-xl">
            Purple Stuff
          </span>
        </a>

        {{-- Navigasi desktop --}}
        <nav class="ml-4 hidden min-w-0 flex-1 items-center justify-center gap-0.5 lg:flex" aria-label="Navigasi utama">
          <a href="{{ route('dashboard') }}"
             class="whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-normal text-slate-900 transition hover:bg-white/70 hover:text-purple-700">
            Home
          </a>

          <div class="relative" x-on:mouseenter="shopMenuOpen = true" x-on:mouseleave="if (!shopMenuPinned) shopMenuOpen = false" x-on:click.outside="shopMenuPinned = false; shopMenuOpen = false">
            <button type="button"
                    class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-normal text-slate-900 transition hover:bg-white/70 hover:text-purple-700"
                    x-on:click.stop="shopMenuPinned = !shopMenuPinned; shopMenuOpen = true">
              Shop Produk
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div x-show="shopMenuOpen"
                 x-transition
                 x-cloak
                 class="absolute left-1/2 top-full z-50 mt-3 w-64 -translate-x-1/2 rounded-3xl bg-white/95 p-3 shadow-2xl ring-1 ring-slate-200/80 backdrop-blur">
              <a href="{{ route('products.index') }}"
                 class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 transition hover:bg-purple-50 hover:text-purple-700">
                Semua Produk
              </a>
              <div class="my-2 h-px bg-slate-100"></div>
              @foreach($categories as $cat)
                @if(($cat['slug'] ?? 'all') !== 'all')
                  <a href="{{ route('products.index', ['category' => $cat['slug']]) }}"
                     class="block rounded-2xl px-4 py-2.5 text-sm font-normal text-slate-600 transition hover:bg-purple-50 hover:text-purple-700">
                    {{ $cat['name'] }}
                  </a>
                @endif
              @endforeach
            </div>
          </div>

          <div class="relative" x-on:mouseenter="artistMenuOpen = true" x-on:mouseleave="if (!artistMenuPinned) artistMenuOpen = false" x-on:click.outside="artistMenuPinned = false; artistMenuOpen = false">
            <button type="button"
                    class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-normal text-slate-900 transition hover:bg-white/70 hover:text-purple-700"
                    x-on:click.stop="artistMenuPinned = !artistMenuPinned; artistMenuOpen = true"
                    aria-haspopup="true"
                    :aria-expanded="artistMenuOpen ? 'true' : 'false'">
              Artist Collection
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="artistMenuOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>

            <div x-show="artistMenuOpen"
                 x-transition
                 x-cloak
                 class="absolute left-1/2 top-full z-50 mt-3 w-64 -translate-x-1/2 rounded-3xl bg-white/95 p-3 shadow-2xl ring-1 ring-slate-200/80 backdrop-blur">
              <div class="px-4 py-3 text-xs font-semibold uppercase tracking-[.18em] text-slate-400">
                Pilih Artist
              </div>

              @if($headerArtists->isNotEmpty())
                <div class="mb-2 h-px bg-slate-100"></div>
                @foreach($headerArtists as $artist)
                  <a href="{{ route('artist.show', ['artist' => $artist['slug']]) }}"
                     class="block rounded-2xl px-4 py-2.5 text-sm font-normal text-slate-600 transition hover:bg-purple-50 hover:text-purple-700">
                    {{ $artist['name'] }}
                  </a>
                @endforeach
              @else
                <div class="rounded-2xl px-4 py-3 text-sm text-slate-500">
                  Belum ada artist.
                </div>
              @endif
            </div>
          </div>

          <a href="{{ route('dashboard') }}#category"
             class="whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-normal text-slate-900 transition hover:bg-white/70 hover:text-purple-700">
            Category
          </a>

          <a href="#about-website"
             class="whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-normal text-slate-900 transition hover:bg-white/70 hover:text-purple-700">
            About
          </a>

          @if(auth('web')->check())
            <a href="{{ route('orders.index') }}"
               class="whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-normal text-slate-900 transition hover:bg-white/70 hover:text-purple-700">
              Pesanan Saya
            </a>
          @else
            <button type="button"
                    class="whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-normal text-slate-900 transition hover:bg-white/70 hover:text-purple-700"
                    x-on:click="openLoginModal('login', '{{ route('orders.index') }}')">
              Pesanan Saya
            </button>
          @endif
        </nav>

        {{-- Search full desktop --}}
        <div class="hidden xl:flex w-[260px] shrink-0 justify-end">
          <form action="{{ route('products.index') }}"
                method="GET"
                class="w-full"
                onsubmit="this.q.value=this.q.value.trim(); if(!this.q.value){ this.q.focus(); return false; }">
            <div class="relative">
              <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari produk"
                class="w-full rounded-full bg-white/82 px-4 py-2.5 pr-10 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-slate-200/80 outline-none backdrop-blur placeholder:text-slate-400 focus:ring-2 focus:ring-purple-200"
              >
              <button type="submit"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-purple-700"
                      aria-label="Cari">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                </svg>
              </button>
            </div>
          </form>
        </div>

        {{-- Right icons --}}
        <div class="ml-auto flex items-center gap-1.5 lg:gap-2">
          <button type="button"
                  class="rounded-full p-2 text-slate-900 transition hover:bg-white/75 hover:text-purple-700 xl:hidden"
                  x-on:click="mobileSearchOpen = true"
                  aria-label="Buka search">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
            </svg>
          </button>

          {{-- Wishlist --}}
          @if(auth('web')->check())
            <a href="{{ route('wishlist.index') }}"
               class="relative rounded-full p-2 text-slate-900 transition hover:bg-white/75 hover:text-purple-700"
               aria-label="Wishlist">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>
              </svg>
              @if($wishlistCount > 0)
                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-semibold text-white">
                  {{ $wishlistCount }}
                </span>
              @endif
            </a>
          @else
            <button type="button"
                    class="rounded-full p-2 text-slate-900 transition hover:bg-white/75 hover:text-purple-700"
                    x-on:click="openLoginModal('login', '{{ route('wishlist.index') }}')"
                    aria-label="Wishlist">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>
              </svg>
            </button>
          @endif

          {{-- Cart --}}
          <button type="button"
                  class="relative rounded-full p-2 text-slate-900 transition hover:bg-white/75 hover:text-purple-700"
                  x-on:click.stop="openCart()"
                  aria-label="Cart">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-6 w-6"
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

            @if($cartCount > 0)
              <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-purple-600 px-1 text-[10px] font-semibold text-white">
                {{ $cartCount }}
              </span>
            @endif
          </button>

          {{-- Profile --}}
          <button type="button"
                  class="rounded-full p-2 text-slate-900 transition hover:bg-white/75 hover:text-purple-700"
                  aria-label="Profile"
                  x-on:click.stop="
                    @if($storefrontUser)
                      openProfileDrawer()
                    @else
                      openLoginModal('login', window.location.href)
                    @endif
                  ">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-6 w-6"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round">
              <circle cx="12" cy="8" r="3.5"></circle>
              <path d="M4 21a8 8 0 0 1 16 0"></path>
            </svg>
          </button>

          <button type="button"
                  class="rounded-full p-2 text-slate-900 transition hover:bg-white/75 hover:text-purple-700 lg:hidden"
                  x-on:click="mobileMenuOpen = !mobileMenuOpen"
                  aria-label="Menu">
            <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileMenuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
          </button>
        </div>
      </div>

      {{-- HEADER SEARCH MODE --}}
      <form x-show="mobileSearchOpen"
            x-cloak
            action="{{ route('products.index') }}"
            method="GET"
            class="flex w-full items-center gap-2 xl:hidden"
            onsubmit="this.q.value=this.q.value.trim(); if(!this.q.value){ this.q.focus(); return false; }">
        <button type="button"
                class="rounded-full p-2 text-slate-900 transition hover:bg-white/75 hover:text-slate-700"
                x-on:click="mobileSearchOpen = false"
                aria-label="Tutup search">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>

        <div class="relative flex-1">
          <input
            x-ref="mobileSearchInput"
            x-effect="if (mobileSearchOpen) { $nextTick(() => $refs.mobileSearchInput.focus()) }"
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari produk"
            class="w-full rounded-full bg-white/92 px-5 py-2.5 pr-11 text-sm font-semibold outline-none ring-1 ring-slate-200/90 focus:ring-2 focus:ring-purple-200"
          >

          <button type="submit"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-purple-700"
                  aria-label="Cari">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
            </svg>
          </button>
        </div>
      </form>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileMenuOpen && !mobileSearchOpen" x-transition x-cloak class="lg:hidden">
      <div class="mb-4 rounded-[1.5rem] bg-white/92 p-3 shadow-xl ring-1 ring-slate-200/80 backdrop-blur">
        <a href="{{ route('dashboard') }}" class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700">Home</a>
        <a href="{{ route('products.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700">Semua Produk</a>
        <div class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-900">Artist Collection</div>
        @if($headerArtists->isNotEmpty())
          <div class="mx-4 my-1 h-px bg-slate-100"></div>
          @foreach($headerArtists as $artist)
            <a href="{{ route('artist.show', ['artist' => $artist['slug']]) }}" class="block rounded-2xl px-7 py-2.5 text-sm font-normal text-slate-600 hover:bg-purple-50 hover:text-purple-700">{{ $artist['name'] }}</a>
          @endforeach
          <div class="mx-4 my-1 h-px bg-slate-100"></div>
        @else
          <div class="rounded-2xl px-7 py-2.5 text-sm text-slate-500">Belum ada artist.</div>
        @endif
        <a href="{{ route('dashboard') }}#category" class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700">Category</a>
        <a href="#about-website" class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700" x-on:click="mobileMenuOpen = false">About</a>
        @if(auth('web')->check())
          <a href="{{ route('orders.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700">Pesanan Saya</a>
          <a href="{{ route('wishlist.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700">Wishlist</a>
        @else
          <button type="button" class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700" x-on:click="openLoginModal('login', '{{ route('orders.index') }}')">Pesanan Saya</button>
          <button type="button" class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700" x-on:click="openLoginModal('login', '{{ route('wishlist.index') }}')">Wishlist</button>
        @endif
        <a href="{{ route('pages.contact') }}" class="block rounded-2xl px-4 py-3 text-sm font-normal text-slate-900 hover:bg-purple-50 hover:text-purple-700">Bantuan</a>
      </div>
    </div>
  </div>
</header>

    <main class="relative z-10">
      @yield('content')
    </main>

    <footer id="footer-newsletter" class="mt-20 bg-gradient-to-br from-purple-950 via-purple-900 to-slate-950 text-slate-200 backdrop-blur">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 grid grid-cols-1 md:grid-cols-4 gap-10">
        <div id="about-website" class="scroll-mt-24">
          <a href="{{ route('dashboard') }}" class="inline-flex flex-col items-start">
            <img
              src="{{ asset('images/logo.png') }}"
              alt="Purple Stuff"
              class="h-20 w-20 rounded-full object-cover shadow-md ring-1 ring-white/15"
            >
          </a>
          <p class="mt-4 max-w-xs text-sm leading-6 text-slate-300/85">
            Purple Stuff adalah toko online yang menyediakan merchandise pilihan, koleksi fandom, dan produk spesial.
          </p>
        </div>

        <div>
          <div class="font-semibold text-white">Shop</div>
          <ul class="mt-3 space-y-2 text-sm text-slate-300/80">
            @foreach($categories as $cat)
              <li>
                <a href="{{ ($cat['slug'] ?? 'all') === 'all' ? route('products.index') : route('products.index', ['category' => $cat['slug']]) }}"
                   class="text-white/70 transition hover:text-white">
                  {{ $cat['name'] }}
                </a>
              </li>
            @endforeach
          </ul>
        </div>

        <div id="customer-service" class="scroll-mt-24">
          <div class="font-semibold text-white">Customer Service</div>
          <ul class="mt-3 space-y-2 text-sm text-slate-300/80">
            <li><a href="{{ route('pages.contact') }}" class="text-white/70 transition hover:text-white">Contact Us</a></li>
            <li><a href="{{ route('pages.shipping') }}" class="text-white/70 transition hover:text-white">Shipping Info</a></li>
            <li><a href="{{ route('pages.returns') }}" class="text-white/70 transition hover:text-white">Returns</a></li>
            <li><a href="{{ route('pages.faq') }}" class="text-white/70 transition hover:text-white">FAQ</a></li>
          </ul>
          <p class="mt-4 text-xs leading-5 text-slate-400">
            Temukan informasi kontak, pengiriman, pengembalian, dan pertanyaan umum pelanggan.
          </p>
        </div>

        <div>
          <div class="font-semibold text-white">Stay Connected</div>
          <p class="mt-3 text-sm leading-6 text-slate-300/85">
            Dapatkan update produk terbaru, restock, dan promo khusus dari Purple Stuff.
          </p>

          <form action="{{ route('newsletter.subscribe') }}#footer-newsletter" method="POST" class="mt-4 flex items-center gap-3">
            @csrf
            <input
              type="email"
              name="email"
              required
              value="{{ old('email') }}"
              placeholder="Email kamu"
              class="w-full rounded-xl bg-white/10 text-white placeholder-white/50 px-4 py-3 outline-none border border-white/15 focus:ring-2 focus:ring-purple-300/40"
            >
            <button
              type="submit"
              class="shrink-0 h-12 w-12 rounded-xl bg-purple-600 hover:bg-purple-500 text-white flex items-center justify-center shadow-lg shadow-purple-950/20 transition"
              aria-label="Subscribe"
              title="Subscribe"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 6 9-6M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/>
              </svg>
            </button>
          </form>

          @if(session('newsletter_success'))
            <div class="mt-3 rounded-2xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-3 text-sm font-semibold text-emerald-100">
              {{ session('newsletter_success') }}
            </div>
          @endif
          @if(session('newsletter_error'))
            <div class="mt-3 rounded-2xl border border-red-300/20 bg-red-400/10 px-4 py-3 text-sm font-semibold text-red-100">
              {{ session('newsletter_error') }}
            </div>
          @endif
          @if($errors->has('email'))
            <div class="mt-3 rounded-2xl border border-red-300/20 bg-red-400/10 px-4 py-3 text-sm font-semibold text-red-100">
              {{ $errors->first('email') }}
            </div>
          @endif

          @php
            $footerWhatsappNumber = '6282340064510';
            $footerWhatsappMessage = 'Halo Purple Stuff, saya ingin bertanya tentang produk.';
            $footerWhatsappUrl = 'https://api.whatsapp.com/send/?phone=6282340064510&text&type=phone_number&app_absent=0' . $footerWhatsappNumber . '?text=' . rawurlencode($footerWhatsappMessage);
          @endphp

          <div class="mt-5 flex items-center gap-4">
            <a href="https://www.instagram.com/purplestuff.merch/" target="_blank" rel="noopener"
               class="text-white/70 hover:text-white transition"
               aria-label="Instagram" title="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="5" ry="5"></rect>
                <path d="M16 11.37a4 4 0 1 1-7.37 2.63A4 4 0 0 1 16 11.37z"></path>
                <line x1="17.5" y1="6.5" x2="17.5" y2="6.5"></line>
              </svg>
            </a>

            <a href="https://www.tiktok.com/@purplestuffmerch?_r=1&_t=ZS-97efuP6xHam" target="_blank" rel="noopener"
               class="text-white/70 hover:text-white transition"
               aria-label="TikTok" title="TikTok">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M16.5 3c.6 2.7 2.1 4.2 4.5 4.5V11c-1.8.1-3.4-.4-4.5-1.4V16c0 3.4-2.6 6-6 6s-6-2.6-6-6 2.6-6 6-6c.4 0 .8 0 1.2.1v3.7c-.4-.2-.8-.3-1.2-.3-1.4 0-2.5 1.1-2.5 2.5S9.1 18.5 10.5 18.5 13 17.4 13 16V3h3.5z"/>
              </svg>
            </a>

            <a href="{{ $footerWhatsappUrl }}" target="_blank" rel="noopener"
               class="text-white/70 hover:text-white transition"
               aria-label="WhatsApp" title="WhatsApp">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.52 3.48A11.86 11.86 0 0 0 12.08 0C5.48 0 .11 5.37.11 11.97c0 2.11.55 4.17 1.6 5.99L0 24l6.2-1.63a11.93 11.93 0 0 0 5.88 1.5h.01c6.6 0 11.97-5.37 11.97-11.97 0-3.2-1.25-6.2-3.54-8.42ZM12.09 21.85h-.01a9.94 9.94 0 0 1-5.06-1.39l-.36-.21-3.68.97.98-3.59-.23-.37a9.86 9.86 0 0 1-1.51-5.29c0-5.49 4.47-9.96 9.97-9.96a9.9 9.9 0 0 1 7.05 2.93 9.9 9.9 0 0 1 2.92 7.04c0 5.49-4.47 9.87-10.07 9.87Zm5.47-7.38c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <div class="border-t border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 text-center text-sm text-slate-300/80">
          © 2026 Purple Stuff. All rights reserved.
        </div>
      </div>
    </footer>

  </div>

  @include('partials.profile-drawer')
  @include('partials.cart-drawer')

  @stack('scripts')

  <script>
    // Bersihkan service worker Firebase lama yang pernah terpasang di root `/`.
    // Service worker admin yang baru tetap aman karena memakai scope `/admin/`.
    (() => {
      if (!('serviceWorker' in navigator)) return;

      window.addEventListener('load', () => {
        navigator.serviceWorker.getRegistration('/').then((registration) => {
          const activeScript = registration?.active?.scriptURL || registration?.waiting?.scriptURL || registration?.installing?.scriptURL || '';
          const legacyScript = `${window.location.origin}/firebase-messaging-sw.js`;

          if (registration && activeScript === legacyScript) {
            return registration.unregister();
          }
        }).catch(() => {
          // Abaikan agar halaman pelanggan tidak terganggu jika browser tidak mengizinkan akses service worker.
        });
      });
    })();
  </script>

  @include('partials.auth-modal')
</body>
</html>