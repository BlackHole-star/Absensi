@extends('layouts.app')

@section('title', 'Tambah Karyawan')

@section('content')
<div class="container my-4 px-3 px-md-5">
  <div class="surface-card">

    {{-- Header halaman --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h3 class="mb-0">Tambah Karyawan Baru</h3>
      <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        ← Kembali
      </a>
    </div>

    {{-- Pesan error validasi --}}
    @if ($errors->any())
      <div class="alert alert-danger rounded-2">
        <ul class="mb-0 ps-3">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.employees.store') }}" class="needs-validation" novalidate>
      @csrf

      {{-- Input nama lengkap --}}
      <div class="mb-3">
        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control"
               value="{{ old('nama_lengkap') }}" required>
      </div>

      {{-- Input email --}}
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control"
               value="{{ old('email') }}" required>
      </div>

      {{-- Informasi password default --}}
      <div class="mb-3">
        <label class="form-label">Password Default</label>
        <input type="text" class="form-control" value="password123" readonly>
      </div>

      {{-- Informasi role --}}
      <div class="mb-3">
        <label class="form-label">Role</label>
        <input type="text" class="form-control" value="karyawan" readonly>
      </div>

      <div class="text-end">
        <button type="submit" class="btn btn-success px-4">Simpan</button>
      </div>
    </form>

  </div>
</div>
@endsection

@push('scripts')
<script>
  // Validasi form bootstrap
  (() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');

    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>
@endpush