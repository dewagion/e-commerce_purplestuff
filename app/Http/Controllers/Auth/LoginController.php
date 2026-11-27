<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartPersistenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        $redirect = $request->query('redirect', url()->previous() ?: route('dashboard'));

        if ($this->isUnsafeRedirect($redirect)) {
            $redirect = route('dashboard');
        }

        return redirect()->route('dashboard')->with([
            'auth_open' => true,
            'auth_tab' => 'login',
            'auth_redirect' => $redirect,
        ]);
    }

    public function store(Request $request, CartPersistenceService $cartService)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'redirect' => ['nullable', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->except('password'));
        }

        $guestCart = session('cart', []);
        $guestCart = is_array($guestCart) ? $guestCart : [];

        if ($user->is_admin) {
            // Pastikan satu browser tidak menyimpan login pelanggan dan admin sekaligus.
            // Ini mencegah status login terlihat berubah-ubah saat berpindah fitur/guard.
            Auth::guard('web')->logout();
            Auth::guard('admin')->login($user, $remember);
        } else {
            // Jika sebelumnya admin login di browser yang sama, keluarkan guard admin
            // sebelum masuk sebagai pelanggan biasa.
            Auth::guard('admin')->logout();
            Auth::guard('web')->login($user, $remember);
        }

        $request->session()->regenerate();

        if (!$user->is_admin) {
            // Keranjang dari sebelum login digabung dengan keranjang tersimpan milik user.
            // Setelah itu disimpan kembali ke session agar cart drawer langsung terisi.
            $cart = $cartService->mergeSessionCartIntoUserCart($user, $guestCart);

            $request->session()->put('cart', $cart);
            $request->session()->put(CartPersistenceService::LOADED_KEY, true);
            $request->session()->forget(['checkout_selected_keys', 'checkout_shipping_options']);
        }

        $redirect = $data['redirect'] ?? null;

        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        if ($redirect && !$this->isUnsafeRedirect($redirect)) {
            return redirect($redirect);
        }

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request, CartPersistenceService $cartService)
    {
        if ($user = Auth::guard('web')->user()) {
            $cart = session('cart', []);
            $cartService->storeCartForUser($user, is_array($cart) ? $cart : []);
        }

        Auth::guard('web')->logout();

        if (!Auth::guard('admin')->check() && !Auth::guard('web')->check()) {
            $request->session()->invalidate();
        } else {
            $request->session()->migrate(true);
        }

        $request->session()->regenerateToken();

        return redirect()->route('dashboard');
    }

    public function destroyAdmin(Request $request)
    {
        Auth::guard('admin')->logout();

        if (!Auth::guard('admin')->check() && !Auth::guard('web')->check()) {
            $request->session()->invalidate();
        } else {
            $request->session()->migrate(true);
        }

        $request->session()->regenerateToken();

        return redirect()->route('dashboard');
    }

    protected function isUnsafeRedirect(?string $url): bool
    {
        if (!$url) {
            return true;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return true;
        }

        if (str_starts_with($url, '//') || str_starts_with($url, '\\')) {
            return true;
        }

        return false;
    }
}
