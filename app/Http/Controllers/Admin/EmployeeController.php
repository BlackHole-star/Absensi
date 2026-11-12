<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Menampilkan daftar seluruh karyawan.
     */
    public function index(Request $request)
    {
        $employees = User::where('role', 'karyawan')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_lengkap', 'asc')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.employees._table', compact('employees'))->render();
        }

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Menampilkan halaman form untuk menambahkan karyawan baru.
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Menyimpan data karyawan baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email',
        ]);

        $validated['password'] = Hash::make('password123');
        $validated['role'] = 'karyawan';

        User::create($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Menampilkan halaman form untuk mengubah data karyawan.
     */
    public function edit($id)
    {
        $employee = User::where('role', 'karyawan')->findOrFail($id);

        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Memperbarui data karyawan yang dipilih.
     */
    public function update(Request $request, $id)
    {
        $employee = User::where('role', 'karyawan')->findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email,' . $employee->id,
        ]);

        $employee->update($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Menghapus data karyawan berdasarkan ID.
     */
    public function destroy($id)
    {
        $employee = User::where('role', 'karyawan')->findOrFail($id);

        if ($employee->id === auth()->id()) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil dihapus.');
    }

    /**
     * Mereset password karyawan ke password default.
     */
    public function resetPassword($id)
    {
        $employee = User::where('role', 'karyawan')->findOrFail($id);

        if ($employee->id === auth()->id()) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Password akun yang sedang digunakan tidak dapat direset dari menu ini.');
        }

        $employee->password = Hash::make('password123');
        $employee->save();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Password karyawan berhasil direset ke password default.');
    }
}