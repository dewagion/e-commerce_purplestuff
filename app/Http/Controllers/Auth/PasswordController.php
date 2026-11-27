<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function request(Request $request)
    {
        $redirect = $this->sanitizeRedirect(
            $request->query('redirect') ?: url()->previous() ?: route('dashboard'),
            $request
        );

        return redirect()->to($redirect)->with([
            'auth_open' => true,
            'auth_tab' => 'forgot',
            'auth_redirect' => $redirect,
        ]);
    }

    public function email(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $redirect = $this->sanitizeRedirect(
            $request->input('redirect') ?: $request->query('redirect') ?: route('dashboard'),
            $request
        );

        $flash = [
            'auth_open' => true,
            'auth_tab' => 'forgot',
            'auth_redirect' => $redirect,
        ];

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with($flash)
                ->withErrors([
                    'email' => 'Gagal mengirim email reset (SMTP). Periksa konfigurasi MAIL_HOST/MAIL_PORT/MAIL_USERNAME/MAIL_PASSWORD di .env.',
                ])
                ->withInput();
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with($flash)->with('status', __($status))
            : back()->with($flash)->withErrors(['email' => __($status)])->withInput();
    }

    public function reset(string $token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    protected function sanitizeRedirect(?string $redirect, Request $request): string
    {
        if (!$redirect) {
            return route('dashboard');
        }

        if (str_contains($redirect, '/forgot-password')) {
            return route('dashboard');
        }

        $parsed = parse_url($redirect);

        if (isset($parsed['host']) && $parsed['host'] !== $request->getHost()) {
            return route('dashboard');
        }

        if (str_starts_with($redirect, '//') || str_starts_with($redirect, '\\\\')) {
            return route('dashboard');
        }

        return $redirect;
    }
}