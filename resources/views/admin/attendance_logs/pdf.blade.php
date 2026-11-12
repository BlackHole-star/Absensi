<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Log Validasi Absensi</title>
  <style>
    body { font-family: sans-serif; font-size: 10px; }
    h2 { margin-bottom: 2px; }
    p.subtitle { margin-top: 0; color: #555; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
    th { background: #f0f0f0; }
    .badge-success { color: #0a7d29; font-weight: bold; }
    .badge-danger { color: #c0392b; font-weight: bold; }
    .badge-warning { color: #b8860b; font-weight: bold; }
  </style>
</head>
<body>
  <h2>Log Validasi Absensi</h2>
  <p class="subtitle">
    Dicetak: {{ now()->format('d/m/Y H:i') }}
    @if($filters['tanggal'] ?? null) | Tanggal: {{ $filters['tanggal'] }} @endif
    @if($filters['statusValidasi'] ?? null) | Status: {{ ucfirst($filters['statusValidasi']) }} @endif
    @if($filters['jenisAbsensi'] ?? null) | Jenis: {{ ucfirst($filters['jenisAbsensi']) }} @endif
    @if($filters['nama'] ?? null) | Nama: {{ $filters['nama'] }} @endif
  </p>

  <table>
    <thead>
      <tr>
        <th>Nama</th>
        <th>Jenis</th>
        <th>Waktu Scan</th>
        <th>Barcode</th>
        <th>Lokasi Barcode</th>
        <th>Jarak</th>
        <th>Akurasi</th>
        <th>IP Address</th>
        <th>Barcode</th>
        <th>GPS</th>
        <th>IP Kantor</th>
        <th>Status</th>
        <th>Alasan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $log)
        <tr>
          <td>{{ $log->user->nama_lengkap ?? '-' }}</td>
          <td>{{ ucfirst($log->jenis_absensi) }}</td>
          <td>{{ optional($log->waktu_scan)->format('d-m-Y H:i:s') }}</td>
          <td>{{ $log->kode_barcode_input }}</td>
          <td>{{ $log->barcode->nama_lokasi ?? '-' }}</td>
          <td>{{ $log->jarak_ke_lokasi !== null ? round($log->jarak_ke_lokasi) . ' m' : '-' }}</td>
          <td>{{ $log->akurasi_meter !== null ? '±' . round($log->akurasi_meter) . ' m' : '-' }}</td>
          <td>{{ $log->ip_address ?? '-' }}</td>
          <td class="{{ $log->validasi_barcode ? 'badge-success' : 'badge-danger' }}">{{ $log->validasi_barcode ? 'Valid' : 'Gagal' }}</td>
          <td class="{{ $log->validasi_gps ? 'badge-success' : 'badge-danger' }}">{{ $log->validasi_gps ? 'Valid' : 'Gagal' }}</td>
          <td class="{{ $log->validasi_ip_kantor ? 'badge-success' : 'badge-danger' }}">{{ $log->validasi_ip_kantor ? 'Valid' : 'Gagal' }}</td>
          <td class="{{ $log->status_validasi === 'valid' ? 'badge-success' : ($log->status_validasi === 'mencurigakan' ? 'badge-warning' : 'badge-danger') }}">
            {{ ucfirst($log->status_validasi) }}
          </td>
          <td>{{ $log->alasan_validasi }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="13" style="text-align:center;">Tidak ada data log</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
