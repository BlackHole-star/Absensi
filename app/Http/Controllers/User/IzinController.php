<?php 

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    public function form()
    {
        return view('user.izin-form');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'nullable|date|after_or_equal:from',
            'status' => 'required|in:izin,sakit', // harus sesuai enum DB
            'note' => 'required|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $filename = null;
        if ($request->hasFile('attachment')) {
            $filename = $request->file('attachment')->store('bukti_izin', 'public');
        }

        $dates = $request->to
            ? CarbonPeriod::create($request->from, $request->to)
            : [Carbon::parse($request->from)];

        foreach ($dates as $day) {
            Attendance::updateOrCreate([
                'user_id' => Auth::id(),
                'tanggal_absensi' => Carbon::parse($day)->toDateString(),
            ], [
                'status_kehadiran' => $request->status, // 'izin' atau 'sakit'
                'catatan' => $request->note,
                'lampiran' => $filename,
            ]);
        }

        return redirect()->route('user.absen.scan')
            ->with('success', 'Pengajuan izin/sakit berhasil dikirim.');
    }
}
