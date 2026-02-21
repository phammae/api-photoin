<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Repositories\Contracts\AlatRepositoryInterface;
use Illuminate\Support\Str;

class AlatService
{
    public function __construct(
        private AlatRepositoryInterface $alatRepository
    ) {}

    // Get daftar alat dengan filter & pagination
    public function getAll(array $filters, int $perPage = 15): array
    {
        $alat = $this->alatRepository->getAll($filters, $perPage);

        return [
            'success' => true,
            'data'    => $alat,
        ];
    }

    // Get detail alat
    public function getById(int $id): array
    {
        $alat = $this->alatRepository->findById($id);

        if (!$alat) {
            throw new \Exception('Alat tidak ditemukan', 404);
        }

        return [
            'success' => true,
            'data'    => $alat,
        ];
    }

    // Get alat yang tersedia
    public function getAvailable(): array
    {
        $alat = $this->alatRepository->getAvailable();

        return [
            'success' => true,
            'data'    => $alat,
        ];
    }

    // Buat alat baru
    public function create(array $data, int $userId): array
    {
        // Generate kode alat jika tidak ada
        if (empty($data['kode_alat'])) {
            $data['kode_alat'] = $this->generateKodeAlat();
        }

        // Cek kode alat sudah ada
        if ($this->alatRepository->findByKode($data['kode_alat'])) {
            throw new \Exception('Kode alat sudah digunakan', 400);
        }

        $alat = $this->alatRepository->create($data);

        LogAktivitas::log(
            $userId,
            'CREATE_ALAT',
            "Menambahkan alat baru: {$alat->nama_alat}",
            'alat',
            $alat->id
        );

        return [
            'success' => true,
            'message' => 'Alat berhasil ditambahkan',
            'data'    => $alat->load(['kategori', 'hargaSewa', 'kelengkapan']),
        ];
    }

    // Update alat
    public function update(int $id, array $data, int $userId): array
    {
        $alat = $this->alatRepository->findById($id);

        if (!$alat) {
            throw new \Exception('Alat tidak ditemukan', 404);
        }

        // Cek kode alat jika diubah
        if (!empty($data['kode_alat']) && $data['kode_alat'] !== $alat->kode_alat) {
            if ($this->alatRepository->findByKode($data['kode_alat'])) {
                throw new \Exception('Kode alat sudah digunakan', 400);
            }
        }

        $this->alatRepository->update($alat, $data);

        LogAktivitas::log(
            $userId,
            'UPDATE_ALAT',
            "Mengupdate alat: {$alat->nama_alat}",
            'alat',
            $alat->id
        );

        return [
            'success' => true,
            'message' => 'Alat berhasil diupdate',
            'data'    => $alat->fresh(['kategori', 'hargaSewa', 'kelengkapan']),
        ];
    }

    // Hapus alat
    public function delete(int $id, int $userId): array
    {
        $alat = $this->alatRepository->findById($id);

        if (!$alat) {
            throw new \Exception('Alat tidak ditemukan', 404);
        }

        // Cek apakah alat sedang/pernah dipinjam
        if ($alat->detailPeminjaman()->exists()) {
            throw new \Exception('Alat tidak dapat dihapus karena memiliki riwayat peminjaman', 400);
        }

        $namaAlat = $alat->nama_alat;
        $this->alatRepository->delete($alat);

        LogAktivitas::log(
            $userId,
            'DELETE_ALAT',
            "Menghapus alat: {$namaAlat}",
            'alat',
            $id
        );

        return [
            'success' => true,
            'message' => 'Alat berhasil dihapus',
        ];
    }

    // Generate kode alat unik
    private function generateKodeAlat(): string
    {
        do {
            // Format: ALT-XXXXXX (6 digit random)
            $kode = 'ALT-' . strtoupper(Str::random(6));
        } while ($this->alatRepository->findByKode($kode));

        return $kode;
    }

    // Cek ketersediaan alat pada tanggal tertentu
    public function checkAvailability(int $id, string $tanggalPinjam, string $tanggalKembali): array
    {
        $alat = $this->alatRepository->findById($id);

        if (!$alat) {
            throw new \Exception('Alat tidak ditemukan', 404);
        }

        $isAvailable = $this->alatRepository->isAvailableOnDate($id, $tanggalPinjam, $tanggalKembali);

        return [
            'success'   => true,
            'available' => $isAvailable,
            'message'   => $isAvailable
                ? 'Alat tersedia pada tanggal tersebut'
                : 'Alat sudah dibooking pada tanggal tersebut',
        ];
    }
}