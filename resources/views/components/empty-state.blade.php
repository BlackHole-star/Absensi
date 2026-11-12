{{--
    Empty state generik untuk blok @empty tabel.

    Cara pakai:
    @include('components.empty-state', ['message' => 'Tidak ada data'])
--}}
<div class="empty-state">
  <i class="bi bi-inbox empty-state-icon"></i>
  <span>{{ $message ?? 'Tidak ada data' }}</span>
</div>
