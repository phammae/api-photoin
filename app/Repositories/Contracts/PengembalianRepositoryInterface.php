<?php

namespace App\Repositories\Contracts;

use App\Models\Pengembalian;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PengembalianRepositoryInterface
{
    /**
     * Get semua pengembalian dengan pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get pengembalian by ID dengan relasi lengkap
     */
    public function findById(int $id): ?Pengembalian;

    /**
     * Get pengembalian by ID peminjaman
     */
    public function findByPeminjamanId(int $idPeminjaman): ?Pengembalian;

    /**
     * Buat pengembalian baru
     */
    public function create(array $data): Pengembalian;

    /**
     * Update pengembalian
     */
    public function update(Pengembalian $pengembalian, array $data): bool;
}