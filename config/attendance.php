<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validasi Anti Fake-GPS
    |--------------------------------------------------------------------------
    |
    | max_accuracy_meters: titik GPS dengan akurasi (radius error) lebih buruk
    | dari nilai ini ditolak. Sebagian besar aplikasi fake GPS/emulator tidak
    | melaporkan akurasi sebaik GPS chip asli di area terbuka.
    |
    | max_speed_kmh: dipakai untuk mendeteksi "teleport" — jika jarak antara
    | scan absen saat ini dengan scan terakhir milik user tidak mungkin
    | ditempuh secepat itu, absen ditandai mencurigakan untuk direview admin.
    |
    */

    'gps' => [
        'max_accuracy_meters' => (float) env('GPS_MAX_ACCURACY_METERS', 75),
        'max_speed_kmh' => (float) env('GPS_MAX_SPEED_KMH', 200),
    ],

];
