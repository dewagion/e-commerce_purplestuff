@php
  // Saat admin menekan “Lihat Toko”, guard yang aktif adalah auth:admin, bukan auth:web.
  // Karena itu jangan memakai auth()->user() saja, agar admin tidak terlihat seperti guest/logout.
  $u = auth('web')->user() ?: auth('admin')->user();
  $isAdminStorePreview = auth('admin')->check() && !auth('web')->check();
  $activeProfileGuard = $isAdminStorePreview ? 'admin' : 'web';
  $profileUpdateRoute = $isAdminStorePreview
      ? (\Illuminate\Support\Facades\Route::has('admin.profile.update') ? route('admin.profile.update') : '#')
      : (\Illuminate\Support\Facades\Route::has('profile.update') ? route('profile.update') : '#');
  $profileEditShouldOpen = (bool) session('profile_edit_open') && session('profile_edit_guard') === $activeProfileGuard;
  $initial = $u ? strtoupper(mb_substr($u->name ?? 'U', 0, 1)) : 'U';
@endphp

<div
  x-cloak
  x-data="{ profileEditOpen: {{ $profileEditShouldOpen ? 'true' : 'false' }} }"
  x-show="profileOpen"
  class="fixed inset-0 z-[70]"
  aria-labelledby="profile-drawer-title"
  role="dialog"
  aria-modal="true"
