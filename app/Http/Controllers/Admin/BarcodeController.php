<?php

namespace App\Http\Controllers\Admin;

use App\Models\Barcode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeController extends Controller
{
    /**
     * Menampilkan daftar seluruh QR Code yang tersimpan.
     */
    public function index()
    {
        $barcodes = Barcode::latest()->get();

        return view('admin.barcodes.index', compact('barcodes'));
    }

    /**
     * Menampilkan halaman form untuk menambahkan QR Code baru.
     */
    public function create()
    {
        return view('admin.barcodes.create');
    }

    /**
     * Menyimpan data QR Code baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi'  => 'required|string|max:255',
            'kode_barcode' => 'required|string|max:255|unique:barcodes,kode_barcode',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|numeric|min:1',
            'ip_kantor'    => 'nullable|string|max:255',
            'is_aktif'     => 'nullable|boolean',
        ]);

        // Checkbox is_aktif hanya bernilai true jika dicentang
        $validated['is_aktif'] = $request->has('is_aktif');

        Barcode::create($validated);

        return redirect()
            ->route('admin.barcodes.index')
            ->with('success', 'QR Code berhasil ditambahkan.');
    }

    /**
     * Menampilkan halaman form untuk mengubah data QR Code.
     */
    public function edit($id)
    {
        $barcode = Barcode::findOrFail($id);

        return view('admin.barcodes.edit', compact('barcode'));
    }

    /**
     * Memperbarui data QR Code yang dipilih.
     */
    public function update(Request $request, $id)
    {
        $barcode = Barcode::findOrFail($id);

        $validated = $request->validate([
            'nama_lokasi'  => 'required|string|max:255',
            'kode_barcode' => 'required|string|max:255|unique:barcodes,kode_barcode,' . $barcode->id,
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|numeric|min:1',
            'ip_kantor'    => 'nullable|string|max:255',
            'is_aktif'     => 'nullable|boolean',
        ]);

        // Checkbox is_aktif hanya bernilai true jika dicentang
        $validated['is_aktif'] = $request->has('is_aktif');

        $barcode->update($validated);

        return redirect()
            ->route('admin.barcodes.index')
            ->with('success', 'QR Code berhasil diperbarui.');
    }

    /**
     * Menghapus data QR Code berdasarkan ID.
     */
    public function destroy($id)
    {
        $barcode = Barcode::findOrFail($id);

        $barcode->delete();

        return redirect()
            ->route('admin.barcodes.index')
            ->with('success', 'QR Code berhasil dihapus.');
    }

    /**
     * Mengunduh QR Code dalam format SVG.
     */
    public function download($id)
    {
        $barcode = Barcode::findOrFail($id);

        $qr = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($barcode->kode_barcode);

        $filename = 'qrcode_' . $barcode->id . '.svg';

        return response($qr)
            ->header('Content-Type', 'image/svg+xml')
            ->header(
                'Content-Disposition',
                "attachment; filename=\"$filename\""
            );
    }
}