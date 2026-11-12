@extends('layouts.app')

@section('title', 'Edit Karyawan')

@section('content')
<div class="container my-4 px-3 px-md-5">
  <div class="surface-card">

    {{-- Header halaman --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h3 class="mb-0">Edit Data Karyawan</h3>
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

    <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}" class="needs-validation" novalidate>
      @csrf
      @method('PUT')

      {{-- Input nama lengkap --}}
      <div class="mb-3">
        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control"
               value="{{ old('nama_lengkap', $employee->nama_lengkap) }}" required>
      </div>

      {{-- Input email --}}
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control"
               value="{{ old('email', $employee->email) }}" required>
      </div>

      {{-- Informasi role --}}
      <div class="mb-3">
        <label class="form-label">Role</label>
        <input type="text" class="form-control" value="{{ ucfirst($employee->role) }}" readonly>
      </div>

      <div class="text-end">
        <button type="submit" class="btn btn-success px-4">Simpan Perubahan</button>
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