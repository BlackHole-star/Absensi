<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class RekapController extends Controller
{
    /**
     * Menampilkan halaman rekap absensi berdasarkan filter:
     * - date  : harian
     * - week  : mingguan
     * - month : bulanan
     * - year  : tahunan
     *
     * Jika tidak ada filter, sistem menampilkan rekap hari ini.
     */
    public function rekap(Request $request)
    {
        $hariIni = Carbon::today();
        $sekarang = Carbon::now();

        $tanggal = $request->input('date');
        $minggu = $request->input('week');
        $bulan = $request->input('month');
        $tahun = $request->input('year');

        /*
        |--------------------------------------------------------------------------
        | Default Rekap
        |--------------------------------------------------------------------------
        */

        $jenisRekap = 'daily';

        $tanggalMulai = $hariIni->copy()->startOfDay();
        $tanggalSelesai = $hariIni->copy()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Menentukan Rentang Tanggal Berdasarkan Filter
        |--------------------------------------------------------------------------
        */

        // ==========================================================
        // REKAP TAHUNAN
        // ==========================================================
        if ($tahun && !$bulan && !$minggu && !$tanggal) {

            $jenisRekap = 'yearly';

            $tanggalMulai = Carbon::createFromDate(
                (int) $tahun,
                1,
                1
            )->startOfDay();

            $tanggalSelesai = Carbon::createFromDate(
                (int) $tahun,
                12,
                31
            )->endOfDay();
        }

        // ==========================================================
        // REKAP BULANAN
        // ==========================================================
        elseif ($bulan && !$minggu && !$tanggal) {

            $jenisRekap = 'monthly';

            try {

                $tanggalBulan = Carbon::createFromFormat(
                    'Y-m',
                    $bulan
                );

                $tanggalMulai = $tanggalBulan
                    ->copy()
                    ->startOfMonth();

                $tanggalSelesai = $tanggalBulan
                    ->copy()
                    ->endOfMonth();

            } catch (\Exception $e) {

                $tanggalMulai = $hariIni
                    ->copy()
                    ->startOfDay();

                $tanggalSelesai = $hariIni
                    ->copy()
                    ->endOfDay();
            }
        }

        // ==========================================================
        // REKAP MINGGUAN
        // ==========================================================
        elseif ($minggu && !$tanggal) {

            if (preg_match('/^(\d{4})-W(\d{2})$/', $minggu, $cocok)) {

                $tahunMinggu = (int) $cocok[1];
                $nomorMinggu = (int) $cocok[2];

                $tanggalMinggu = Carbon::now()
                    ->setISODate($tahunMinggu, $nomorMinggu);

                $jenisRekap = 'weekly';

                $tanggalMulai = $tanggalMinggu
                    ->copy()
                    ->startOfWeek();

                $tanggalSelesai = $tanggalMinggu
                    ->copy()
                    ->endOfWeek();
            }
        }

        // ==========================================================
        // REKAP HARIAN
        // ==========================================================
        elseif ($tanggal) {

            $jenisRekap = 'daily';

            try {

                $tanggalDipilih = Carbon::createFromFormat(
                    'Y-m-d',
                    $tanggal
                );

                $tanggalMulai = $tanggalDipilih
                    ->copy()
                    ->startOfDay();

                $tanggalSelesai = $tanggalDipilih
                    ->copy()
                    ->endOfDay();

            } catch (\Exception $e) {

                $tanggalMulai = $hariIni
                    ->copy()
                    ->startOfDay();

                $tanggalSelesai = $hariIni
                    ->copy()
                    ->endOfDay();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Mencegah Tanggal Masa Depan
        |--------------------------------------------------------------------------
        */

        if ($tanggalMulai->gt($sekarang)) {
            $tanggalMulai = $sekarang
                ->copy()
                ->startOfDay();
        }

        if ($tanggalSelesai->gt($sekarang)) {
            $tanggalSelesai = $sekarang
                ->copy()
                ->endOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Mengambil Daftar Karyawan
        |--------------------------------------------------------------------------
        */

        $daftarKaryawan = User::where('role', 'karyawan')
            ->orderBy('nama_lengkap')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Mengambil Data Absensi
        |--------------------------------------------------------------------------
        */

        $dataAbsensi = Attendance::whereBetween('tanggal_absensi', [
                $tanggalMulai->toDateString(),
                $tanggalSelesai->toDateString()
            ])
            ->get()
            ->groupBy('user_id');

        /*
        |--------------------------------------------------------------------------
        | Menyiapkan Data Rekap
        |--------------------------------------------------------------------------
        */

        $rekapData = [];
        $rentangTanggal = [];

        /*
        |--------------------------------------------------------------------------
        | REKAP TAHUNAN
        |--------------------------------------------------------------------------
        */

        if ($jenisRekap === 'yearly') {

            foreach ($daftarKaryawan as $karyawan) {

                $ringkasan = [
                    'H' => 0,
                    'T' => 0,
                    'I' => 0,
                    'S' => 0,
                    'A' => 0,
                ];

                $absensiKaryawan = $dataAbsensi->get(
                    $karyawan->id,
                    collect()
                );

                foreach ($absensiKaryawan as $absensi) {

                    $statusDatabase = strtolower(
                        trim((string) $absensi->status_kehadiran)
                    );

                    $status = match ($statusDatabase) {

                        'hadir' => 'H',

                        'terlambat' => 'T',

                        'izin' => 'I',

                        'sakit' => 'S',

                        'alpha' => 'A',

                        default => 'A',
                    };

                    $ringkasan[$status]++;
                }

                $rekapData[] = [
                    'nama' => $karyawan->nama_lengkap,
                    'ringkasan' => $ringkasan,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | REKAP HARIAN / MINGGUAN / BULANAN
        |--------------------------------------------------------------------------
        */

        else {

            /*
            |--------------------------------------------------------------------------
            | Membuat daftar tanggal
            |--------------------------------------------------------------------------
            */

            for (
                $tgl = $tanggalMulai->copy();
                $tgl->lte($tanggalSelesai);
                $tgl->addDay()
            ) {

                $rentangTanggal[] = $tgl->format('Y-m-d');
            }

            /*
            |--------------------------------------------------------------------------
            | Rekap Per Karyawan
            |--------------------------------------------------------------------------
            */

            foreach ($daftarKaryawan as $karyawan) {

                $dataKaryawan = [
                    'nama' => $karyawan->nama_lengkap,

                    'status_harian' => [],

                    'ringkasan' => [
                        'H' => 0,
                        'T' => 0,
                        'I' => 0,
                        'S' => 0,
                        'A' => 0,
                    ],
                ];

                /*
                |--------------------------------------------------------------------------
                | Mengambil absensi karyawan
                |--------------------------------------------------------------------------
                */

                $absensiKaryawan = $dataAbsensi->get(
                    $karyawan->id,
                    collect()
                );

                /*
                |--------------------------------------------------------------------------
                | Loop Setiap Tanggal
                |--------------------------------------------------------------------------
                */

                foreach ($rentangTanggal as $tgl) {

                    /*
                    |--------------------------------------------------------------------------
                    | PERBAIKAN UTAMA
                    |--------------------------------------------------------------------------
                    |
                    | Jangan menggunakan:
                    |
                    | firstWhere('tanggal_absensi', $tgl)
                    |
                    | karena tanggal dari database bisa berupa Carbon/date object.
                    |
                    | Kita ubah keduanya menjadi format Y-m-d terlebih dahulu.
                    |
                    */

                    $absensi = $absensiKaryawan->first(
                        function ($item) use ($tgl) {

                            return Carbon::parse(
                                $item->tanggal_absensi
                            )->format('Y-m-d') === $tgl;
                        }
                    );

                    $tanggalCarbon = Carbon::parse($tgl);

                    /*
                    |--------------------------------------------------------------------------
                    | Hari Sabtu dan Minggu
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array(
                            $tanggalCarbon->dayOfWeekIso,
                            [6, 7]
                        )
                    ) {

                        $status = '-';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Tidak Ada Data Absensi
                    |--------------------------------------------------------------------------
                    */

                    elseif (!$absensi) {

                        $status = 'A';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Ada Data Absensi
                    |--------------------------------------------------------------------------
                    */

                    else {

                        $statusDatabase = strtolower(
                            trim((string) $absensi->status_kehadiran)
                        );

                        $status = match ($statusDatabase) {

                            'hadir' => 'H',

                            'terlambat' => 'T',

                            'izin' => 'I',

                            'sakit' => 'S',

                            'alpha' => 'A',

                            default => 'A',
                        };
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Menyimpan Status Harian
                    |--------------------------------------------------------------------------
                    */

                    $dataKaryawan['status_harian'][] = $status;

                    /*
                    |--------------------------------------------------------------------------
                    | Menambahkan Ke Ringkasan
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isset(
                            $dataKaryawan['ringkasan'][$status]
                        )
                    ) {

                        $dataKaryawan['ringkasan'][$status]++;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Menambahkan Data Karyawan ke Rekap
                |--------------------------------------------------------------------------
                */

                $rekapData[] = $dataKaryawan;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Mengirim Data ke View
        |--------------------------------------------------------------------------
        */

        return view('admin.rekap', [

            'type' => $jenisRekap,

            'dateRange' => $rentangTanggal,

            'rekapData' => $rekapData,

            'date' => $tanggal,

            'week' => $minggu,

            'month' => $bulan,

            'year' => $tahun,
        ]);
    }
}