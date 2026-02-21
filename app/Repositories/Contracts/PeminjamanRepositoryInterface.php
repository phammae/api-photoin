<?php

namespace App\Repositories\Contracts;

use App\Models\Peminjaman;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PeminjamanRepositoryInterface
{
    // Get peminjaman dengan fitur pagination dan filter
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    // Get peminjaman by ID
    public function findById(int $id): ?Peminjaman;

    // Get peminjaman by kode
    public function findByKode(string $kode): ?Peminjaman;

    // Create peminjaman baru
    public function create(array $data): ?Peminjaman;

    // Update peminjaman
    public function update(Peminjaman $Peminjaman, array $data): bool;

    // Get peminjaman by penyewa
    public function getByPenyewa(int $idPenyewa, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    // Get peminjaman yang menunggu persetujuan
    public function getPendingApproval(int $perPage = 15): LengthAwarePaginator;

    // Get peminjaman aktif (dipinjam/disetujui)
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    // Update status peminjaman
    public function updateStatus(Peminjaman $Peminjaman, string $status, ?array $additionalData = null): bool;
}