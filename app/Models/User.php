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
            'password' => 'hashed',
        ];
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAKtivitas::class, 'id_user');
    }

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


}
