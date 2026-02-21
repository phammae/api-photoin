<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelengkapanAlat extends Model
{
    use HasFactory;

    protected $table = 'kelengkapan_alat';

    protected $fillable = [
        'id_alat',
        'nama_kelengkapan',
        'jumlah',
        'kondisi',
        'keterangan',
    ];

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }
}