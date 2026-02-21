<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AturanDenda extends Model
{
    use HasFactory;

    protected $table = 'aturan_denda';

    protected $fillable = [
        'jenis_denda',
        'nominal',
        'satuan',
        'deskripsi',
        'is_active',
    ];
    
    protected $casts = [
        'nominal'   => 'decimal:2',
        'is_active' => 'boolean',
    ];
    public function detailDenda()
    {
        return $this->hasMany(DetailDenda::class, 'id_aturan_denda');
    }
    public static function getAktif(string $jenisDenda): ?self
    {
        return self::where('jenis_denda', $jenisDenda)
                   ->where('is_active', true)
                   ->first();
    }
}