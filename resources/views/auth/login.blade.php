@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
  <form class="surface-card" action="{{ route('login') }}" method="POST">
    @csrf

    <h2 class="text-center fs-4 fw-semibold mb-4">Masuk ke Akun Anda</h2>

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
      <label for="email" class="form-label">Alamat Email</label>
      <input
        type="email"
        id="email"
        name="email"
        class="form-control form-control-lg"
        placeholder="nama@contoh.com"
        value="{{ old('email') }}"
        required
        autofocus
      >
    </div>

    <div class="mb-3">
      <label for="password" class="form-label d-flex justify-content-between align-items-center">
        <span>Kata Sandi</span>
      </label>
      <input
        type="password"
        id="password"
        name="password"
        class="form-control form-control-lg"
        placeholder="Masukkan kata sandi"
        required
      >
    </div>

    <div class="form-check mb-3">
      <input
        class="form-check-input"
        type="checkbox"
        name="remember"
        id="remember"
      >
      <label class="form-check-label" for="remember">
        Ingat saya
      </label>
    </div>

    <div>
      <button type="submit" class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-box-arrow-in-right"></i> Masuk
      </button>
    </div>
  </form>
@endsection
