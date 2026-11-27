<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Admin Panel' }}</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    [x-cloak]{display:none!important}
    details summary::-webkit-details-marker{display:none}
  </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
  @php
    $adminUser = auth('admin')->user();

    $notificationsEnabled = $adminUser && \Illuminate\Support\Facades\Schema::hasTable('notifications');

    $notificationsRouteReady =
      \Illuminate\Support\Facades\Route::has('admin.notifications.feed') &&
      \Illuminate\Support\Facades\Route::has('admin.notifications.read') &&
      \Illuminate\Support\Facades\Route::has('admin.notifications.read-all') &&
      \Illuminate\Support\Facades\Route::has('admin.notifications.destroy') &&
      \Illuminate\Support\Facades\Route::has('admin.notifications.destroy-all');

    $adminUnreadCount = $notificationsEnabled ? $adminUser->unreadNotifications()->count() : 0;
    $adminNotificationCount = $notificationsEnabled ? $adminUser->notifications()->count() : 0;
    $adminNotifications = $notificationsEnabled ? $adminUser->notifications()->latest()->take(8)->get() : collect();

    $notificationsFeedUrl = $notificationsRouteReady ? route('admin.notifications.feed') : null;
    $notificationsReadAllUrl = $notificationsRouteReady ? route('admin.notifications.read-all') : null;
    $notificationsDeleteAllUrl = $notificationsRouteReady ? route('admin.notifications.destroy-all') : null;

    $firebasePushService = app(\App\Services\FirebaseCloudMessagingService::class);
    $firebasePushEnabled = $firebasePushService->webPushEnabled();
    $firebaseWebConfig = $firebasePushEnabled ? $firebasePushService->webConfig() : [];
    $firebaseVapidKey = $firebasePushEnabled ? config('services.firebase.web.vapid_key') : null;

    $adminPushTableReady = $adminUser && \Illuminate\Support\Facades\Schema::hasTable('admin_push_subscriptions');
    $adminPushRegistered = ($firebasePushEnabled && $adminPushTableReady) ? $adminUser->adminPushSubscriptions()->exists() : false;
    $adminPushSubscribeUrl = ($firebasePushEnabled && $adminPushTableReady && \Illuminate\Support\Facades\Route::has('admin.push.subscribe')) ? route('admin.push.subscribe') : null;
    $adminPushUnsubscribeUrl = ($firebasePushEnabled && $adminPushTableReady && \Illuminate\Support\Facades\Route::has('admin.push.unsubscribe')) ? route('admin.push.unsubscribe') : null;
    $firebaseServiceWorkerUrl = $firebasePushEnabled ? route('admin.firebase.messaging.sw') : null;
  @endphp

  <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
    <aside class="bg-slate-950 text-white p-6">
      <a href="{{ route('admin.dashboard') }}" class="text-xl font-extrabold">
        Admin<span class="text-purple-400">.</span>Purplestuff
      </a>

      <div class="mt-8 space-y-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
           class="block rounded-xl px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
          Dashboard
        </a>

        <a href="{{ route('admin.products.index') }}"
           class="block rounded-xl px-4 py-3 {{ request()->routeIs('admin.products.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
          Produk
        </a>

        <a href="{{ route('admin.artists.index') }}"
           class="block rounded-xl px-4 py-3 {{ request()->routeIs('admin.artists.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
          Artist
        </a>

        <a href="{{ route('admin.categories.index') }}"
           class="block rounded-xl px-4 py-3 {{ request()->routeIs('admin.categories.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
          Kategori
        </a>

        <a href="{{ route('admin.orders.index') }}"
           class="block rounded-xl px-4 py-3 {{ request()->routeIs('admin.orders.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
          Pesanan & Pembayaran
        </a>

        <a href="{{ route('admin.banners.index') }}"
           class="block rounded-xl px-4 py-3 {{ request()->routeIs('admin.banners.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
          Banner Dashboard
        </a>

        <a href="{{ route('dashboard') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-white/5 hover:text-white">
          Lihat Toko
          <span class="ml-1 text-xs text-slate-500">↗</span>
        </a>
      </div>

      @if($adminUser)
        <div class="mt-10 rounded-[1.5rem] bg-white/[0.07] p-4 text-sm text-slate-300 shadow-lg shadow-black/10 ring-1 ring-white/10">
          <div class="font-semibold text-white">{{ $adminUser->name }}</div>
          <div class="mt-1 text-slate-400">{{ $adminUser->email }}</div>

          @if(\Illuminate\Support\Facades\Route::has('admin.profile.update'))
            <details class="mt-4 rounded-2xl bg-white/[0.06] p-3 ring-1 ring-white/10" {{ session('profile_edit_open') && session('profile_edit_guard') === 'admin' ? 'open' : '' }}>
              <summary class="cursor-pointer text-sm font-extrabold text-purple-200 transition hover:text-white">
                Edit Profil Admin
              </summary>

              <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-4 space-y-3">
                @csrf
                @method('PATCH')

                <div>
                  <label class="mb-1 block text-xs font-semibold text-slate-300">Nama Lengkap</label>
                  <input type="text"
                         name="name"
                         value="{{ old('name', $adminUser->name) }}"
                         required
                         maxlength="255"
                         class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 outline-none transition focus:border-purple-300 focus:ring-2 focus:ring-purple-400/20">
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold text-slate-300">Email</label>
                  <input type="email"
                         name="email"
                         value="{{ old('email', $adminUser->email) }}"
                         required
                         maxlength="255"
                         class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 outline-none transition focus:border-purple-300 focus:ring-2 focus:ring-purple-400/20">
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold text-slate-300">Nomor Telepon</label>
                  <input type="text"
                         name="phone"
                         value="{{ old('phone', $adminUser->phone) }}"
                         maxlength="30"
                         class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 outline-none transition focus:border-purple-300 focus:ring-2 focus:ring-purple-400/20">
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold text-slate-300">Kode Pos</label>
                  <input type="text"
                         name="shipping_postal_code"
                         value="{{ old('shipping_postal_code', $adminUser->shipping_postal_code) }}"
                         maxlength="20"
                         class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 outline-none transition focus:border-purple-300 focus:ring-2 focus:ring-purple-400/20">
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold text-slate-300">Alamat</label>
                  <textarea name="address"
                            rows="3"
                            maxlength="2000"
                            class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 outline-none transition focus:border-purple-300 focus:ring-2 focus:ring-purple-400/20">{{ old('address', $adminUser->address) }}</textarea>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-purple-600 to-violet-600 px-4 py-2.5 font-extrabold text-white shadow-lg shadow-purple-900/20 transition hover:-translate-y-0.5">
                  Simpan Profil
                </button>
              </form>
            </details>
          @endif

          <form method="POST" action="{{ route('admin.logout') }}" class="mt-4">
            @csrf
            <button type="submit"
                    class="w-full rounded-xl bg-rose-500 px-4 py-2.5 font-extrabold text-white shadow-lg shadow-rose-950/20 transition hover:-translate-y-0.5 hover:bg-rose-400">
              Logout
            </button>
          </form>
        </div>
      @endif
    </aside>

    <main>
      <div class="border-b bg-white">
        <div class="mx-auto flex max-w-7xl items-start justify-between gap-4 px-6 py-5">
          <div>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ $heading ?? 'Admin Panel' }}</h1>
            @isset($subheading)
              <p class="mt-1 text-sm text-slate-500">{{ $subheading }}</p>
            @endisset
          </div>

          @if($notificationsEnabled && $notificationsRouteReady)
            <details id="adminNotificationDropdown" class="relative">
              <summary id="adminNotificationBell" class="list-none cursor-pointer">
                <div class="relative flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm hover:bg-slate-50">
                  <svg xmlns="http://www.w3.org/2000/svg"
                       class="h-5 w-5 text-slate-700"
                       viewBox="0 0 24 24"
                       fill="none"
                       stroke="currentColor"
                       stroke-width="1.8"
                       stroke-linecap="round"
                       stroke-linejoin="round">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                    <path d="M10 21a2 2 0 0 0 4 0"></path>
                  </svg>

                  <span id="adminNotificationBadge"
                        class="absolute -right-1 -top-1 {{ $adminUnreadCount > 0 ? 'flex' : 'hidden' }} h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                    {{ $adminUnreadCount }}
                  </span>
                </div>
              </summary>

              <div class="absolute right-0 z-50 mt-3 w-[380px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                  <div>
                    <div class="font-bold text-slate-900">Notifikasi Admin</div>
                    <div class="text-xs text-slate-500">
                      <span id="adminUnreadText">{{ $adminUnreadCount }}</span> belum dibaca
                    </div>
                    <div id="adminPushStatus" class="mt-1 text-[11px] text-slate-500">
                      @if(!$firebasePushEnabled)
                        Push background belum dikonfigurasi di server.
                      @elseif($adminPushRegistered)
                        Push browser sudah aktif di perangkat ini.
                      @else
                        Push browser belum aktif di perangkat ini.
                      @endif
                    </div>
                  </div>

                  <div class="flex flex-col items-end gap-2">
                    <button id="adminPushToggleButton"
                            type="button"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 {{ $firebasePushEnabled ? '' : 'cursor-not-allowed opacity-50' }}"
                            {{ $firebasePushEnabled ? '' : 'disabled' }}>
                      {{ $adminPushRegistered ? 'Perbarui Notif Browser' : 'Aktifkan Notif Browser' }}
                    </button>

                    <form method="POST" action="{{ $notificationsReadAllUrl }}">
                      @csrf
                      <button id="adminReadAllButton"
                              type="submit"
                              class="text-xs font-semibold text-purple-600 hover:text-purple-500 {{ $adminUnreadCount > 0 ? '' : 'hidden' }}">
                        Tandai semua dibaca
                      </button>
                    </form>

                    <form method="POST"
                          action="{{ $notificationsDeleteAllUrl }}"
                          onsubmit="return confirm('Hapus semua notifikasi admin? Tindakan ini tidak bisa dibatalkan.');">
                      @csrf
                      @method('DELETE')
                      <button id="adminDeleteAllButton"
                              type="submit"
                              class="text-xs font-semibold text-rose-600 hover:text-rose-500 {{ $adminNotificationCount > 0 ? '' : 'hidden' }}">
                        Hapus semua
                      </button>
                    </form>
                  </div>
                </div>

                <div id="adminNotificationList" class="max-h-[420px] overflow-y-auto p-3">
                  @forelse($adminNotifications as $notification)
                    <div class="mb-3 rounded-2xl border p-3 {{ is_null($notification->read_at) ? 'border-purple-200 bg-purple-50/50' : 'border-slate-100 bg-slate-50' }}">
                      <div class="text-sm font-semibold text-slate-900">
                        {{ $notification->data['message'] ?? 'Notifikasi baru' }}
                      </div>

                      <div class="mt-1 text-xs text-slate-500">
                        {{ $notification->created_at->diffForHumans() }}
                      </div>

                      @if(!empty($notification->data['order_number']))
                        <div class="mt-2 text-xs text-slate-600">
                          Order: <span class="font-semibold">{{ $notification->data['order_number'] }}</span>
                        </div>
                      @endif

                      <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if(!empty($notification->data['link']))
                          <a href="{{ $notification->data['link'] }}"
                             class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                            Lihat Order
                          </a>
                        @endif

                        @if(is_null($notification->read_at))
                          <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-white">
                              Tandai dibaca
                            </button>
                          </form>
                        @endif

                        <form method="POST"
                              action="{{ route('admin.notifications.destroy', $notification->id) }}"
                              onsubmit="return confirm('Hapus notifikasi ini?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit"
                                  class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                            Hapus
                          </button>
                        </form>
                      </div>
                    </div>
                  @empty
                    <div id="adminNotificationEmpty"
                         class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                      Belum ada notifikasi.
                    </div>
                  @endforelse
                </div>
              </div>
            </details>
          @endif
        </div>
      </div>

      <div class="mx-auto max-w-7xl px-6 py-6">
        @if(session('success'))
          <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
          </div>
        @endif

        @if($errors->any())
          <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
          </div>
        @endif

        @yield('content')
      </div>
    </main>
  </div>

  @include('layouts.partials.admin-notification-scripts')
</body>
</html>