<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\User;
use App\Models\Attendance;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Set locale & timezone
        Carbon::setLocale('id');
        App::setLocale('id');
        date_default_timezone_set(config('app.timezone'));

        // Scheduler
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->call(function () {
                $today = now()->toDateString();
                $users = User::where('role', 'karyawan')->get();

                foreach ($users as $user) {
                    $attendance = Attendance::firstOrCreate([
                        'user_id' => $user->id,
                        'tanggal_absensi' => $today,
                    ]);

                    // Jangan ubah jika statusnya sudah izin atau sakit
                    if (in_array($attendance->status_kehadiran, ['izin', 'sakit'])) {
                        continue;
                    }

                    $now = now();
                    $jamMasukMulai = Carbon::parse($today . ' 09:00:00');
                    $jamMasukTelat  = Carbon::parse($today . ' 09:05:00');
                    $jamPulangMulai = Carbon::parse($today . ' 17:00:00');

                    // Absen masuk
                    if (!$attendance->jam_masuk) {
                        if ($now->between($jamMasukMulai, $jamMasukTelat)) {
                            $attendance->status_kehadiran = 'hadir';
                            $attendance->jam_masuk = $now->toTimeString();
                        } elseif ($now->gt($jamMasukTelat) && $now->lt($jamPulangMulai)) {
                            $attendance->status_kehadiran = 'terlambat';
                            $attendance->jam_masuk = $now->toTimeString();
                        } elseif ($now->lt($jamMasukMulai)) {
                            // Sebelum jam masuk, jangan set apapun
                            continue;
                        } else {
                            // Sudah lewat jam pulang, belum absen masuk
                            $attendance->status_kehadiran = 'alpha';
                        }
                    }

                    // Absen pulang
                    if (!$attendance->jam_pulang && $now->gte($jamPulangMulai)) {
                        $attendance->jam_pulang = $now->toTimeString();
                    }

                    $attendance->save();
                }
            })->everyMinute(); // update tiap menit
        });
    }
}
