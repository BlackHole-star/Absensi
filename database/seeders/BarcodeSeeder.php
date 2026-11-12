<?php

namespace Database\Seeders;

use App\Models\Barcode;
use Illuminate\Database\Seeder;

class BarcodeSeeder extends Seeder
{
    public function run(): void
    {
        $barcodes = [
            [
                'nama_lokasi' => 'Kantor Pusat',
                'kode_barcode' => 'KANTOR-PUSAT-001',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meter' => 50,
                'ip_kantor' => '127.0.0.1',
                'is_aktif' => true,
            ],
            [
                'nama_lokasi' => 'Cabang Bekasi',
                'kode_barcode' => 'CABANG-BEKASI-001',
                'latitude' => -6.238270,
                'longitude' => 106.975573,
                'radius_meter' => 50,
                'ip_kantor' => '127.0.0.1',
                'is_aktif' => true,
            ],
        ];

        foreach ($barcodes as $barcode) {
            Barcode::updateOrCreate(
                ['kode_barcode' => $barcode['kode_barcode']],
                $barcode
            );
        }
    }
}