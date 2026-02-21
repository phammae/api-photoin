<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findByUsernameOrEmail(string $login): ?User
    {
        return User::where('username', $login)
                   ->orWhere('email', $login)
                   ->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    public function findByVerificationToken(string $token): ?User
    {
        return User::where('verification_token', $token)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function updateVerificationToken(User $user, string $token): bool
    {
        return $user->update(['verification_token' => $token]);
    }

    public function verifyEmail(User $user): bool
    {
        return $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
            'status' => 'aktif',
        ]);
    }
}