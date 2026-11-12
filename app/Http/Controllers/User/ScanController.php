<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Barcode;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $userId = Auth::id();

        $attendance = Attendance::where('user_id', $userId)
            ->where('tanggal_absensi', $today)
            ->first();

        $lastLocationLog = $attendance
            ? $attendance->attendanceLogs()
                ->whereIn('status_validasi', ['valid', 'mencurigakan'])
                ->latest('waktu_scan')
                ->first()
            : null;

        return view('user.scan', compact(
            'attendance',
            'today',
            'lastLocationLog'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode_value' => 'required|string',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'accuracy'      => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();
        $today  = now()->toDateString();
        $now    = now();
        $ip     = (string) $request->ip();

        $attendance = Attendance::where('user_id', $userId)
            ->where('tanggal_absensi', $today)
            ->first();

        $jenis = $attendance ? 'pulang' : 'masuk';

        /*
        |--------------------------------------------------------------------------
        | Validasi Barcode
        |--------------------------------------------------------------------------
        */

        $barcode = Barcode::where('kode_barcode', $request->barcode_value)
            ->where('is_aktif', true)
            ->first();

        $validasiBarcode = (bool) $barcode;

        $jarak = null;
        $validasiGps = false;
        $validasiIp = false;
        $alasan = [];

        if (!$validasiBarcode) {

            $alasan[] = 'Barcode tidak dikenali atau tidak aktif.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Validasi GPS
            |--------------------------------------------------------------------------
            */

            $jarak = $this->distance(
                $barcode->latitude,
                $barcode->longitude,
                $request->latitude,
                $request->longitude
            );

            $maxAccuracy = (float) config(
                'attendance.gps.max_accuracy_meters'
            );

            $distanceOk = $jarak <= $barcode->radius_meter;

            $accuracyOk = $request->accuracy <= $maxAccuracy;

            $validasiGps = $distanceOk && $accuracyOk;

            if (!$distanceOk) {

                $alasan[] = sprintf(
                    'Jarak %sm melebihi radius %sm dari lokasi barcode.',
                    round($jarak),
                    $barcode->radius_meter
                );
            }

            if (!$accuracyOk) {

                $alasan[] = sprintf(
                    'Akurasi GPS %sm melebihi batas %sm (kemungkinan lokasi tidak valid).',
                    round($request->accuracy),
                    $maxAccuracy
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validasi IP Kantor
            |--------------------------------------------------------------------------
            */

            $allowedIps = $this->parseAllowedIps(
                $barcode->ip_kantor
            );

            /*
            |--------------------------------------------------------------------------
            | Jika IP kantor kosong, validasi IP dilewati.
            |--------------------------------------------------------------------------
            */

            $validasiIp =
                empty($allowedIps)
                || in_array($ip, $allowedIps, true);

            if (!$validasiIp) {

                $alasan[] =
                    'Perangkat tidak terhubung ke jaringan/IP kantor yang terdaftar untuk lokasi ini.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Jika Barcode / GPS / IP tidak valid
        |--------------------------------------------------------------------------
        */

        if (
            !$validasiBarcode
            || !$validasiGps
            || !$validasiIp
        ) {

            $this->recordLog(
                $userId,
                null,
                $barcode,
                $jenis,
                $now,
                $request,
                $ip,
                [
                    'jarak'            => $jarak,
                    'validasi_barcode' => $validasiBarcode,
                    'validasi_gps'     => $validasiGps,
                    'validasi_ip'      => $validasiIp,
                    'status'           => 'ditolak',
                    'alasan'           => implode(' ', $alasan),
                ]
            );

            return $this->error(
                $alasan[0] ?? 'Absen ditolak.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Deteksi perpindahan lokasi tidak wajar
        |--------------------------------------------------------------------------
        */

        $mencurigakan = $this->isImplausibleMovement(
            $userId,
            $now,
            $request->latitude,
            $request->longitude
        );

        if ($mencurigakan) {

            $alasan[] =
                'Perpindahan lokasi dari absen sebelumnya tidak wajar (kecepatan tersirat terlalu tinggi).';
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Absensi
        |--------------------------------------------------------------------------
        */

        return DB::transaction(function () use (
            $userId,
            $today,
            $now,
            $barcode,
            $request,
            $attendance,
            $jenis,
            $ip,
            $jarak,
            $mencurigakan,
            $alasan
        ) {

            $result = $attendance
                ? $this->handleAbsensiKeluar(
                    $attendance,
                    $now
                )
                : $this->handleAbsensiMasuk(
                    $userId,
                    $today,
                    $now
                );

            /*
            |--------------------------------------------------------------------------
            | Simpan Log Absensi
            |--------------------------------------------------------------------------
            */

            $this->recordLog(
                $userId,
                $result['attendance'],
                $barcode,
                $jenis,
                $now,
                $request,
                $ip,
                [
                    'jarak'            => $jarak,
                    'validasi_barcode' => true,
                    'validasi_gps'     => true,
                    'validasi_ip'      => true,
                    'status'           => $result['success']
                        ? ($mencurigakan
                            ? 'mencurigakan'
                            : 'valid')
                        : 'ditolak',

                    'alasan' => $result['success']
                        ? (implode(' ', $alasan) ?: null)
                        : $result['message'],
                ]
            );

            return $result['success']
                ? $this->success($result['message'])
                : $this->error($result['message']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ABSEN MASUK
    |--------------------------------------------------------------------------
    */

    private function handleAbsensiMasuk(
        int $userId,
        string $today,
        Carbon $now
    ): array {

        $jamMasuk = [
            'start' => Carbon::createFromTime(7, 30),
            'end'   => Carbon::createFromTime(9, 30),
            'late'  => Carbon::createFromTime(8, 45),
        ];

        if (!$now->between(
            $jamMasuk['start'],
            $jamMasuk['end']
        )) {

            return [
                'success'    => false,
                'message'    => 'Waktu absen masuk di luar jam yang diperbolehkan.',
                'attendance' => null,
            ];
        }

        $status = $now->between(
            $jamMasuk['late'],
            $jamMasuk['end']
        )
            ? 'terlambat'
            : 'hadir';

        $attendance = Attendance::create([
            'user_id'          => $userId,
            'tanggal_absensi'  => $today,
            'jam_masuk'        => $now->toTimeString(),
            'status_kehadiran' => $status,
        ]);

        return [
            'success'    => true,
            'message'    => 'Absen masuk berhasil.',
            'attendance' => $attendance,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ABSEN PULANG
    |--------------------------------------------------------------------------
    */

    private function handleAbsensiKeluar(
        Attendance $attendance,
        Carbon $now
    ): array {

        $jamKeluar = [
            'start' => Carbon::createFromTime(8, 30),
            'end'   => Carbon::createFromTime(9, 30),
        ];

        if ($attendance->jam_pulang) {

            return [
                'success'    => false,
                'message'    => 'Sudah melakukan absen keluar.',
                'attendance' => $attendance,
            ];
        }

        if (!$now->between(
            $jamKeluar['start'],
            $jamKeluar['end']
        )) {

            return [
                'success'    => false,
                'message'    => 'Waktu absen pulang di luar jam yang diperbolehkan.',
                'attendance' => $attendance,
            ];
        }

        $attendance->update([
            'jam_pulang' => $now->toTimeString(),
        ]);

        return [
            'success'    => true,
            'message'    => 'Absen pulang berhasil.',
            'attendance' => $attendance,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DETEKSI PERPINDAHAN LOKASI TIDAK WAJAR
    |--------------------------------------------------------------------------
    */

    private function isImplausibleMovement(
        int $userId,
        Carbon $now,
        float $lat,
        float $lon
    ): bool {

        $lastLog = AttendanceLog::where('user_id', $userId)
            ->whereIn(
                'status_validasi',
                ['valid', 'mencurigakan']
            )
            ->latest('waktu_scan')
            ->first();

        if (!$lastLog) {
            return false;
        }

        $seconds = $now->diffInSeconds(
            Carbon::parse($lastLog->waktu_scan)
        );

        if ($seconds < 60) {
            return false;
        }

        $jarak = $this->distance(
            $lastLog->latitude_absen,
            $lastLog->longitude_absen,
            $lat,
            $lon
        );

        $kmh =
            ($jarak / 1000)
            / ($seconds / 3600);

        return $kmh > (float) config(
            'attendance.gps.max_speed_kmh'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN LOG
    |--------------------------------------------------------------------------
    */

    private function recordLog(
        int $userId,
        ?Attendance $attendance,
        ?Barcode $barcode,
        string $jenis,
        Carbon $now,
        Request $request,
        string $ip,
        array $result
    ): void {

        AttendanceLog::create([
            'user_id'            => $userId,
            'attendance_id'      => $attendance?->id,
            'barcode_id'         => $barcode?->id,
            'jenis_absensi'      => $jenis,
            'waktu_scan'         => $now,
            'kode_barcode_input' => $request->barcode_value,
            'latitude_absen'     => $request->latitude,
            'longitude_absen'    => $request->longitude,
            'akurasi_meter'      => $request->accuracy,
            'jarak_ke_lokasi'    => $result['jarak'],
            'validasi_barcode'   => $result['validasi_barcode'],
            'validasi_gps'       => $result['validasi_gps'],
            'validasi_ip_kantor' => $result['validasi_ip'],
            'ip_address'         => $ip,
            'user_agent'         => (string) $request->userAgent(),
            'status_validasi'    => $result['status'],
            'alasan_validasi'    => $result['alasan'] ?: null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PARSE IP
    |--------------------------------------------------------------------------
    */

    private function parseAllowedIps(
        ?string $ipKantor
    ): array {

        if (!$ipKantor) {
            return [];
        }

        return array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(',', $ipKantor)
                )
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HITUNG JARAK GPS
    |--------------------------------------------------------------------------
    */

    private function distance(
        $lat1,
        $lon1,
        $lat2,
        $lon2
    ) {

        $earth = 6371000;

        $dLat = deg2rad(
            $lat2 - $lat1
        );

        $dLon = deg2rad(
            $lon2 - $lon1
        );

        $a =
            sin($dLat / 2) ** 2
            +
            cos(deg2rad($lat1))
            *
            cos(deg2rad($lat2))
            *
            sin($dLon / 2) ** 2;

        return 2
            * $earth
            * atan2(
                sqrt($a),
                sqrt(1 - $a)
            );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE SUCCESS
    |--------------------------------------------------------------------------
    */

    private function success(
        $message,
        $data = null
    ) {

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE ERROR
    |--------------------------------------------------------------------------
    */

    private function error(
        $message,
        $status = 400
    ) {

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}