<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    // redirect ke Google Auth

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    // handle untuk Google OAuth Callback
    public function handleGoogleCallback()
    {
        try {
            // mendapatkan user dari Google
            $googleUser = Socialite::driver('google')->stateless()->user();;

            // cek apakah user dengan id google ini sudah ada
            $user = User::where('google_id', $googleUser->id)->first();

            $isNewUser = false;

            if ($user) {
                // User dengan akun google yang ada
                $message = 'Login berhasil dengan Google';
                
            } else {
                // cek apakah email terdaftar
                $existingUser = User::where('email', $googleUser->email)->first();

                if ($existingUser) {
        
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                        'oauth_provider' => 'google',
                        'email_verified_at' => now(), 
                        'foto' => $googleUser->avatar ?? $existingUser->foto,
                        'status' => 'aktif',
                    ]);
                    $user = $existingUser;
                    $message = 'Akun Google berhasil ditautkan';
                } else {
                    $user = User::create([
                        'google_id' => $googleUser->id,
                            'email' => $googleUser->email,
                            'nama_lengkap' => $googleUser->name,
                            'foto' => $googleUser->avatar,
                            'oauth_provider' => 'google',
                            'role' => 'penyewa',
                            'status' => 'aktif',
                            'email_verified_at' => now(), 
                            'password' => null, 
                            'username' => null,
                    ]);
                    $isNewUser = true;
                    $message = 'Akun berhasil dibuat dengan Google';
                }

                $token = $user->createToken('auth_token')->plainTextToken;
            
                LogAktivitas::log(
                    $user->id,
                    $isNewUser ? 'REGISTER_GOOGLE' : 'LOGIN_GOOGLE',
                    $message
                );

                // Redirect ke frontend menggunakan token
                $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
                $isNewParam = $isNewUser ? 'true' : 'false';
                $redirectUrl = "{$frontendUrl}/auth/callback?token={$token}&is_new={$isNewParam}";

                return redirect($redirectUrl);
                
        } } catch (\Exception $e) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $errorMessage = urlencode('Google OAuth gagal: ' . $e->getMessage());
            return redirect("{$frontendUrl}/login?error={$errorMessage}");
        }
    }
}
