@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="container">
  <div class="surface-card">
    @php
      $initials = collect(explode(' ', trim($user->nama_lengkap)))
          ->filter()
          ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
          ->take(2)
          ->implode('');
    @endphp
    <div class="d-flex align-items-center gap-3 mb-4">
      <span class="navbar-avatar-chip" style="width:3.5rem; height:3.5rem; font-size:var(--text-xl);">{{ $initials }}</span>
      <div>
        <h4 class="mb-0">{{ $user->nama_lengkap }}</h4>
        <span class="text-muted small">Profil {{ ucfirst($user->role) }}</span>
      </div>
    </div>

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

    <form method="POST" action="{{ route('profile.update') }}">
      @csrf
      @method('PUT')

      {{-- ID Karyawan --}}
      <div class="mb-3">
        <label class="form-label">ID Karyawan</label>
        <input type="text" class="form-control" value="{{ $user->id }}" readonly>
      </div>

      {{-- Nama (editable) --}}
      <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" class="form-control"
               value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
      </div>

      {{-- Email (readonly) --}}
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
      </div>

      {{-- Role --}}
      <div class="mb-3">
        <label class="form-label">Role</label>
        <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
      </div>

      {{-- Tombol Simpan --}}
      <div class="text-end">
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
          <i class="bi bi-check-lg"></i> Simpan Perubahan
        </button>
        <a href="{{ route('profile.password') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
          <i class="bi bi-key"></i> Ubah Password
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
