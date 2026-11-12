<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());

        $users = User::where('role', 'karyawan')
            ->select('id', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();

        $jumlahKaryawan = $users->count();

        $attendances = Attendance::whereDate('tanggal_absensi', $tanggal)
            ->get()
            ->keyBy('user_id');

        $rekap = $users->map(function ($user) use ($attendances) {
            $attendance = $attendances->get($user->id);

            return (object) [
                'nama_lengkap' => $user->nama_lengkap,
                'status_kehadiran' => $attendance->status_kehadiran ?? 'alpha',
                'jam_masuk' => $attendance->jam_masuk ?? null,
                'jam_pulang' => $attendance->jam_pulang ?? null,
            ];
        });

        $hadirCount = $rekap->where('status_kehadiran', 'hadir')->count();
        $terlambatCount = $rekap->where('status_kehadiran', 'terlambat')->count();
        $izinCount = $rekap->where('status_kehadiran', 'izin')->count();
        $sakitCount = $rekap->where('status_kehadiran', 'sakit')->count();
        $alphaCount = $rekap->where('status_kehadiran', 'alpha')->count();

        $validLogCount = AttendanceLog::whereDate('waktu_scan', $tanggal)
            ->where('status_validasi', 'valid')
            ->count();

        $ditolakLogCount = AttendanceLog::whereDate('waktu_scan', $tanggal)
            ->where('status_validasi', 'ditolak')
            ->count();

        $mencurigakanLogCount = AttendanceLog::whereDate('waktu_scan', $tanggal)
            ->where('status_validasi', 'mencurigakan')
            ->count();

        $logTerbaru = AttendanceLog::with(['user', 'barcode'])
            ->whereDate('waktu_scan', $tanggal)
            ->latest('waktu_scan')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'tanggal',
            'jumlahKaryawan',
            'rekap',
            'hadirCount',
            'terlambatCount',
            'izinCount',
            'sakitCount',
            'alphaCount',
            'validLogCount',
            'ditolakLogCount',
            'mencurigakanLogCount',
            'logTerbaru'
        ));
    }
}