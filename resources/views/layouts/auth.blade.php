<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') - Absensi</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css'])

    @stack('styles')
  </head>
  <body class="auth-body">
    <div class="auth-shell">
      <div class="auth-brand-panel d-none d-lg-flex">
        <div class="auth-brand-panel-inner">
          <div class="auth-brand-logo">
            <img src="{{ asset('logo 3.png') }}" alt="Logo">
          </div>
          <h1 class="auth-brand-title">Aplikasi Absensi</h1>
          <p class="auth-brand-subtitle mb-0">
            Kelola kehadiran karyawan dengan scan QR & validasi lokasi secara real-time.
          </p>
        </div>
      </div>

      <div class="auth-form-panel">
        <div class="auth-form-panel-inner">
          <div class="text-center mb-4 d-lg-none">
            <img src="{{ asset('logo 3.png') }}" alt="Logo" style="height:64px; width:auto;">
          </div>

          {{-- Main Auth Content --}}
          @yield('content')
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
  </body>
</html>
