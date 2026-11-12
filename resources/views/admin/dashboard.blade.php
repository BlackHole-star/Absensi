@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container my-4 px-3 px-md-5">
    <div class="surface-card">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="mb-1">Dashboard Admin</h3>
                <p class="text-muted mb-0">
                    Tanggal: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                </p>
            </div>

            <div class="text-md-end">
                <h5 class="text-muted mb-0">Jumlah Karyawan: {{ $jumlahKaryawan }}</h5>
            </div>
        </div>

        {{-- <form method="GET" class="row g-2 mb-4">
            <div class="col-md-4">
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form> --}}

        {{-- Statistik Kehadiran --}}
        <div class="mb-4">
            <h5 class="mb-3">Statistik Kehadiran</h5>
            <div class="row g-3">
                @php
                    $attendanceCards = [
                        ['label' => 'Hadir', 'count' => $hadirCount, 'color' => 'success', 'icon' => 'bi-check-circle-fill'],
                        ['label' => 'Terlambat', 'count' => $terlambatCount, 'color' => 'warning', 'icon' => 'bi-clock-history'],
                        ['label' => 'Izin', 'count' => $izinCount, 'color' => 'primary', 'icon' => 'bi-file-earmark-text-fill'],
                        ['label' => 'Sakit', 'count' => $sakitCount, 'color' => 'info', 'icon' => 'bi-heart-pulse-fill'],
                        ['label' => 'Alpha', 'count' => $alphaCount, 'color' => 'danger', 'icon' => 'bi-x-circle-fill'],
                    ];
                @endphp

                @foreach ($attendanceCards as $data)
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-tile">
                            <div class="stat-tile-icon" style="background-color: var(--color-{{ $data['color'] }}-soft); color: var(--color-{{ $data['color'] }});">
                                <i class="bi {{ $data['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="stat-tile-value">{{ $data['count'] }}</div>
                                <div class="stat-tile-label">{{ $data['label'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Statistik Validasi --}}
        <div class="mb-4">
            <h5 class="mb-3">Statistik Validasi Scan</h5>
            <div class="row g-3">
                @php
                    $validationCards = [
                        ['label' => 'Scan Valid', 'count' => $validLogCount, 'color' => 'success', 'icon' => 'bi-shield-check'],
                        ['label' => 'Scan Ditolak', 'count' => $ditolakLogCount, 'color' => 'danger', 'icon' => 'bi-shield-x'],
                        ['label' => 'Scan Mencurigakan', 'count' => $mencurigakanLogCount, 'color' => 'warning', 'icon' => 'bi-shield-exclamation'],
                    ];
                @endphp

                @foreach ($validationCards as $data)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="stat-tile">
                            <div class="stat-tile-icon" style="background-color: var(--color-{{ $data['color'] }}-soft); color: var(--color-{{ $data['color'] }});">
                                <i class="bi {{ $data['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="stat-tile-value">{{ $data['count'] }}</div>
                                <div class="stat-tile-label">{{ $data['label'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rekap Kehadiran --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Rekap Kehadiran</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-clean align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusLabel = [
                                'hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin',
                                'sakit' => 'Sakit', 'alpha' => 'Alpha',
                            ];
                        @endphp

                        @forelse($rekap as $item)
                            <tr>
                                <td>{{ $item->nama_lengkap }}</td>
                                <td>
                                    <span class="status-badge-{{ $item->status_kehadiran }}">
                                        {{ $statusLabel[$item->status_kehadiran] ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $item->jam_masuk ?: '-' }}</td>
                                <td>{{ $item->jam_pulang ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-0">
                                    @include('components.empty-state')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Log Scan Terbaru --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">10 Log Scan Terbaru</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-clean align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Waktu Scan</th>
                            <th>Barcode Input</th>
                            <th>Jarak</th>
                            <th>IP Address</th>
                            <th>Status Validasi</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $validasiMap = [
                                'valid' => ['label' => 'Valid', 'badge' => 'badge-soft-success'],
                                'ditolak' => ['label' => 'Ditolak', 'badge' => 'badge-soft-danger'],
                                'mencurigakan' => ['label' => 'Mencurigakan', 'badge' => 'badge-soft-warning'],
                            ];
                        @endphp

                        @forelse($logTerbaru as $log)
                            @php
                                $validasi = $validasiMap[$log->status_validasi] ?? ['label' => '-', 'badge' => 'badge-soft-secondary'];
                            @endphp

                            <tr>
                                <td>{{ $log->user->nama_lengkap ?? '-' }}</td>
                                <td>{{ ucfirst($log->jenis_absensi) }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->waktu_scan)->format('d-m-Y H:i:s') }}</td>
                                <td>{{ $log->kode_barcode_input }}</td>
                                <td>{{ $log->jarak_ke_lokasi !== null ? $log->jarak_ke_lokasi . ' m' : '-' }}</td>
                                <td>{{ $log->ip_address ?? '-' }}</td>
                                <td>
                                    <span class="{{ $validasi['badge'] }}">
                                        {{ $validasi['label'] }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($log->alasan_validasi, 50) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-0">
                                    @include('components.empty-state', ['message' => 'Belum ada log scan'])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.attendance_logs.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua Log
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    setTimeout(() => {
        const url = new URL(window.location.href);
        window.location.href = url.toString();
    }, 60000);
</script>
@endsection