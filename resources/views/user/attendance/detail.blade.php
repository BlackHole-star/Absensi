@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3 class="mb-3">Detail Absensi Tanggal {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h3>

  @if($attendance)
    <ul class="list-group">
      <li class="list-group-item">
        <strong>Status:</strong>
        <span class="status-badge-{{ $attendance->status_kehadiran }}">{{ ucfirst($attendance->status_kehadiran) }}</span>
      </li>
      <li class="list-group-item">
        <strong>Jam Masuk:</strong> 
        {{ $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '-' }}
      </li>
      <li class="list-group-item">
        <strong>Jam Keluar:</strong> 
        {{ $attendance->jam_pulang ? \Carbon\Carbon::parse($attendance->jam_pulang)->format('H:i') : '-' }}
      </li>
      <li class="list-group-item">
        <strong>Keterangan:</strong> {{ $attendance->catatan ?? '-' }}
      </li>
      <li class="list-group-item">
        <strong>Bukti:</strong>
        @if($attendance->lampiran)
          @php
            $ext = pathinfo($attendance->lampiran, PATHINFO_EXTENSION);
          @endphp

          @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
            <div class="mt-2">
              <a href="{{ asset('storage/' . $attendance->lampiran) }}" target="_blank">
                <img src="{{ asset('storage/' . $attendance->lampiran) }}" class="img-fluid rounded border" style="max-height: 200px;">
              </a>
            </div>
          @else
            <a href="{{ asset('storage/' . $attendance->lampiran) }}" target="_blank">Lihat Lampiran</a>
          @endif
        @else
          -
        @endif
      </li>
    </ul>
  @else
    <div class="alert alert-warning mt-3">
      Tidak ada data absensi pada tanggal ini.
    </div>
  @endif

  <a href="{{ route('user.absen.history') }}" class="btn btn-secondary mt-4 d-inline-flex align-items-center gap-2">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>
@endsection
