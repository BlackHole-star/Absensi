<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * 1. Cek Alpha untuk yang belum absen masuk
 *    Jalan sekali sehari jam 09:06
 */
Schedule::call(function () {
    $today = now()->toDateString();
    $jamMasukAkhir = Carbon::createFromTime(9, 5);

    $affectedUsers = [];

    foreach (User::all() as $user) {
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$existing && now()->gt($jamMasukAkhir)) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'status' => 'absent',
            ]);

            $affectedUsers[] = $user->name ?? "User ID {$user->id}";
        }
    }

    if ($affectedUsers) {
        Log::info('Auto Alpha - Tidak absen masuk', [
            'tanggal' => $today,
            'user' => $affectedUsers
        ]);
    }
})->dailyAt('09:06');


/**
 * 2. Cek Alpha untuk yang belum absen pulang
 *    Jalan sekali sehari jam 09:16
 */
Schedule::call(function () {
    $today = now()->toDateString();
    $jamPulangAkhir = Carbon::createFromTime(9, 15);

    $affectedUsers = [];

    foreach (User::all() as $user) {
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (
            $existing &&
            !$existing->time_out &&
            now()->gt($jamPulangAkhir) &&
            !in_array($existing->status, ['izin', 'sakit'])
        ) {
            $existing->update([
                'status' => 'absent',
            ]);

            $affectedUsers[] = $user->name ?? "User ID {$user->id}";
        }
    }

    if ($affectedUsers) {
        Log::info('Auto Alpha - Tidak absen pulang', [
            'tanggal' => $today,
            'user' => $affectedUsers
        ]);
    }
})->dailyAt('09:16');
