<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'email',
        'alamat',
        'password',
        'role',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relasi ke tb_sampah
     */
    public function sampah()
    {
        return $this->hasMany(Sampah::class, 'id_user', 'id_user');
    }

    /**
     * Relasi ke tb_poin
     */
    public function poin()
    {
        return $this->hasMany(Point::class, 'id_user', 'id_user');
    }

    /**
     * Relasi ke tb_superadmin
     */
    public function superadmin()
    {
        return $this->hasMany(SuperAdmin::class, 'id_user', 'id_user');
    }
}