>
  <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" x-on:click="profileOpen=false"></div>

  <div
    class="absolute inset-y-3 right-3 w-[calc(100%-1.5rem)] max-w-lg overflow-hidden rounded-[2rem] bg-slate-100/88 shadow-[0_30px_90px_rgba(15,23,42,.26)] ring-1 ring-white/50 backdrop-blur-xl sm:inset-y-4 sm:right-4 sm:w-full"
    x-transition:enter="transform transition ease-out duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transform transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
  >
    <div class="flex h-full flex-col">
      {{-- header --}}
      <div class="sticky top-0 z-10 flex items-center justify-between border-b border-white/45 bg-slate-100/85 px-6 py-5 backdrop-blur-xl">
        <div>
          <h2 id="profile-drawer-title" class="text-2xl font-black tracking-tight text-slate-950">Profil Saya</h2>
        </div>

        <button type="button"
                class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                x-on:click="profileOpen=false"
                aria-label="Close profile">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
               viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18" />
            <path d="M6 6l12 12" />
          </svg>
        </button>
      </div>

      {{-- body --}}
      <div class="flex-1 overflow-y-auto bg-gradient-to-b from-slate-100/95 via-slate-100/90 to-purple-100/55 px-6 py-6 space-y-6">

        @if($u)
          {{-- avatar + nama --}}
          <div class="rounded-[2rem] bg-white/18 p-6 text-center shadow-[0_14px_36px_rgba(15,23,42,.07)] ring-1 ring-white/35 backdrop-blur-md">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-violet-500 text-3xl font-black text-white shadow-lg shadow-purple-500/25 ring-4 ring-white/60">
              {{ $initial }}
            </div>
            <div class="mt-4 text-lg font-black text-slate-950">{{ $u->name }}</div>
            <div class="text-sm font-medium text-slate-600">{{ $u->email }}</div>
            @if($isAdminStorePreview)
              <div class="mx-auto mt-3 inline-flex rounded-full bg-white/55 px-3 py-1 text-xs font-semibold text-purple-700 shadow-sm ring-1 ring-white/60 backdrop-blur">
                Mode preview toko sebagai admin
              </div>
            @endif
          </div>

          @if(session('success') && session('profile_edit_guard') === $activeProfileGuard)
            <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
              {{ session('success') }}
            </div>
          @endif

          @if($errors->any() && session('profile_edit_guard') === $activeProfileGuard)
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              {{ $errors->first() }}
            </div>
          @endif

          {{-- Data diri --}}
          <div>
            <div class="flex items-center justify-between">
              <div class="font-extrabold text-slate-950">Data Diri</div>

              <button type="button"
                      x-on:click="profileEditOpen=true"
                      class="inline-flex items-center gap-2 text-sm font-semibold text-purple-600 hover:text-purple-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 20h9" />
                  <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                </svg>
                <span>Edit</span>
              </button>
            </div>

            <div class="mt-3 space-y-4 rounded-[1.75rem] bg-white/72 p-5 shadow-[0_12px_34px_rgba(15,23,42,.06)] ring-1 ring-white/60 backdrop-blur">
              <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                       fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21a8 8 0 0 0-16 0" />
                    <circle cx="12" cy="8" r="4" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Nama Lengkap</div>
                  <div class="font-extrabold text-slate-950">{{ $u->name }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                       fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="6" width="18" height="14" rx="2" />
                    <path d="m21 8-9 6-9-6" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Email</div>
                  <div class="font-extrabold text-slate-950">{{ $u->email }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                       fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.9 19.9 0 0 1-8.7-3.1 19.5 19.5 0 0 1-6-6A19.9 19.9 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.1 9.5a16 16 0 0 0 6.4 6.4l1.1-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Nomor Telepon</div>
                  <div class="font-extrabold text-slate-950">{{ $u->phone ?? '-' }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                       fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21s7-4.4 7-11a7 7 0 0 0-14 0c0 6.6 7 11 7 11Z" />
                    <circle cx="12" cy="10" r="2.5" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Alamat</div>
                  <div class="font-semibold text-slate-900 whitespace-pre-line">{{ $u->address ?? '-' }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                       fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="M16 2v4" />
                    <path d="M8 2v4" />
                    <path d="M3 10h18" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Member sejak</div>
                  <div class="font-extrabold text-slate-950">{{ optional($u->created_at)->translatedFormat('j F Y') }}</div>
                </div>
              </div>
            </div>
          </div>

          {{-- Menu --}}
          <div>
            <div class="font-extrabold text-slate-950">Menu</div>
            <div class="mt-3 overflow-hidden rounded-[1.75rem] bg-white/75 shadow-[0_12px_34px_rgba(15,23,42,.06)] ring-1 ring-white/60 backdrop-blur">

              @if($u->is_admin || $isAdminStorePreview)
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center justify-between px-5 py-4 transition hover:bg-purple-50">
                  <div class="flex items-center gap-3">
                    <span class="text-purple-600 inline-flex">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                           fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z" />
                      </svg>
                    </span>
                    <div>
                      <div class="font-extrabold text-slate-950">Admin Panel</div>
                      <div class="text-xs text-slate-500">Kembali ke dashboard admin</div>
                    </div>
                  </div>
                  <span class="text-slate-400 inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="m9 18 6-6-6-6" />
                    </svg>
                  </span>
                </a>

                <div class="h-px bg-gray-100"></div>
              @endif

              @if(!$isAdminStorePreview)
              <a href="{{ route('orders.index') }}"
                 class="flex items-center justify-between px-5 py-4 transition hover:bg-slate-50">
                <div class="flex items-center gap-3">
                  <span class="text-slate-500 inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                         fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                      <rect x="9" y="3" width="6" height="4" rx="1" />
                      <path d="M9 12h6" />
                      <path d="M9 16h6" />
                    </svg>
                  </span>
                  <span class="font-extrabold text-slate-950">Pesanan Saya</span>
                </div>
                <span class="text-slate-400 inline-flex">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                       fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6" />
                  </svg>
                </span>
              </a>

              <div class="h-px bg-gray-100"></div>

              <a href="{{ route('wishlist.index') }}"
                 class="flex items-center justify-between px-5 py-4 transition hover:bg-slate-50">
                <div class="flex items-center gap-3">
                  <span class="text-slate-500 inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                         fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/>
                    </svg>
                  </span>
                  <span class="font-extrabold text-slate-950">Wishlist</span>
                </div>
                <span class="text-slate-400 inline-flex">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                       fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6" />
                  </svg>
                </span>
              </a>
              @else
                <div class="px-4 py-4 text-sm text-slate-500">
                  Fitur pelanggan seperti Pesanan Saya dan Wishlist tidak ditampilkan dalam mode preview admin.
                </div>
              @endif
            </div>
          </div>

        @else
          {{-- Guest: tampilkan tombol login --}}
          <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center">
            <div class="font-bold text-slate-900">Kamu belum login</div>
            <div class="mt-1 text-sm text-slate-500">Login / Sign Up untuk melihat profil, wishlist, dan pesanan.</div>
            <button type="button"
                    class="mt-4 w-full rounded-2xl bg-gradient-to-r from-purple-600 to-violet-600 py-3.5 font-extrabold text-white shadow-lg shadow-purple-500/20 transition hover:-translate-y-0.5"
                    x-on:click="profileOpen=false; window.openAuthModal('login', '{{ url()->current() }}')">
              Login / Sign Up
            </button>
          </div>
        @endif

      </div>

      {{-- footer logout (di bawah) --}}
      @if($u)
        <div class="border-t border-slate-100 bg-white/95 px-6 py-5">
          <form method="POST" action="{{ $isAdminStorePreview ? route('admin.logout') : route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-500 py-3.5 font-extrabold text-white shadow-lg shadow-rose-500/20 transition hover:-translate-y-0.5 hover:bg-rose-500/90">
              <span class="inline-flex">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                  <path d="M16 17l5-5-5-5" />
                  <path d="M21 12H9" />
                </svg>
              </span>
              {{ $isAdminStorePreview ? 'Logout Admin' : 'Logout' }}
            </button>
          </form>
        </div>
      @endif

    </div>
  </div>

  @if($u)
    <div
      x-cloak
      x-show="profileEditOpen"
      class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-md"
      role="dialog"
      aria-modal="true"
      aria-labelledby="profile-edit-title"
      x-on:keydown.escape.window="profileEditOpen=false"
    >
      <div class="absolute inset-0" x-on:click="profileEditOpen=false"></div>

      <div class="relative w-full max-w-lg overflow-hidden rounded-[2.25rem] bg-white/95 shadow-[0_30px_90px_rgba(15,23,42,.24)] ring-1 ring-white/80 backdrop-blur-xl">
        <div class="flex items-center justify-between border-b border-slate-200/70 bg-gradient-to-r from-white/96 via-slate-50/95 to-purple-50/85 px-6 py-5 backdrop-blur-xl">
          <div>
            <h3 id="profile-edit-title" class="text-lg font-extrabold text-slate-900">Edit Data Diri</h3>
            <p class="mt-1 text-xs text-slate-500">
              Perubahan ini akan tersimpan ke akun {{ $isAdminStorePreview ? 'admin' : 'pelanggan' }}.
            </p>
          </div>
          <button type="button"
                  class="rounded-full bg-white/80 p-2 text-slate-500 shadow-sm ring-1 ring-slate-200/70 transition hover:bg-white hover:text-slate-800"
                  x-on:click="profileEditOpen=false"
                  aria-label="Tutup form edit profil">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 6 6 18" />
              <path d="M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form method="POST" action="{{ $profileUpdateRoute }}" class="space-y-4 px-6 py-6" style="background: linear-gradient(180deg, rgba(255,255,255,.96) 0%, rgba(248,250,252,.94) 48%, rgba(245,243,255,.82) 100%);">
          @csrf
          @method('PATCH')

          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Lengkap</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $u->name) }}"
                   required
                   maxlength="255"
                   class="w-full rounded-2xl border border-slate-200/85 bg-white/95 px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none backdrop-blur transition placeholder:text-slate-400 focus:border-purple-300 focus:bg-white focus:ring-4 focus:ring-purple-200/45">
          </div>

          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
            <input type="email"
                   name="email"
                   value="{{ old('email', $u->email) }}"
                   required
                   maxlength="255"
                   class="w-full rounded-2xl border border-slate-200/85 bg-white/95 px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none backdrop-blur transition placeholder:text-slate-400 focus:border-purple-300 focus:bg-white focus:ring-4 focus:ring-purple-200/45">
          </div>

          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Nomor Telepon</label>
            <input type="text"
                   name="phone"
                   value="{{ old('phone', $u->phone) }}"
                   maxlength="30"
                   class="w-full rounded-2xl border border-slate-200/85 bg-white/95 px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none backdrop-blur transition placeholder:text-slate-400 focus:border-purple-300 focus:bg-white focus:ring-4 focus:ring-purple-200/45">
          </div>

          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Kode Pos Pengiriman</label>
            <input type="text"
                   name="shipping_postal_code"
                   value="{{ old('shipping_postal_code', $u->shipping_postal_code) }}"
                   maxlength="20"
                   class="w-full rounded-2xl border border-slate-200/85 bg-white/95 px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none backdrop-blur transition placeholder:text-slate-400 focus:border-purple-300 focus:bg-white focus:ring-4 focus:ring-purple-200/45">
          </div>

          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Alamat</label>
            <textarea name="address"
                      rows="4"
                      maxlength="2000"
                      class="w-full rounded-2xl border border-slate-200/85 bg-white/95 px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none backdrop-blur transition placeholder:text-slate-400 focus:border-purple-300 focus:bg-white focus:ring-4 focus:ring-purple-200/45">{{ old('address', $u->address) }}</textarea>
          </div>

          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button"
                    x-on:click="profileEditOpen=false"
                    class="rounded-2xl bg-white/88 px-5 py-3 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200/80 backdrop-blur transition hover:bg-white">
              Batal
            </button>
            <button type="submit"
                    class="rounded-2xl bg-gradient-to-r from-purple-600 to-violet-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-purple-500/25 transition hover:-translate-y-0.5 hover:shadow-purple-500/35">
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

</div>