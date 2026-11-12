<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lokasi',
        'kode_barcode',
        'latitude',
        'longitude',
        'radius_meter',
        'ip_kantor',
        'is_aktif',
    ];

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
