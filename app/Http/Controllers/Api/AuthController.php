<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationEmail;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    
    // Register User baru

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'nama_lengkap' => 'required|string|max:100',
                'no_hp' => 'nullable|string|max:15',
                'alamat' => 'nullable|string',
            ], [
                'username.required' => 'Username wajib diisi',
                'username.unique' => 'Username sudah digunakan',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
                'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            ]);
            
            //Generated verified token
            $verificationToken = Str::random(64);

            //Create User
            $user = User::create([
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'email' => $validated['email'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_hp' => $validated['no_hp'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'role' => 'penyewa',
                'status' => 'nonaktif', 
                'email_verified_at' => null,
                'verification_token' => $verificationToken,
            ]);

            //send verfication email
            $verificationUrl = config('app.url') . "app/verify-email?token={verificationToken}";
            Mail::to($user->email)->send(new VerificationEmail($user, $verificationUrl));

            //log 
            LogAktivitas::log(
                $user->id,
                'REGISTER',
                "User baru mendaftar: {$user->email}",
                'user',
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun.',
                'data' => [
                    'email' => $user->email,
                ]
            ], 201);

    
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' .$e->getMessage(),
            ], 500);
        }
    }

    // Verify email

    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token verifikasi tidak ditemukan',
            ], 400);
        }

        // Cari user berdasarkan token
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kadaluarsa',
            ], 404);
        }

        // Cek apakah sudah terverikasi
        if ($user->email_verified_at !== null) {
            return response()->json([
                'success' => true,
                'message' => 'Email sudah diverifikasi sebelumnya',
            ], 200);
        }

        // update user
        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
            'status' => 'aktif',
        ]);

        // Auto-Generate token auth
        $authToken = $user->createToken('auth_token')->plainTextToken;

        LogAktivitas::log(
            $user->id,
            'EMAIL_VERIFIED',
            'User berhasil verifikasi email'
        );

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi! Anda akan diarahkan ke dashboard.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role,
                    'foto' => $user->foto,
                ],
                'token' => $authToken,
            ]
        ], 200);
    }

    // resend email verifikasi
    public function resendVerification(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan',
                ], 404);
            }

            if ($user->email_verified_at !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email sudah diverifikasi',
                ], 400);
            }

            // generate token baru
            $verificationToken = Str::random(64);
            $user->update(['verification_token' => $verificationToken]);

            //resend email
            $verificationUrl = config('app.url') . "api/verify-email?token={$verificationToken}";
            Mail::to($user->email)->send(new VerificationEmail($user, $verificationUrl));

            return response()->json([
                'success' => true,
                'message' => 'Email verifikasi telah dikirim ulang. Silakan cek inbox Anda.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Login
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'login' => 'required|string',
                'password' => 'required|string',
            ], [
                'login.required' => 'Username atau email wajib diisi',
                'password.required' => 'Password wajib diisi',
            ]);

            // mencari akun berdasarkan username atau email
            $user = User::where('username', $validated['login'])
                        ->orWhere('email', $validated['login'])
                        ->first();

            //cek apakah user ada
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau email tidak ditemukan',
                ], 401);
            }

            // cek jika user menggunakan Oauth
            if ($user->isOAuthUser() && $user->password === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini menggunakan login dengan Google. Silakan gunakan tombol "Sign in with Google".',
                ], 400);
            }

            // verify password
            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password salah',
                ], 401);
            }

            if (!$user->isEmailVerified()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email belum diverifikasi. Silakan cek inbox atau kirim ulang email verifikasi.',
                    'data' => [
                        'email' => $user->email,
                        'need_verification' => true,
                    ]
                ], 403);
            }

            // cek status
            if (!$user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                ], 403);
            }

            // generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            // log aktivitas
            LogAktivitas::log(
                $user->id,
                'LOGIN',
                'User login ke sistem'
            );

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'nama_lengkap' => $user->nama_lengkap,
                        'role' => $user->role,
                        'foto' => $user->foto,
                        'no_hp' => $user->no_hp,
                    ],
                    'token' => $token,
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // get current user
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ], 200);
    }

    // logout
    public function logout(Request $request)
    {
        // Log activity
        LogAktivitas::log(
            $request->user()->id,
            'LOGOUT',
            'User logout dari sistem'
        );

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ], 200);
    }
}
