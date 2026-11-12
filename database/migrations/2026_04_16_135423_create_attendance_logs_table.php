<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->foreignId('barcode_id')->nullable()->constrained('barcodes')->nullOnDelete();

            $table->enum('jenis_absensi', ['masuk', 'pulang']);
            $table->dateTime('waktu_scan');

            $table->string('kode_barcode_input');
            $table->double('latitude_absen');
            $table->double('longitude_absen');
            $table->float('jarak_ke_lokasi')->nullable();

            $table->boolean('validasi_barcode')->default(false);
            $table->boolean('validasi_gps')->default(false);
            $table->boolean('validasi_ip_kantor')->default(false);

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->enum('status_validasi', [
                'valid',
                'ditolak',
                'mencurigakan'
            ])->default('ditolak');

            $table->text('alasan_validasi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
