<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'detail_peminjaman_alat';

    protected $fillable = [
        'id_peminjaman',
        'id_alat',
        'id_harga_sewa',
        'durasi_hari',
        'harga_per_hari',
        'subtotal',
        'kondisi_awal',
        'catatan_kondisi_awal',
    ];

    protected $casts = [
        'harga_per_hari' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    public function hargaSewa()
    {
        return $this->belongsTo(HargaSewa::class, 'id_harga_sewa');
    }
}