@extends('layouts.app')

@section('title', 'Log Validasi Absensi')

@section('content')
<div class="container my-4 px-3 px-md-5">
    <div class="surface-card">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="mb-1">Log Validasi Absensi</h3>
                <p class="text-muted mb-0">
                    Riwayat scan absensi beserta hasil validasi sistem
                </p>
            </div>

            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label">Nama Karyawan</label>
                <input type="text" name="nama" value="{{ $nama }}" class="form-control" placeholder="Cari nama">
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label">Dropdown Karyawan</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua Karyawan</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (string) $userId === (string) $user->id ? 'selected' : '' }}>
                            {{ $user->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label">Jenis Absensi</label>
                <select name="jenis_absensi" class="form-select">
                    <option value="">Semua</option>
                    <option value="masuk" {{ $jenisAbsensi === 'masuk' ? 'selected' : '' }}>Masuk</option>
                    <option value="pulang" {{ $jenisAbsensi === 'pulang' ? 'selected' : '' }}>Pulang</option>
                </select>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label">Status Validasi</label>
                <select name="status_validasi" class="form-select">
                    <option value="">Semua</option>
                    <option value="valid" {{ $statusValidasi === 'valid' ? 'selected' : '' }}>Valid</option>
                    <option value="ditolak" {{ $statusValidasi === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="mencurigakan" {{ $statusValidasi === 'mencurigakan' ? 'selected' : '' }}>Mencurigakan</option>
                </select>
            </div>

            <div class="col-6 col-md-4 col-lg-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>

            <div class="col-6 col-md-4 col-lg-2 d-flex align-items-end">
                <a href="{{ route('admin.attendance_logs.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>

        <div class="d-flex gap-2 mb-4 flex-wrap">
            <a href="{{ route('admin.attendance_logs.export', request()->query()) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
                <i class="bi bi-filetype-csv"></i> Export CSV
            </a>
            <a href="{{ route('admin.attendance_logs.export_excel', request()->query()) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="{{ route('admin.attendance_logs.export_pdf', request()->query()) }}" class="btn btn-danger d-inline-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>

        {{-- Tabel --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Daftar Log Scan</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-clean align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Waktu Scan</th>
                            <th class="d-none d-lg-table-cell">Barcode Input</th>
                            <th>Lokasi Barcode</th>
                            <th>Jarak</th>
                            <th class="d-none d-lg-table-cell">Akurasi GPS</th>
                            <th class="d-none d-lg-table-cell">IP Address</th>
                            <th class="d-none d-lg-table-cell">Barcode</th>
                            <th class="d-none d-lg-table-cell">GPS</th>
                            <th class="d-none d-lg-table-cell">IP Kantor</th>
                            <th>Status</th>
                            <th>Alasan Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusMap = [
                                'valid' => ['label' => 'Valid', 'badge' => 'badge-soft-success'],
                                'ditolak' => ['label' => 'Ditolak', 'badge' => 'badge-soft-danger'],
                                'mencurigakan' => ['label' => 'Mencurigakan', 'badge' => 'badge-soft-warning'],
                            ];
                        @endphp

                        @forelse($logs as $log)
                            @php
                                $status = $statusMap[$log->status_validasi] ?? ['label' => '-', 'badge' => 'badge-soft-secondary'];
                            @endphp

                            <tr>
                                <td>{{ $log->user->nama_lengkap ?? '-' }}</td>
                                <td>{{ ucfirst($log->jenis_absensi) }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->waktu_scan)->format('d-m-Y H:i:s') }}</td>
                                <td class="d-none d-lg-table-cell">{{ $log->kode_barcode_input }}</td>
                                <td>{{ $log->barcode->nama_lokasi ?? '-' }}</td>
                                <td>{{ $log->jarak_ke_lokasi !== null ? round($log->jarak_ke_lokasi) . ' m' : '-' }}</td>
                                <td class="d-none d-lg-table-cell">{{ $log->akurasi_meter !== null ? '±' . round($log->akurasi_meter) . ' m' : '-' }}</td>
                                <td class="d-none d-lg-table-cell">{{ $log->ip_address ?? '-' }}</td>

                                <td class="d-none d-lg-table-cell">
                                    <span class="{{ $log->validasi_barcode ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                        {{ $log->validasi_barcode ? 'Valid' : 'Gagal' }}
                                    </span>
                                </td>

                                <td class="d-none d-lg-table-cell">
                                    <span class="{{ $log->validasi_gps ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                        {{ $log->validasi_gps ? 'Valid' : 'Gagal' }}
                                    </span>
                                </td>

                                <td class="d-none d-lg-table-cell">
                                    <span class="{{ $log->validasi_ip_kantor ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                        {{ $log->validasi_ip_kantor ? 'Valid' : 'Gagal' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="{{ $status['badge'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>

                                <td>{{ \Illuminate\Support\Str::limit($log->alasan_validasi, 60) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="p-0">
                                    @include('components.empty-state', ['message' => 'Tidak ada data log'])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection