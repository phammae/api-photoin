<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Cari user berdasarkan username atau email
     */
    public function findByUsernameOrEmail(string $login): ?User;

    /**
     * Cari user berdasarkan email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Cari user berdasarkan Google ID
     */
    public function findByGoogleId(string $googleId): ?User;

    /**
     * Cari user berdasarkan verification token
     */
    public function findByVerificationToken(string $token): ?User;

    /**
     * Buat user baru
     */
    public function create(array $data): User;

    /**
     * Update user
     */
    public function update(User $user, array $data): bool;

    /**
     * Update verification token
     */
    public function updateVerificationToken(User $user, string $token): bool;

    /**
     * Verifikasi email user
     */
    public function verifyEmail(User $user): bool;
}