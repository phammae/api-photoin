<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';
    
    public $timestamps = false; // Only created_at

    protected $fillable = [
        'id_user',
        'aktivitas',
        'deskripsi',
        'tabel_terkait',
        'id_terkait',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Helper method to log activity
    public static function log(
        ?int $userId,
        string $aktivitas,
        ?string $deskripsi = null,
        ?string $tabelTerkait = null,
        ?int $idTerkait = null
    ): void {
        self::create([
            'id_user' => $userId,
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
            'tabel_terkait' => $tabelTerkait,
            'id_terkait' => $idTerkait,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}