<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Tampilkan halaman profil
    public function show()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    // Update profil (nama aja)
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
        ], [
            'nama_lengkap.required' => 'Nama harus diisi.',
        ]);

        $user->update([
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    // Tampilkan form ganti password
    public function passwordForm()
    {
        return view('profile-password');
    }

    // Update password
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|min:6',
            'password'         => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.min'      => 'Password lama minimal 6 karakter.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 6 karakter.',
            'password.confirmed'        => 'Konfirmasi password baru tidak sesuai.',
        ]);

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai.'
            ])->withInput();
        }

        // Set password baru
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Opsional: logout biar lebih aman
        // Auth::logout();
        // return redirect()->route('login')->with('success', 'Password berhasil diubah, silakan login ulang.');

        return redirect()->route('profile.show')->with('success', 'Password berhasil diubah.');
    }
}
