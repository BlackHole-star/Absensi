<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'karyawan')->get();

        $startMonth = 5; // Mei
        $startYear = 2025;
        $today = Carbon::today();
        $endMonth = $today->month;
        $endYear = $today->year;

        foreach ($users as $user) {
            $records = [];

            for ($year = $startYear; $year <= $endYear; $year++) {
                $monthStart = ($year == $startYear) ? $startMonth : 1;
                $monthEnd   = ($year == $endYear) ? $endMonth : 12;

                for ($month = $monthStart; $month <= $monthEnd; $month++) {
                    $start = Carbon::createFromDate($year, $month, 1);
                    $end   = $start->copy()->endOfMonth();

                    if ($year == $endYear && $month == $endMonth && $end->gt($today)) {
                        $end = $today;
                    }

                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        if ($date->isWeekend()) {
                            continue;
                        }

                        $rand = rand(1, 100);

                        $statusKehadiran = 'hadir';
                        $jamMasuk = '09:00:00';
                        $jamPulang = '17:00:00';
                        $catatan = null;

                        if ($rand <= 3) {
                            $statusKehadiran = 'alpha';
                            $jamMasuk = null;
                            $jamPulang = null;
                        } elseif ($rand <= 8) {
                            $statusKehadiran = 'sakit';
                            $jamMasuk = null;
                            $jamPulang = null;
                            $catatan = 'Sakit';
                        } elseif ($rand <= 15) {
                            $statusKehadiran = 'izin';
                            $jamMasuk = null;
                            $jamPulang = null;
                            $catatan = 'Izin pribadi';
                        } elseif ($rand <= 30) {
                            $statusKehadiran = 'terlambat';
                            $minute = rand(2, 5);
                            $jamMasuk = '09:' . str_pad($minute, 2, '0', STR_PAD_LEFT) . ':00';
                            $jamPulang = '17:00:00';
                            $catatan = 'Terlambat';
                        }

                        $records[] = [
                            'user_id' => $user->id,
                            'tanggal_absensi' => $date->toDateString(),
                            'jam_masuk' => $jamMasuk,
                            'jam_pulang' => $jamPulang,
                            'status_kehadiran' => $statusKehadiran,
                            'catatan' => $catatan,
                            'lampiran' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if (count($records) >= 500) {
                            Attendance::upsert(
                                $records,
                                ['user_id', 'tanggal_absensi'],
                                ['jam_masuk', 'jam_pulang', 'status_kehadiran', 'catatan', 'lampiran', 'updated_at']
                            );

                            $records = [];
                        }
                    }
                }
            }

            if (!empty($records)) {
                Attendance::upsert(
                    $records,
                    ['user_id', 'tanggal_absensi'],
                    ['jam_masuk', 'jam_pulang', 'status_kehadiran', 'catatan', 'lampiran', 'updated_at']
                );
            }
        }
    }
}