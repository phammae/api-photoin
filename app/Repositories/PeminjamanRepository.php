<?php

namespace App\Repositories;

use App\Models\Peminjaman;
use App\Repositories\Contracts\PeminjamanRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PeminjamanRepository implements PeminjamanRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Peminjaman::with(['penyewa', 'petugas', 'details.alat']);

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by tanggal pinjam
        if (!empty($filters['tanggal_dari'])) {
            $query->where('tanggal_pinjam', '>=', $filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $query->where('tanggal_pinjam', '<=', $filters['tanggal_sampai']);
        }

        // Search by kode atau nama penyewa
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_peminjaman', 'like', "%{$search}%")
                  ->orWhereHas('penyewa', function ($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Peminjaman
    {
        return Peminjaman::with([
            'penyewa',
            'petugas',
            'disetujuiOleh',
            'details.alat.kategori',
            'details.hargaSewa',
            'pengembalian.detailDenda'
        ])->find($id);
    }

    public function findByKode(string $kode): ?Peminjaman
    {
        return Peminjaman::where('kode_peminjaman', $kode)->first();
    }

    public function create(array $data): Peminjaman
    {
        return Peminjaman::create($data);
    }

    public function update(Peminjaman $peminjaman, array $data): bool
    {
        return $peminjaman->update($data);
    }

    public function getByPenyewa(int $idPenyewa, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Peminjaman::with(['petugas', 'details.alat'])
                           ->where('id_penyewa', $idPenyewa);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getPendingApproval(int $perPage = 15): LengthAwarePaginator
    {
        return Peminjaman::with(['penyewa', 'details.alat'])
                         ->where('status', 'menunggu')
                         ->latest()
                         ->paginate($perPage);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return Peminjaman::with(['penyewa', 'details.alat'])
                         ->whereIn('status', ['disetujui', 'dipinjam'])
                         ->latest()
                         ->paginate($perPage);
    }

    public function updateStatus(Peminjaman $peminjaman, string $status, ?array $additionalData = null): bool
    {
        $data = array_merge(['status' => $status], $additionalData ?? []);
        return $peminjaman->update($data);
    }
}