<?php

use App\Http\Controllers\Admin\AttendanceLogController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\AttendanceHistoryController;
use App\Http\Controllers\User\IzinController;
use App\Http\Controllers\User\ScanController;
use Illuminate\Support\Facades\Route;

// ========== AUTH ==========
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root ke login
Route::get('/', fn () => redirect()->route('login'));

// ========== PROFILE ==========
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/profile/password', [ProfileController::class, 'passwordForm'])->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

// ========== ADMIN ==========
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Attendance Logs
    Route::get('/attendance-logs', [AttendanceLogController::class, 'index'])
        ->name('attendance_logs.index');

    Route::get('/attendance-logs/export', [AttendanceLogController::class, 'export'])
        ->name('attendance_logs.export');

    Route::get('/attendance-logs/export-pdf', [AttendanceLogController::class, 'exportPdf'])
        ->name('attendance_logs.export_pdf');

    Route::get('/attendance-logs/export-excel', [AttendanceLogController::class, 'exportExcel'])
        ->name('attendance_logs.export_excel');

    // Rekap & Export
    Route::get('/rekap', [RekapController::class, 'rekap'])->name('rekap');
    Route::get('/export/excel', [ExportController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [ExportController::class, 'exportPDF'])->name('export.pdf');

    // Barcode CRUD
    Route::resource('barcodes', BarcodeController::class)->except(['show']);
    Route::get('/barcodes/{id}/download', [BarcodeController::class, 'download'])->name('barcodes.download');

    // route destroy ini sebenarnya sudah dibuat otomatis oleh Route::resource
    // jadi sebaiknya hapus yang manual biar nggak dobel
    // Route::delete('/barcodes/{barcode}', [BarcodeController::class, 'destroy'])->name('barcodes.destroy');

    // Karyawan CRUD
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::post('/employees/{id}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.resetPassword');
});

// ========== USER ==========
Route::middleware(['auth', 'role:karyawan'])->prefix('user')->name('user.')->group(function () {
    Route::prefix('absen')->group(function () {
        // Absen Masuk & Pulang
        Route::get('/scan', [ScanController::class, 'index'])->name('absen.scan');
        Route::post('/store', [ScanController::class, 'store'])->name('absen.store');

        // Izin/Sakit
        Route::get('/izin', [IzinController::class, 'form'])->name('absen.izin');
        Route::post('/izin', [IzinController::class, 'submit'])->name('absen.izin.submit');

        // Riwayat Absensi
        Route::get('/riwayat', [AttendanceHistoryController::class, 'index'])->name('absen.history');
        Route::get('/riwayat/{date}', [AttendanceHistoryController::class, 'show'])->name('absen.history.detail');
    });
});