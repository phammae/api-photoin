<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_alat';

    protected $fillable = [
        'id_penyewa',
        'id_petugas',
        'kode_peminjaman',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'status',
        'total_harga',
        'total_denda',
        'catatan_penyewa',
        'catatan_petugas',
        'alasan_tolak',
        'disetujui_oleh',
        'disetujui_at',
    ];

    protected $casts = [
        'tanggal_pinjam'                 => 'date',
        'tanggal_kembali_rencana'        => 'date',
        'tanggal_kembali_aktual'         => 'date',
        'disetujui_at'                   => 'datetime',
        'total_harga'                    => 'decimal:2',
        'total_denda'                    => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Peminjaman $peminjaman) {
            if (empty($peminjaman->kode_peminjaman)) {
                $peminjaman->kode_peminjaman = self::generatedKode();
            }
        });
    }

    public function penyewa()
    {
        return $this->belongsTo(User::class, 'id_penyewa');
    }
    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_petugas');
    }
    public function disetujuiOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
    public function details()
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peminjaman');
    }
    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'id_peminjaman');
    }

    // Helper untuk status
    public function isMenunggu():bool       { return $this->status === 'menunggu'; }
    public function isDisetujui():bool      { return $this->status === 'disetujui'; }
    public function isDitolak():bool        { return $this->status === 'ditolak'; }
    public function isDipinjam():bool       { return $this->status === 'dipinjam'; }
    public function isDikembalikan():bool   { return $this->status === 'dikembalikan'; }
    public function isDibatalkan():bool     { return $this->status === 'dibatalkan'; }

    public function hitungHariTerlambat(): int
    {
        $tanggalAktual = $this->tanggal_kembali_aktual ?? now()->toDateObject();
        $selisih = $this->tanggal_kembali_rencana->diffInDays($tanggalAktual, false);
        return max(0, (int) $selisih);
    }
}