<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update profil pelanggan yang login dengan guard web.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user('web');

        if (!$user) {
            abort(403);
        }

        return $this->updateUserProfile($request, $user, 'web');
    }

    /**
     * Update profil admin yang login dengan guard admin.
     */
    public function updateAdmin(Request $request): RedirectResponse
    {
        $user = $request->user('admin');

        if (!$user || !$user->is_admin) {
            abort(403);
        }

        return $this->updateUserProfile($request, $user, 'admin');
    }

    protected function updateUserProfile(Request $request, User $user, string $guard): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah digunakan akun lain.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
            'address.max' => 'Alamat maksimal 2000 karakter.',
            'shipping_postal_code.max' => 'Kode pos maksimal 20 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('profile_open', true)
                ->with('profile_edit_open', true)
                ->with('profile_edit_guard', $guard);
        }

        $data = $validator->validated();

        $user->forceFill([
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'phone' => $this->nullableTrim($data['phone'] ?? null),
            'address' => $this->nullableTrim($data['address'] ?? null),
            'shipping_postal_code' => $this->nullableTrim($data['shipping_postal_code'] ?? null),
        ])->save();

        return back()
            ->with('success', 'Data diri berhasil diperbarui.')
            ->with('profile_open', true)
            ->with('profile_edit_guard', $guard);
    }

    protected function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
