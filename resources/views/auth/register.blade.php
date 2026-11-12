@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
  <form class="surface-card" action="{{ route('register') }}" method="POST">
    @csrf

    <h2 class="text-center fs-4 fw-semibold mb-4">Buat Akun Baru</h2>

    @if ($errors->any())
      <div class="alert alert-danger small py-2 px-3 rounded shadow-sm">
        <ul class="mb-0 ps-4">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="mb-3">
      <label for="name" class="form-label">Nama Lengkap</label>
      <input
        type="text"
        id="name"
        name="name"
        class="form-control form-control-lg"
        placeholder="Nama lengkap Anda"
        value="{{ old('name') }}"
        required
      >
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Alamat Email</label>
      <input
        type="email"
        id="email"
        name="email"
        class="form-control form-control-lg"
        placeholder="nama@contoh.com"
        value="{{ old('email') }}"
        required
      >
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Kata Sandi</label>
      <input
        type="password"
        id="password"
        name="password"
        class="form-control form-control-lg"
        placeholder="Buat kata sandi"
        required
      >
    </div>

    <div class="mb-3">
      <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
      <input
        type="password"
        id="password_confirmation"
        name="password_confirmation"
        class="form-control form-control-lg"
        placeholder="Ulangi kata sandi"
        required
      >
    </div>

    <div class="form-check mb-3">
      <input
        type="checkbox"
        class="form-check-input"
        id="agree"
        required
      >
      <label class="form-check-label small" for="agree">
        Saya menyetujui <a href="#" class="text-primary text-decoration-none">syarat dan kebijakan</a>
      </label>
    </div>

    <div>
      <button type="submit" class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-person-plus"></i> Daftar
      </button>
    </div>
  </form>

  <div class="text-center text-muted mt-4 small">
    Sudah punya akun?
    <a href="{{ route('login') }}" class="text-primary text-decoration-none">Masuk di sini</a>
  </div>
@endsection
