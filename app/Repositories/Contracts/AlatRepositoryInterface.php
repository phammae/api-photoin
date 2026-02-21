<?php

namespace App\Repositories\Contracts;

use App\Models\Alat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AlatRepositoryInterface
{
    /**
     * Get semua alat dengan filter & pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get alat by ID dengan relasi
     */
    public function findById(int $id): ?Alat;

    /**
     * Get alat by kode
     */
    public function findByKode(string $kode): ?Alat;

    /**
     * Buat alat baru
     */
    public function create(array $data): Alat;

    /**
     * Update alat
     */
    public function update(Alat $alat, array $data): bool;

    /**
     * Hapus alat
     */
    public function delete(Alat $alat): bool;

    /**
     * Get alat yang tersedia untuk disewa
     */
    public function getAvailable(): Collection;

    /**
     * Update status alat
     */
    public function updateStatus(Alat $alat, string $status): bool;

    /**
     * Cek ketersediaan alat pada tanggal tertentu
     */
    public function isAvailableOnDate(int $alatId, string $tanggalPinjam, string $tanggalKembali): bool;
}