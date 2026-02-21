<?php

namespace App\Repositories;

use App\Models\KategoriAlat;
use App\Repositories\Contracts\KategoriAlatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class KategoriAlatRepository implements KategoriAlatRepositoryInterface
{
    public function getAll(): Collection
    {
        return KategoriAlat::withCount('alat')->get();
    }

    public function findById(int $id): ?KategoriAlat
    {
        return KategoriAlat::with('alat')->find($id);
    }

    public function create(array $data): KategoriAlat
    {
        return KategoriAlat::create($data);
    }

    public function update(KategoriAlat $kategori, array $data): bool
    {
        return $kategori->update($data);
    }

    public function delete(KategoriAlat $kategori): bool
    {
        return $kategori->delete();
    }

    public function hasAlat(int $kategoriId): bool
    {
        return KategoriAlat::find($kategoriId)?->alat()->exists() ?? false;
    }
}