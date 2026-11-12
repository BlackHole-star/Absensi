@extends('layouts.app')

@section('title', 'Data QR Code')

@section('content')
<div class="container my-4 px-3 px-md-5">
  <div class="surface-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
      <h3 class="mb-0">Daftar QR Code</h3>
      <a href="{{ route('admin.barcodes.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
        <i class="bi bi-plus-circle"></i>
        Tambah QR Code
      </a>
    </div>

    <div class="row g-4">
      @forelse($barcodes as $barcode)
        <div class="col-12 col-md-6 col-lg-4">
          <div class="surface-card h-100 d-flex flex-column justify-content-between">

            <div class="text-center mb-3" id="qrcode-{{ $barcode->id }}"></div>

            <h5 class="fw-semibold text-center">{{ $barcode->nama_lokasi }}</h5>

            <ul class="list-unstyled small text-muted mb-3">
              <li><strong>Isi Kode:</strong> {{ $barcode->kode_barcode }}</li>
              <li>
                <strong>Koordinat:</strong>
                <a href="https://www.google.com/maps/search/?api=1&query={{ $barcode->latitude }},{{ $barcode->longitude }}" target="_blank">
                  {{ $barcode->latitude }}, {{ $barcode->longitude }}
                </a>
              </li>
              <li><strong>Radius:</strong> {{ $barcode->radius_meter }} meter</li>
              <li><strong>IP Kantor:</strong> {{ $barcode->ip_kantor ?? '-' }}</li>
              <li>
                <strong>Status:</strong>
                @if($barcode->is_aktif)
                  <span class="badge-soft-success">Aktif</span>
                @else
                  <span class="badge-soft-secondary">Nonaktif</span>
                @endif
              </li>
            </ul>

            <div class="d-flex justify-content-end gap-2 mt-auto">
              <a href="{{ route('admin.barcodes.edit', $barcode->id) }}" class="btn btn-sm btn-outline-warning" title="Ubah QR Code">
                <i class="bi bi-pencil"></i>
              </a>

              <a href="{{ route('admin.barcodes.download', $barcode->id) }}" class="btn btn-sm btn-outline-success" title="Unduh QR Code">
                <i class="bi bi-download"></i>
              </a>

              <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteBarcodeModal-{{ $barcode->id }}">
                <i class="bi bi-trash"></i>
              </button>
            </div>

          </div>
        </div>
      @empty
        <div class="col-12">
          @include('components.empty-state', ['message' => 'Belum ada QR Code yang terdaftar.'])
        </div>
      @endforelse
    </div>
  </div>
</div>

{{-- Modal konfirmasi hapus, satu per QR Code --}}
@foreach ($barcodes as $barcode)
  @include('components.confirm-modal', [
      'id' => 'deleteBarcodeModal-'.$barcode->id,
      'title' => 'Hapus QR Code?',
      'message' => 'QR Code untuk lokasi "'.$barcode->nama_lokasi.'" akan dihapus permanen.',
      'action' => route('admin.barcodes.destroy', $barcode->id),
      'method' => 'DELETE',
      'confirmLabel' => 'Hapus',
      'confirmVariant' => 'danger',
  ])
@endforeach

<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
<script>
  /**
   * Membuat tampilan QR Code untuk setiap data yang ada pada daftar.
   */
  document.addEventListener("DOMContentLoaded", function () {
    @foreach($barcodes as $barcode)
      new QRCode(document.getElementById("qrcode-{{ $barcode->id }}"), {
        text: @json($barcode->kode_barcode),
        width: 128,
        height: 128,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
      });
    @endforeach
  });
</script>
@endsection