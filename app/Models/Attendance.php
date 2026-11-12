<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal_absensi',
        'jam_masuk',
        'jam_pulang',
        'status_kehadiran',
        'catatan',
        'lampiran',
    ];

    protected $casts = [
        'tanggal_absensi' => 'date',
        // 'jam_masuk' => 'datetime:H:i:s',
        // 'jam_pulang' => 'datetime:H:i:s',
    ];

    public static function izinStatusOptions(): array
    {
        return [
            'izin' => 'Izin',
            'sakit' => 'Sakit',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
