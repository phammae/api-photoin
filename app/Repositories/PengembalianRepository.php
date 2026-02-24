<?php

namespace App\Repositories;

use App\Models\Pengembalian;
use App\Repositories\Contracts\PengembalianRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PengembalianRepository implements PengembalianRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Pengembalian::with([
            'peminjaman.penyewa',
            'peminjaman.details.alat',
            'petugas',
            'detailDenda.alat',
            'detailDenda.aturanDenda'
        ]);

        // Filter by tanggal
        if (!empty($filters['tanggal_dari'])) {
            $query->where('tanggal_kembali', '>=', $filters['tanggal_dari']);
        }
        if (!empty($filters['tanggal_sampai'])) {
            $query->where('tanggal_kembali', '<=', $filters['tanggal_sampai']);
        }

        // Filter by ada denda atau tidak
        if (isset($filters['ada_denda'])) {
            if ($filters['ada_denda']) {
                $query->where('total_denda', '>', 0);
            } else {
                $query->where('total_denda', 0);
            }
        }

        // Search by kode peminjaman
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('peminjaman', function($q) use ($search) {
                $q->where('kode_peminjaman', 'like', "%{$search}%");
            });
        }

        return $query->latest('tanggal_kembali')->paginate($perPage);
    }

    public function findById(int $id): ?Pengembalian
    {
        return Pengembalian::with([
            'peminjaman.penyewa',
            'peminjaman.details.alat.kategori',
            'petugas',
            'detailDenda.alat',
            'detailDenda.aturanDenda'
        ])->find($id);
    }

    public function findByPeminjamanId(int $idPeminjaman): ?Pengembalian
    {
        return Pengembalian::where('id_peminjaman', $idPeminjaman)->first();
    }

    public function create(array $data): Pengembalian
    {
        return Pengembalian::create($data);
    }

    public function update(Pengembalian $pengembalian, array $data): bool
    {
        return $pengembalian->update($data);
    }

}