<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_peminjaman',
        'jenis_pembayaran',
        'jumlah',
        'metode_pembayaran',
        'status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_snap_token',
        'midtrans_snap_url',
        'tanggal_bayar',
        'tanggal_expired',
        'catatan',
    ];

    protected $casts = [
        'jumlah'          => 'decimal:2',
        'tanggal_bayar'   => 'datetime',
        'tanggal_expired' => 'datetime',
    ];

    // Relationships
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isDp(): bool
    {
        return $this->jenis_pembayaran === 'dp';
    }

    public function isPelunasan(): bool
    {
        return $this->jenis_pembayaran === 'pelunasan';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeDp($query)
    {
        return $query->where('jenis_pembayaran', 'dp');
    }

    public function scopePelunasan($query)
    {
        return $query->where('jenis_pembayaran', 'pelunasan');
    }
}
