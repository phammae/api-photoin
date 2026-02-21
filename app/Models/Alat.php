<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';

    protected $fillable = [
        'kode_alat',
        'nama_alat',
        'id_kategori',
        'merk',
        'tipe',
        'serial_number',
        'tahun_pembelian',
        'harga_beli',
        'kondisi',
        'status',
        'foto',
        'spesifikasi',
        'lokasi_penyimpanan',
    ];

    protected $casts = [
        'foto' => 'array',
        'harga_beli' => 'decimal:2',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriAlat::class, 'id_kategori');
    }
    public function kelengkapan()
    {
        return $this->hasMany(KelengkapanAlat::class, 'id_alat');
    }
    public function hargaSewa()
    {
        return $this->hasMany(HargaSewa::class, 'id_alat');
    }
    public function detailPeminjaman()
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_alat');
    }
    public function isTersedia(): bool
    {
        return $this->status === 'tersedia';
    }
    public function isDisewa(): bool
    {
        return $this->status === 'disewa';
    }
}