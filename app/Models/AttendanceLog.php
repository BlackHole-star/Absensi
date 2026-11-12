<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'barcode_id',
        'jenis_absensi',
        'waktu_scan',
        'kode_barcode_input',
        'latitude_absen',
        'longitude_absen',
        'akurasi_meter',
        'jarak_ke_lokasi',
        'validasi_barcode',
        'validasi_gps',
        'validasi_ip_kantor',
        'ip_address',
        'user_agent',
        'status_validasi',
        'alasan_validasi',
    ];

    protected $casts = [
        'waktu_scan' => 'datetime',
        'validasi_barcode' => 'boolean',
        'validasi_gps' => 'boolean',
        'validasi_ip_kantor' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function barcode()
    {
        return $this->belongsTo(Barcode::class);
    }
}
