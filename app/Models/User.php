<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'email',
        'nama_lengkap',
        'role',
        'no_hp',
        'foto',
        'alamat',
        'status',
        'email_verified_at',
        'verification_token',
        'google_id',
        'oauth_provider',
        'ktp_photo',
        'ktp_selfie_photo',
        'nik',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at'       => 'datetime',
            'password'          => 'hashed',
            'is_verified'       => 'boolean',
        ];
    }

    // Relationships
    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class, 'id_user');
    }

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'id_penyewa');
    }

    public function peminjamanDitangani()
    {
        return $this->hasMany(Peminjaman::class, 'id_petugas');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function usersVerified()
    {
        return $this->hasMany(User::class, 'verified_by');
    }

    // Helper Methods - Email Verification (existing)
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function isOAuthUser(): bool
    {
        return $this->oauth_provider !== null;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // Helper Methods - Role
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isPenyewa(): bool
    {
        return $this->role === 'penyewa';
    }

    // Helper Methods - KTP Verification
    public function isVerified(): bool
    {
        return $this->is_verified === true;
    }

    public function isUnverified(): bool
    {
        return $this->is_verified === false;
    }

    public function hasKtpUploaded(): bool
    {
        return !empty($this->ktp_photo) && !empty($this->ktp_selfie_photo);
    }

    public function canRental(): bool
    {
        return $this->isActive() && $this->isVerified();
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopePendingVerification($query)
    {
        return $query->where('is_verified', false)
                     ->whereNotNull('ktp_photo')
                     ->whereNotNull('ktp_selfie_photo');
    }

    public function scopePenyewa($query)
    {
        return $query->where('role', 'penyewa');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

}
