<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaSewa extends Model
{
    use HasFactory;

    protected $table = 'harga_sewa';

    protected $fillable = [
        'id_alat',
        'durasi_hari',
        'harga',
        'is_paket',
        'nama_paket',
        'deskripsi_paket',
        'is_active',
    ];

    protected $casts = [
        'harga'     => 'decimal:2',
        'is_paket'  => 'boolean',
        'is_active' => 'boolean',
    ];
    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }
    public function detailPeminjaman()
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_harga_sewa');
    }
    public function hargaPerHari(): float
    {
        return $this->durasi_hari > 0
            ? (float) $this->harga / $this->durasi_hari
            : 0;
    }
}