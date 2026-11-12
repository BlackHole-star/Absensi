@extends('layouts.app')

@section('title', 'Ganti Password')

@section('content')
<div class="container">
  <div class="surface-card">
    <h4 class="mb-4 d-flex align-items-center gap-2"><i class="bi bi-key text-primary"></i> Ganti Password</h4>

    {{-- Notifikasi error --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('profile.updatePassword') }}">
      @csrf
      @method('PUT')

      {{-- Password Lama --}}
      <div class="mb-3">
          <label class="form-label">Password Lama</label>
          <input type="password" name="current_password" class="form-control"
                placeholder="Masukkan password lama">
          @error('current_password')
              <div class="text-danger small">{{ $message }}</div>
          @enderror
      </div>

      {{-- Password Baru --}}
      <div class="mb-3">
          <label class="form-label">Password Baru</label>
          <input type="password" name="password" class="form-control" 
                placeholder="Masukkan password baru">
          @error('password')
              <div class="text-danger small">{{ $message }}</div>
          @enderror
      </div>

      {{-- Konfirmasi Password Baru --}}
      <div class="mb-3">
          <label class="form-label">Konfirmasi Password Baru</label>
          <input type="password" name="password_confirmation" class="form-control"
                placeholder="Ulangi password baru">
          @error('password_confirmation')
              <div class="text-danger small">{{ $message }}</div>
          @enderror
      </div>

      <div class="text-end">
        <a href="{{ route('profile.show') }}" class="btn btn-secondary d-inline-flex align-items-center gap-2">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
          <i class="bi bi-check-lg"></i> Simpan Password
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
