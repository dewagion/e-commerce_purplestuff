<div
  x-data="{
    open: {{ (session('auth_open') || (isset($openAuthModal) && $openAuthModal) || $errors->has('email') || $errors->has('password') || $errors->has('name') || $errors->has('phone') || $errors->has('address') || $errors->has('password_confirmation')) ? 'true' : 'false' }},
    tab: '{{ session("auth_tab") ?? ($authTab ?? (($errors->has("name") || $errors->has("phone") || $errors->has("address") || $errors->has("password_confirmation")) ? "signup" : "login")) }}',
    redirect: '{{ session("auth_redirect") ?? ($redirect ?? url()->current()) }}',
    showLoginPass: false,
    showRegPass: false,
    showRegPass2: false
  }"
  x-init="document.addEventListener('auth:open', (e) => {
      tab = (e.detail && e.detail.tab) ? e.detail.tab : tab;
      if (e.detail && e.detail.redirect) redirect = e.detail.redirect;
      open = true;
    });"
  x-on:keydown.escape.window="open=false"
  x-cloak
>
  <style>
    .ps-auth-panel input:-webkit-autofill,
    .ps-auth-panel textarea:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 1000px rgba(255,255,255,.96) inset !important;
      -webkit-text-fill-color: rgb(15,23,42) !important;
      caret-color: rgb(15,23,42);
    }
  </style>

  <div x-show="open" class="fixed inset-0 z-[99999] flex items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" x-on:click="open=false"></div>

    <div class="ps-auth-panel relative w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white/95 shadow-[0_30px_90px_rgba(15,23,42,.28)] ring-1 ring-white/60 backdrop-blur">
      <button class="absolute right-5 top-5 rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
              type="button"
              x-on:click="open=false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6 6 18"/>
          <path d="M6 6l12 12"/>
        </svg>
      </button>

      <div class="p-6 sm:p-8">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-purple-100 text-purple-700 shadow-inner">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6c-1.5-1.6-4-1.6-5.5 0L12 7.9 8.7 4.6c-1.5-1.6-4-1.6-5.5 0-1.6 1.6-1.6 4.2 0 5.8l3.1 3.2L12 20l5.7-6.4 3.1-3.2c1.6-1.6 1.6-4.2 0-5.8z"/></svg>
          </div>
          <div>
            <div class="text-2xl font-black tracking-tight text-slate-950">Welcome to Purple Stuff</div>
            <div class="mt-1 text-sm leading-6 text-slate-500">Login atau buat akun baru untuk melanjutkan belanja merch favoritmu.</div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="mt-6 flex gap-1 rounded-full bg-slate-100/90 p-1.5 shadow-inner">
          <button type="button"
                  class="flex-1 rounded-full py-2.5 text-sm font-extrabold transition"
                  :class="tab==='login' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  x-on:click="tab='login'">
            Login
          </button>

          <button type="button"
                  class="flex-1 rounded-full py-2.5 text-sm font-extrabold transition"
                  :class="tab==='signup' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  x-on:click="tab='signup'">
            Sign Up
          </button>
        </div>

        <!-- Body -->
        <div class="mt-6 max-h-[70vh] overflow-y-auto pr-1">
          <!-- LOGIN -->
          <div x-show="tab==='login'" x-cloak>
            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
              @csrf
              <input type="hidden" name="redirect" :value="redirect">

              <div>
                <label class="text-sm font-bold text-slate-800">Email</label>
                <input name="email" type="email" value="{{ old('email') }}"
                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                       placeholder="email@example.com" required>
                @error('email')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="text-sm font-bold text-slate-800">Password</label>
                <div class="relative mt-2">
                  <input name="password" :type="showLoginPass ? 'text' : 'password'"
                         class="w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 pr-12 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                         placeholder="••••••••" required>
                  <button type="button"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-800"
                          x-on:click="showLoginPass = !showLoginPass"
                          :aria-label="showLoginPass ? 'Hide password' : 'Show password'">
                    <span x-show="showLoginPass" x-cloak>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </span>
                    <span x-show="!showLoginPass" x-cloak>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3l18 18"/>
                        <path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/>
                        <path d="M9.9 5.1A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a17.5 17.5 0 0 1-3.1 4.3"/>
                        <path d="M6.1 6.1C3.9 7.8 2 12 2 12s3.5 7 10 7c1 0 2-.2 2.9-.5"/>
                      </svg>
                    </span>
                  </button>
                </div>
                @error('password')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                  <input type="checkbox" name="remember" class="rounded border-slate-300">
                  Remember me
                </label>

                <button type="button"
                        class="text-sm font-semibold text-purple-600 hover:text-purple-500"
                        x-on:click="tab='forgot'">
                  Forgot password?
                </button>
              </div>

              <button class="w-full rounded-2xl bg-gradient-to-r from-purple-600 to-violet-600 py-3.5 font-extrabold text-white shadow-lg shadow-purple-500/20 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-500/25">
                Login
              </button>
            </form>
          </div>

          <!-- SIGN UP -->
          <div x-show="tab==='signup'" x-cloak>
            <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
              @csrf
              <input type="hidden" name="redirect" :value="redirect">

              <div>
                <label class="text-sm font-bold text-slate-800">Nama Lengkap *</label>
                <input name="name" type="text" value="{{ old('name') }}"
                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                       placeholder="John Doe" required>
                @error('name')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="text-sm font-bold text-slate-800">Email *</label>
                <input name="email" type="email" value="{{ old('email') }}"
                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                       placeholder="email@example.com" required>
                @error('email')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="text-sm font-bold text-slate-800">Nomor Telepon *</label>
                <input name="phone" type="text" value="{{ old('phone') }}"
                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                       placeholder="08123456789" required>
                @error('phone')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="text-sm font-bold text-slate-800">Alamat Lengkap *</label>
                <textarea name="address" rows="3"
                          class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                          placeholder="Jl. Contoh No. 123, RT/RW 01/02, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos"
                          required>{{ old('address') }}</textarea>
                @error('address')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="text-sm font-bold text-slate-800">Password *</label>
                <div class="relative mt-2">
                  <input name="password" :type="showRegPass ? 'text' : 'password'"
                         class="w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 pr-12 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                         placeholder="••••••••" required>
                  <button type="button"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-800"
                          x-on:click="showRegPass = !showRegPass"
                          :aria-label="showRegPass ? 'Hide password' : 'Show password'">
                    <span x-show="showRegPass" x-cloak>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </span>
                    <span x-show="!showRegPass" x-cloak>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3l18 18"/>
                        <path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/>
                        <path d="M9.9 5.1A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a17.5 17.5 0 0 1-3.1 4.3"/>
                        <path d="M6.1 6.1C3.9 7.8 2 12 2 12s3.5 7 10 7c1 0 2-.2 2.9-.5"/>
                      </svg>
                    </span>
                  </button>
                </div>
                <div class="mt-2 text-xs text-slate-500">Minimal 6 karakter</div>
                @error('password')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="text-sm font-bold text-slate-800">Konfirmasi Password *</label>
                <div class="relative mt-2">
                  <input name="password_confirmation" :type="showRegPass2 ? 'text' : 'password'"
                         class="w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 pr-12 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                         placeholder="••••••••" required>
                  <button type="button"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-800"
                          x-on:click="showRegPass2 = !showRegPass2"
                          :aria-label="showRegPass2 ? 'Hide password' : 'Show password'">
                    <span x-show="showRegPass2" x-cloak>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </span>
                    <span x-show="!showRegPass2" x-cloak>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3l18 18"/>
                        <path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/>
                        <path d="M9.9 5.1A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a17.5 17.5 0 0 1-3.1 4.3"/>
                        <path d="M6.1 6.1C3.9 7.8 2 12 2 12s3.5 7 10 7c1 0 2-.2 2.9-.5"/>
                      </svg>
                    </span>
                  </button>
                </div>
              </div>

              <button class="w-full rounded-2xl bg-gradient-to-r from-purple-600 to-violet-600 py-3.5 font-extrabold text-white shadow-lg shadow-purple-500/20 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-500/25">
                Buat Akun
              </button>

              <div class="text-xs text-slate-500 text-center">
                Dengan mendaftar, Anda menyetujui syarat & ketentuan kami
              </div>
            </form>
          </div>

          <!-- FORGOT PASSWORD -->
          <div x-show="tab==='forgot'" x-cloak>
            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
              @csrf
              <input type="hidden" name="redirect" :value="redirect">

              <div class="text-sm text-slate-600">
                Masukkan email akun kamu. Kami akan kirim link untuk reset password.
              </div>

              @if (session('status'))
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 font-semibold">
                  {{ session('status') }}
                </div>
              @endif

              <div>
                <label class="text-sm font-bold text-slate-800">Email</label>
                <input name="email" type="email" value="{{ old('email') }}"
                      class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-purple-300 focus:ring-4 focus:ring-purple-100"
                      placeholder="email@example.com" required>
                @error('email')
                  <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
              </div>

              <button class="w-full rounded-2xl bg-gradient-to-r from-purple-600 to-violet-600 py-3.5 font-extrabold text-white shadow-lg shadow-purple-500/20 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-500/25">
                Kirim Link Reset
              </button>

              <button type="button"
                      class="w-full rounded-2xl bg-slate-100 py-3 font-extrabold text-slate-900 transition hover:bg-slate-200"
                      x-on:click="tab='login'">
                Kembali ke Login
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>