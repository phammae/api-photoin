<?php

namespace App\Services;

use App\Mail\VerificationEmail;
use App\Models\LogAktivitas;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    // register
    public function register(array $data): array
    {
        $verificationToken = Str::random(64);

        $user = $this->userRepository->create([
            'username'           => $data['username'],
            'password'           => Hash::make($data['password']),
            'email'              => $data['email'],
            'nama_lengkap'       => $data['nama_lengkap'],
            'no_hp'              => $data['no_hp'] ?? null,
            'alamat'             => $data['alamat'] ?? null,
            'role'               => 'penyewa',
            'status'             => 'nonaktif',
            'email_verified_at'  => null,
            'verification_token' => $verificationToken,
        ]);

        // Kirim email verifikasi
        $this->sendVerificationEmail($user, $verificationToken);

        // Log aktivitas
        LogAktivitas::log(
            $user->id,
            'REGISTER',
            "User baru mendaftar: {$user->email}",
            'users',
            $user->id
        );

        return [
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun.',
            'data'    => [
                'email' => $user->email,
            ]
        ];
    }

    // login
    public function login(array $data): array
    {
        $user = $this->userRepository->findByUsernameOrEmail($data['login']);

        if (!$user) {
            throw new \Exception('Username atau email tidak ditemukan', 401);
        }

        // Cek jika user OAuth tapi login biasa
        if ($user->isOAuthUser() && $user->password === null) {
            throw new \Exception(
                'Akun ini menggunakan login dengan Google. Silakan gunakan tombol "Sign in with Google".',
                400
            );
        }

        // Verifikasi password
        if (!Hash::check($data['password'], $user->password)) {
            throw new \Exception('Password salah', 401);
        }

        // Cek email verified
        if (!$user->isEmailVerified()) {
            return [
                'success' => false,
                'message' => 'Email belum diverifikasi. Silakan cek inbox atau kirim ulang email verifikasi.',
                'data'    => [
                    'email'             => $user->email,
                    'need_verification' => true,
                ],
                'status_code' => 403,
            ];
        }

        // Cek status aktif
        if (!$user->isActive()) {
            throw new \Exception('Akun Anda telah dinonaktifkan. Hubungi administrator.', 403);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Log aktivitas
        LogAktivitas::log(
            $user->id,
            'LOGIN',
            'User login ke sistem',
            'users',
            $user->id
        );

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'data'    => [
                'user' => [
                    'id'           => $user->id,
                    'username'     => $user->username,
                    'email'        => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role'         => $user->role,
                    'foto'         => $user->foto,
                    'no_hp'        => $user->no_hp,
                ],
                'token' => $token,
            ],
            'status_code' => 200,
        ];
    }

    // verification email
    public function verifyEmail(string $token): array
    {
        $user = $this->userRepository->findByVerificationToken($token);

        if (!$user) {
            throw new \Exception('Token tidak valid atau sudah kadaluarsa', 404);
        }

        // Cek apakah sudah terverifikasi
        if ($user->isEmailVerified()) {
            return [
                'success' => true,
                'message' => 'Email sudah diverifikasi sebelumnya',
                'status_code' => 200,
            ];
        }

        // Verifikasi email
        $this->userRepository->verifyEmail($user);

        // Generate token auth
        $authToken = $user->createToken('auth_token')->plainTextToken;

        LogAktivitas::log(
            $user->id,
            'EMAIL_VERIFIED',
            'User berhasil verifikasi email',
            'users',
            $user->id
        );

        return [
            'success' => true,
            'message' => 'Email berhasil diverifikasi! Anda akan diarahkan ke dashboard.',
            'data'    => [
                'user' => [
                    'id'           => $user->id,
                    'username'     => $user->username,
                    'email'        => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role'         => $user->role,
                    'foto'         => $user->foto,
                ],
                'token' => $authToken,
            ],
            'status_code' => 200,
        ];
    }

// resend email verification
    public function resendVerification(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('Email tidak ditemukan', 404);
        }

        if ($user->isEmailVerified()) {
            throw new \Exception('Email sudah diverifikasi', 400);
        }

        // Generate token baru
        $verificationToken = Str::random(64);
        $this->userRepository->updateVerificationToken($user, $verificationToken);

        // Kirim email
        $this->sendVerificationEmail($user, $verificationToken);

        return [
            'success'     => true,
            'message'     => 'Email verifikasi telah dikirim ulang. Silakan cek inbox Anda.',
            'status_code' => 200,
        ];
    }

    // logout
    public function logout(User $user): array
    {
        LogAktivitas::log(
            $user->id,
            'LOGOUT',
            'User logout dari sistem',
            'users',
            $user->id
        );

        $user->currentAccessToken()->delete();

        return [
            'success'     => true,
            'message'     => 'Logout berhasil',
            'status_code' => 200,
        ];
    }

    // handle google Auth
    public function handleGoogleCallback($googleUser): array
    {
        $isNewUser = false;
        $message   = '';

        // Cek apakah sudah ada user dengan google_id ini
        $user = $this->userRepository->findByGoogleId($googleUser->id);

        if ($user) {
            $message = 'Login berhasil dengan Google';
        } else {
            // Cek apakah email sudah terdaftar
            $existingUser = $this->userRepository->findByEmail($googleUser->email);

            if ($existingUser) {
                // Link Google ke akun existing
                $this->userRepository->update($existingUser, [
                    'google_id'         => $googleUser->id,
                    'oauth_provider'    => 'google',
                    'email_verified_at' => $existingUser->email_verified_at ?? now(),
                    'foto'              => $googleUser->avatar ?? $existingUser->foto,
                    'status'            => 'aktif',
                ]);
                $user    = $existingUser;
                $message = 'Akun Google berhasil ditautkan';
            } else {
                // Buat akun baru
                $user = $this->userRepository->create([
                    'google_id'         => $googleUser->id,
                    'email'             => $googleUser->email,
                    'nama_lengkap'      => $googleUser->name,
                    'foto'              => $googleUser->avatar,
                    'oauth_provider'    => 'google',
                    'role'              => 'penyewa',
                    'status'            => 'aktif',
                    'email_verified_at' => now(),
                    'password'          => null,
                    'username'          => null,
                ]);
                $isNewUser = true;
                $message   = 'Akun berhasil dibuat dengan Google';
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        LogAktivitas::log(
            $user->id,
            $isNewUser ? 'REGISTER_GOOGLE' : 'LOGIN_GOOGLE',
            $message,
            'users',
            $user->id
        );

        return [
            'token'     => $token,
            'is_new'    => $isNewUser,
            'message'   => $message,
        ];
    }

    // kirim verication email
    private function sendVerificationEmail(User $user, string $token): void
    {
        $verificationUrl = config('app.url') . "/api/auth/verify-email?token={$token}";
        Mail::to($user->email)->send(new VerificationEmail($user, $verificationUrl));
    }
}