<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}