<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $redirect = fn () => redirect()->to(url()->previous() . '#footer-newsletter');

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum valid.',
            'email.max' => 'Email terlalu panjang.',
        ]);

        if ($validator->fails()) {
            return $redirect()
                ->withErrors($validator)
                ->withInput();
        }

        $email = Str::lower(trim($request->input('email')));

        try {
            $subscriber = NewsletterSubscriber::updateOrCreate(
                ['email' => $email],
                ['subscribed_at' => now()]
            );

            $message = $subscriber->wasRecentlyCreated
                ? 'Email berhasil disimpan. Kamu akan mendapat info promo, restock, atau update toko jika fitur newsletter digunakan.'
                : 'Email ini sudah terdaftar. Data subscriber berhasil diperbarui.';

            return $redirect()->with('newsletter_success', $message);
        } catch (\Throwable $e) {
            Log::error('Newsletter subscription failed.', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return $redirect()
                ->withInput()
                ->with('newsletter_error', 'Email belum berhasil disimpan. Silakan coba lagi.');
        }
    }
}
