<div class="table-responsive">
<table class="table table-clean align-middle">
  <thead>
    <tr>
      <th>Nama Lengkap</th>
      <th>Email</th>
      <th>Role</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($employees as $employee)
      <tr>
        <td>
          {{ $employee->nama_lengkap }}

          {{-- Tanda akun yang sedang login --}}
          @if ($employee->id === auth()->id())
            <span class="badge-soft-info ms-1">Akun Anda</span>
          @endif
        </td>
        <td>{{ $employee->email }}</td>
        <td>
          <span class="badge-soft-success">
            {{ ucfirst($employee->role) }}
          </span>
        </td>
        <td>
          <div class="d-flex gap-2 flex-wrap">

            {{-- Tombol edit --}}
            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm btn-primary" title="Edit">
              <i class="bi bi-pencil-square"></i>
            </a>

            {{-- Tombol reset password --}}
            <button type="button" class="btn btn-sm btn-warning" title="Reset Password" data-bs-toggle="modal" data-bs-target="#resetPasswordModal-{{ $employee->id }}">
              <i class="bi bi-arrow-repeat"></i>
            </button>

            {{-- Tombol hapus --}}
            <button type="button" class="btn btn-sm btn-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal-{{ $employee->id }}">
              <i class="bi bi-trash"></i>
            </button>

          </div>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="4" class="p-0">
          @include('components.empty-state', ['message' => 'Data karyawan tidak ditemukan.'])
        </td>
      </tr>
    @endforelse
  </tbody>
</table>
</div>

{{-- Modal konfirmasi reset password & hapus, satu pasang per baris karyawan --}}
@foreach ($employees as $employee)
  @include('components.confirm-modal', [
      'id' => 'resetPasswordModal-'.$employee->id,
      'title' => 'Reset Password Karyawan?',
      'message' => 'Password "'.$employee->nama_lengkap.'" akan direset ke kata sandi default.',
      'action' => route('admin.employees.resetPassword', $employee->id),
      'method' => 'POST',
      'confirmLabel' => 'Reset Password',
      'confirmVariant' => 'warning',
  ])

  @include('components.confirm-modal', [
      'id' => 'deleteEmployeeModal-'.$employee->id,
      'title' => 'Hapus Karyawan?',
      'message' => 'Data "'.$employee->nama_lengkap.'" akan dihapus permanen dan tidak bisa dikembalikan.',
      'action' => route('admin.employees.destroy', $employee->id),
      'method' => 'DELETE',
      'confirmLabel' => 'Hapus',
      'confirmVariant' => 'danger',
  ])
@endforeach

{{-- Pagination --}}
@if ($employees->hasPages())
  <div class="mt-3 d-flex justify-content-center">
    {{ $employees->links('pagination::bootstrap-5') }}
  </div>
@endif
