<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceLogExport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class AttendanceLogController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $statusValidasi = $request->input('status_validasi');
        $jenisAbsensi = $request->input('jenis_absensi');
        $userId = $request->input('user_id');
        $nama = $request->input('nama');

        $users = User::where('role', 'karyawan')
            ->select('id', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();

        $logs = $this->filteredQuery($request)->paginate(10)->withQueryString();

        return view('admin.attendance_logs.index', compact(
            'logs',
            'users',
            'tanggal',
            'statusValidasi',
            'jenisAbsensi',
            'userId',
            'nama'
        ));
    }

    public function export(Request $request)
    {
        $logs = $this->filteredQuery($request)->get();

        $filename = 'attendance_logs_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            fputcsv($file, $this->exportHeadings());

            foreach ($logs as $log) {
                fputcsv($file, $this->exportRow($log));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $logs = $this->filteredQuery($request)->get();

        $pdf = PDF::loadView('admin.attendance_logs.pdf', [
            'logs' => $logs,
            'filters' => [
                'tanggal' => $request->input('tanggal'),
                'statusValidasi' => $request->input('status_validasi'),
                'jenisAbsensi' => $request->input('jenis_absensi'),
                'nama' => $request->input('nama'),
            ],
        ])->setPaper('a4', 'landscape');

        $filename = 'attendance_logs_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $logs = $this->filteredQuery($request)->get();

        $filename = 'attendance_logs_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AttendanceLogExport($logs), $filename);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = AttendanceLog::with(['user', 'barcode'])
            ->latest('waktu_scan');

        if ($request->filled('tanggal')) {
            $query->whereDate('waktu_scan', $request->input('tanggal'));
        }

        if ($request->filled('status_validasi')) {
            $query->where('status_validasi', $request->input('status_validasi'));
        }

        if ($request->filled('jenis_absensi')) {
            $query->where('jenis_absensi', $request->input('jenis_absensi'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('nama')) {
            $nama = $request->input('nama');
            $query->whereHas('user', function ($q) use ($nama) {
                $q->where('nama_lengkap', 'like', '%' . $nama . '%');
            });
        }

        return $query;
    }

    public static function exportHeadings(): array
    {
        return [
            'Nama',
            'Jenis Absensi',
            'Waktu Scan',
            'Barcode Input',
            'Lokasi Barcode',
            'Latitude Absen',
            'Longitude Absen',
            'Akurasi GPS (m)',
            'Jarak ke Lokasi',
            'IP Address',
            'Validasi Barcode',
            'Validasi GPS',
            'Validasi IP Kantor',
            'Status Validasi',
            'Alasan Validasi',
        ];
    }

    public static function exportRow(AttendanceLog $log): array
    {
        return [
            $log->user->nama_lengkap ?? '-',
            $log->jenis_absensi,
            optional($log->waktu_scan)->format('d-m-Y H:i:s'),
            $log->kode_barcode_input,
            $log->barcode->nama_lokasi ?? '-',
            $log->latitude_absen,
            $log->longitude_absen,
            $log->akurasi_meter,
            $log->jarak_ke_lokasi,
            $log->ip_address,
            $log->validasi_barcode ? 'Valid' : 'Gagal',
            $log->validasi_gps ? 'Valid' : 'Gagal',
            $log->validasi_ip_kantor ? 'Valid' : 'Gagal',
            $log->status_validasi,
            $log->alasan_validasi,
        ];
    }
}
