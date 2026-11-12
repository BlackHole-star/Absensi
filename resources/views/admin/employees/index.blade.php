@extends('layouts.app')

@section('title', 'Kelola Karyawan')

@section('content')
<div class="container my-4 px-3 px-md-5">
  <div class="surface-card">

    {{-- Header halaman --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h3 class="mb-0">Data Karyawan</h3>
      <a href="{{ route('admin.employees.create') }}" class="btn btn-success d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i> Tambah Karyawan
      </a>
    </div>

    {{-- Input pencarian --}}
    <div class="mb-3">
      <input type="text" id="searchInput" class="form-control" placeholder="Cari nama atau email...">
    </div>

    {{-- Tabel data --}}
    <div id="tableContainer">
      @include('admin.employees._table', ['employees' => $employees])
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  const input = document.getElementById('searchInput');
  const container = document.getElementById('tableContainer');
  let timer;

  // Pencarian dengan AJAX
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      const keyword = input.value.trim();
      const url = `?search=${encodeURIComponent(keyword)}`;

      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
          container.innerHTML = html;
          window.history.pushState({}, '', url);
        })
        .catch(err => console.error(err));
    }, 400);
  });

  // Pagination dengan AJAX
  document.addEventListener('click', function (e) {
    const link = e.target.closest('a.page-link');
    if (link && link.closest('.pagination')) {
      e.preventDefault();

      fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
          container.innerHTML = html;
          window.history.pushState({}, '', link.href);
        })
        .catch(err => console.error(err));
    }
  });
</script>
@endpush