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
        Schema::create('barcodes', function (Blueprint $table) {
            $table->id();

            $table->string('nama_lokasi');
            $table->string('kode_barcode')->unique();

            $table->double('latitude');
            $table->double('longitude');
            $table->float('radius_meter');

            $table->string('ip_kantor')->nullable();
            $table->boolean('is_aktif')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcodes');
    }
};
