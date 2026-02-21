<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailDenda extends Model
{
    use HasFactory;

    protected $table = 'detail_denda';

    protected $fillable = [
        'id_pengembalian',
        'id_alat',
        'id_aturan_denda',
        'jenis_denda',
        'jumlah_hari',
        'nominal_per_satuan',
        'total_nominal',
        'keterangan',
    ];

    protected $casts = [
        'nominal_per_satuan' => 'decimal:2',
        'total_nominal'      => 'decimal:2',
    ];
    
    public function pengembalian()
    {
        return $this->belongsTo(Pengembalian::class, 'id_pengembalian');
    }
    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }
    public function aturanDenda()
    {
        return $this->belongsTo(AturanDenda::class, 'id_aturan_denda');
    }
}