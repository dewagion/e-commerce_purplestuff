@props([
    'productId',
    'wishlisted' => false,
    'context' => 'default',
])

@php
    $formId = 'wishlist-form-' . md5($productId . '-' . $context);
    $isAdminStorePreview = auth('admin')->check() && !auth('web')->check();
@endphp

@auth('web')
  <form id="{{ $formId }}" method="POST" action="{{ route('wishlist.toggle', $productId) }}" class="hidden">
    @csrf
  </form>
  <div role="button" tabindex="0"
       class="absolute top-3 right-3 flex h-10 w-10 items-center justify-center rounded-full border border-white/70 bg-white/85 shadow-md backdrop-blur transition-all duration-200 hover:scale-105 hover:bg-white active:scale-95 cursor-pointer"
       onclick="event.stopPropagation(); document.getElementById('{{ $formId }}').submit();">
    <svg xmlns="http://www.w3.org/2000/svg"
         viewBox="0 0 24 24"
         class="h-5 w-5 transition-all duration-200 {{ $wishlisted ? 'scale-110 text-rose-500 drop-shadow-[0_2px_6px_rgba(244,63,94,0.35)]' : 'text-slate-700 group-hover:text-slate-900' }}"
         fill="{{ $wishlisted ? 'currentColor' : 'none' }}"
         stroke="currentColor"
         stroke-width="1.8"
         stroke-linecap="round"
         stroke-linejoin="round">
      <path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/>
    </svg>
  </div>
@else
  <div role="button" tabindex="0"
       class="absolute top-3 right-3 flex h-10 w-10 items-center justify-center rounded-full border border-white/70 bg-white/85 shadow-md backdrop-blur transition-all duration-200 hover:scale-105 hover:bg-white active:scale-95 cursor-pointer"
       title="{{ $isAdminStorePreview ? 'Wishlist hanya untuk pelanggan' : 'Login untuk menambahkan wishlist' }}"
       onclick="event.stopPropagation(); @if(!$isAdminStorePreview) window.openAuthModal('login', '{{ route('wishlist.index') }}'); @endif">
    <svg xmlns="http://www.w3.org/2000/svg"
         viewBox="0 0 24 24"
         class="h-5 w-5 text-slate-700 transition-all duration-200"
         fill="none"
         stroke="currentColor"
         stroke-width="1.8"
         stroke-linecap="round"
         stroke-linejoin="round">
      <path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/>
    </svg>
  </div>
@endauth
