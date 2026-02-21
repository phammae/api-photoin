<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriAlat extends Model
{
    use HasFactory;

    protected $table = 'kategori_alat';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'icon',
    ];

    public function alat()
    {
        return $this->hasMany(Alat::class, 'id_kategori');
    }
}