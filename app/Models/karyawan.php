<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Karyawan extends Authenticatable
{
    use HasFactory, Notifiable;
    
    protected $table = 'karyawan'; // Nama tabel di database
    protected $primaryKey = 'email'; // Primary key
    protected $keyType = 'string';  // Tipe primary key
    public $incrementing = false;  // Non-auto increment
    protected $fillable = [
        'email',
        'nama_lengkap',
        'jabatan',
        'no_hp',
        'password',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

