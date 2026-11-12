{{--
    Modal konfirmasi generik — pengganti confirm() native browser.

    Cara pakai:
    @include('components.confirm-modal', [
        'id' => 'deleteEmployeeModal-'.$employee->id,
        'title' => 'Hapus Karyawan?',
        'message' => 'Data "'.$employee->nama_lengkap.'" akan dihapus permanen dan tidak bisa dikembalikan.',
        'action' => route('admin.employees.destroy', $employee->id),
        'method' => 'DELETE',
        'confirmLabel' => 'Hapus',
    ])

    Tombol pemicu: <button type="button" data-bs-toggle="modal" data-bs-target="#{{ $id }}">
--}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">{{ $message }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="POST" action="{{ $action }}">
          @csrf
          @if(($method ?? 'POST') !== 'POST')
            @method($method)
          @endif
          <button type="submit" class="btn btn-{{ $confirmVariant ?? 'danger' }}">{{ $confirmLabel ?? 'Konfirmasi' }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
