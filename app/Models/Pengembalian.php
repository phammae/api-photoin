<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    use HasFactory;

    protected $table = 'pengembalian';

    protected $fillable = [
        'id_peminjaman',
        'id_petugas',
        'tanggal_kembali',
        'hari_terlambat',
        'total_denda',
        'catatan',
    ];

    protected $casts = [
        'tanggal_kembali' => 'date',
        'total_denda'     => 'decimal:2',
    ];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }
    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_petugas');
    }
    public function detailDenda()
    {
        return $this->hasMany(DetailDenda::class, 'id_pengembalian');
    }
    public function adaDenda(): bool
    {
        return $this->total_denda > 0;
    }
}