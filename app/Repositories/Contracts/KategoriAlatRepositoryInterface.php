<?php

namespace App\Repositories\Contracts;

use App\Models\KategoriAlat;
use Illuminate\Database\Eloquent\Collection;

interface KategoriAlatRepositoryInterface
{
    /**
     * Get semua kategori
     */
    public function getAll(): Collection;

    /**
     * Get kategori by ID
     */
    public function findById(int $id): ?KategoriAlat;

    /**
     * Buat kategori baru
     */
    public function create(array $data): KategoriAlat;

    /**
     * Update kategori
     */
    public function update(KategoriAlat $kategori, array $data): bool;

    /**
     * Hapus kategori
     */
    public function delete(KategoriAlat $kategori): bool;

    /**
     * Cek apakah kategori punya alat
     */
    public function hasAlat(int $kategoriId): bool;
}