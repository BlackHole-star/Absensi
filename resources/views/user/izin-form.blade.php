@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Pengajuan Izin / Sakit</h3>

  {{-- Tombol Kembali --}}
  <div class="mb-3">
    <a href="{{ route('user.absen.scan') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>

  <div class="surface-card">
      <form action="{{ route('user.absen.izin.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3 mb-3">
          <div class="col-12 col-md-4">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
              <option value="">-- Pilih --</option>
              <option value="izin">Izin</option>
              <option value="sakit">Sakit</option>
            </select>
          </div>

          <div class="col-6 col-md-4">
            <label for="from" class="form-label">Tanggal Mulai</label>
            <input type="date" name="from" id="from" class="form-control" min="{{ date('Y-m-d') }}" required>
          </div>

          <div class="col-6 col-md-4">
            <label for="to" class="form-label">Tanggal Berakhir <small class="text-muted">(Opsional)</small></label>
            <input type="date" name="to" id="to" class="form-control" min="{{ date('Y-m-d') }}">
          </div>
        </div>

        <div class="mb-3">
          <label for="note" class="form-label">Keterangan</label>
          <textarea name="note" id="note" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
          <label for="attachment" class="form-label">Lampiran Bukti <small class="text-muted">(Opsional)</small></label>
          <input type="file" name="attachment" id="attachment" class="form-control" onchange="previewImage(event)">
          <div class="mt-3 d-none" id="preview">
            <img id="preview-img" class="img-fluid rounded border" style="max-height: 250px;">
          </div>
        </div>

        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-send"></i> Kirim Pengajuan
          </button>
        </div>
      </form>
  </div>
</div>

<script>
  function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const img = document.getElementById('preview-img');
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        img.src = e.target.result;
        preview.classList.remove('d-none');
      }
      reader.readAsDataURL(file);
    }
  }
</script>
@endsection
