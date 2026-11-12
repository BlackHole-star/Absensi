<!doctype html>
<html lang="id" class="h-100">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', config('app.name', 'Absensi'))</title>

    {{-- <!-- Favicon Multi-size -->
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}"> --}}

    <!-- Font & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
  </head>

  <body>
    {{-- Navigasi --}}
    <nav class="navbar navbar-expand-lg">
      <div class="container-xl px-0 d-flex flex-wrap align-items-center justify-content-between">
        <a
          href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('user.absen.scan') }}"
          class="navbar-brand d-flex align-items-center gap-2">
          <img src="{{ asset('logo 3.png') }}" alt="Logo" style="height:50px; width:auto;">
          <span>Aplikasi Absensi</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Buka menu navigasi">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse flex-grow-1" id="mainNavbar">
          @auth
            <ul class="navbar-nav mx-lg-auto">
              @if(auth()->user()->role === 'admin')
                <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Beranda</a></li>
                <li class="nav-item"><a href="{{ route('admin.attendance_logs.index') }}" class="nav-link {{ request()->routeIs('admin.attendance_logs.*') ? 'active' : '' }}">Log Absensi</a></li>
                <li class="nav-item"><a href="{{ route('admin.barcodes.index') }}" class="nav-link {{ request()->routeIs('admin.barcodes.*') ? 'active' : '' }}">Kode QR</a></li>
                <li class="nav-item"><a href="{{ route('admin.rekap') }}" class="nav-link {{ request()->routeIs('admin.rekap') ? 'active' : '' }}">Rekap Absensi</a></li>
                <li class="nav-item"><a href="{{ route('admin.employees.index') }}" class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">Data Karyawan</a></li>
              @else
                <li class="nav-item"><a href="{{ route('user.absen.scan') }}" class="nav-link {{ request()->routeIs('user.absen.scan') ? 'active' : '' }}">Scan Absen</a></li>
                <li class="nav-item"><a href="{{ route('user.absen.izin') }}" class="nav-link {{ request()->routeIs('user.absen.izin') ? 'active' : '' }}">Ajukan Izin</a></li>
                <li class="nav-item"><a href="{{ route('user.absen.history') }}" class="nav-link {{ request()->routeIs('user.absen.history*') ? 'active' : '' }}">Riwayat Absen</a></li>
              @endif
            </ul>

            {{-- Info Pengguna + Logout --}}
            @php
              $namaLengkap = auth()->user()->nama_lengkap;
              $initials = collect(explode(' ', trim($namaLengkap)))
                  ->filter()
                  ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                  ->take(2)
                  ->implode('');
            @endphp
            <div class="dropdown">
              <button class="btn btn-sm dropdown-toggle fw-semibold text-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="navbar-avatar-chip">{{ $initials }}</span>
                {{ $namaLengkap }}
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <a class="dropdown-item" href="{{ route('profile.show') }}">Profil</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item text-danger" type="submit">Keluar</button>
                  </form>
                </li>
              </ul>
            </div>
          @endauth
        </div>
      </div>
    </nav>

    {{-- Konten Utama --}}
    <main class="container-xl main-content">
      @include('components.alert')
      @yield('content')
    </main>

    {{-- Footer --}}
    <footer>
      &copy; {{ date('Y') }} Aplikasi Absensi. Seluruh hak cipta dilindungi.
    </footer>

    @include('components.toast-container')

    {{-- Skrip --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
  </body>
</html>
