<?php

namespace App\Repositories;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Repositories\Contracts\AlatRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AlatRepository implements AlatRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Alat::with(['kategori', 'hargaSewa', 'kelengkapan']);

        // Filter by kategori
        if (!empty($filters['id_kategori'])) {
            $query->where('id_kategori', $filters['id_kategori']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by kondisi
        if (!empty($filters['kondisi'])) {
            $query->where('kondisi', $filters['kondisi']);
        }

        // Search by nama/kode/merk
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_alat', 'like', "%{$search}%")
                  ->orWhere('kode_alat', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Alat
    {
        return Alat::with(['kategori', 'hargaSewa', 'kelengkapan'])->find($id);
    }

    public function findByKode(string $kode): ?Alat
    {
        return Alat::where('kode_alat', $kode)->first();
    }

    public function create(array $data): Alat
    {
        return Alat::create($data);
    }

    public function update(Alat $alat, array $data): bool
    {
        return $alat->update($data);
    }

    public function delete(Alat $alat): bool
    {
        return $alat->delete();
    }

    public function getAvailable(): Collection
    {
        return Alat::where('status', 'tersedia')
                   ->where('kondisi', 'baik')
                   ->with(['kategori', 'hargaSewa'])
                   ->get();
    }

    public function updateStatus(Alat $alat, string $status): bool
    {
        return $alat->update(['status' => $status]);
    }

    public function isAvailableOnDate(int $alatId, string $tanggalPinjam, string $tanggalKembali): bool
    {
        // Cek apakah alat ada peminjaman yang overlap dengan tanggal yang diminta
        $overlap = DetailPeminjaman::where('id_alat', $alatId)
            ->whereHas('peminjaman', function ($query) use ($tanggalPinjam, $tanggalKembali) {
                $query->whereIn('status', ['disetujui', 'dipinjam'])
                      ->where(function ($q) use ($tanggalPinjam, $tanggalKembali) {
                          // Tanggal pinjam di antara rentang existing
                          $q->whereBetween('tanggal_pinjam', [$tanggalPinjam, $tanggalKembali])
                            // Atau tanggal kembali di antara rentang existing
                            ->orWhereBetween('tanggal_kembali_rencana', [$tanggalPinjam, $tanggalKembali])
                            // Atau existing peminjaman mengcover seluruh rentang
                            ->orWhere(function ($q2) use ($tanggalPinjam, $tanggalKembali) {
                                $q2->where('tanggal_pinjam', '<=', $tanggalPinjam)
                                   ->where('tanggal_kembali_rencana', '>=', $tanggalKembali);
                            });
                      });
            })
            ->exists();

        return !$overlap; // True jika tidak ada overlap (tersedia)
    }
}