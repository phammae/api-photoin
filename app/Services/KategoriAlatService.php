<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Repositories\Contracts\KategoriAlatRepositoryInterface;

class KategoriAlatService
{
    public function __construct(
        private KategoriAlatRepositoryInterface $kategoriRepository
    ) {}

    public function getAll(): array
    {
        $kategori = $this->kategoriRepository->getAll();

        return [
            'success' => true,
            'data'    => $kategori,
        ];
    }

    public function getById(int $id): array
    {
        $kategori = $this->kategoriRepository->findById($id);

        if (!$kategori) {
            throw new \Exception('Kategori tidak ditemukan', 404);
        }

        return [
            'success' => true,
            'data'    => $kategori,
        ];
    }

    public function create(array $data, int $userId): array
    {
        $kategori = $this->kategoriRepository->create($data);

        LogAktivitas::log(
            $userId,
            'CREATE_KATEGORI',
            "Menambahkan kategori: {$kategori->nama_kategori}",
            'kategori_alat',
            $kategori->id
        );

        return [
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data'    => $kategori,
        ];
    }

    public function update(int $id, array $data, int $userId): array
    {
        $kategori = $this->kategoriRepository->findById($id);

        if (!$kategori) {
            throw new \Exception('Kategori tidak ditemukan', 404);
        }

        $this->kategoriRepository->update($kategori, $data);

        LogAktivitas::log(
            $userId,
            'UPDATE_KATEGORI',
            "Mengupdate kategori: {$kategori->nama_kategori}",
            'kategori_alat',
            $kategori->id
        );

        return [
            'success' => true,
            'message' => 'Kategori berhasil diupdate',
            'data'    => $kategori->fresh(),
        ];
    }

    public function delete(int $id, int $userId): array
    {
        $kategori = $this->kategoriRepository->findById($id);

        if (!$kategori) {
            throw new \Exception('Kategori tidak ditemukan', 404);
        }

        if ($this->kategoriRepository->hasAlat($id)) {
            throw new \Exception('Kategori tidak dapat dihapus karena masih memiliki alat', 400);
        }

        $namaKategori = $kategori->nama_kategori;
        $this->kategoriRepository->delete($kategori);

        LogAktivitas::log(
            $userId,
            'DELETE_KATEGORI',
            "Menghapus kategori: {$namaKategori}",
            'kategori_alat',
            $id
        );

        return [
            'success' => true,
            'message' => 'Kategori berhasil dihapus',
        ];
    }
}