<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartPersistenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request, CartPersistenceService $cartService)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'redirect' => ['nullable', 'string'],
        ]);

        $guestCart = session('cart', []);
        $guestCart = is_array($guestCart) ? $guestCart : [];

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $cart = $cartService->mergeSessionCartIntoUserCart($user, $guestCart);
        $request->session()->put('cart', $cart);
        $request->session()->put(CartPersistenceService::LOADED_KEY, true);
        $request->session()->forget(['checkout_selected_keys', 'checkout_shipping_options']);

        $to = $data['redirect'] ?? route('dashboard');

        if ($this->isUnsafeRedirect($to)) {
            $to = route('dashboard');
        }

        return redirect()->to($to);
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
