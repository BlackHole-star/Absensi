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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('tanggal_absensi');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            $table->enum('status_kehadiran', [
                'hadir',
                'terlambat',
                'izin',
                'sakit',
                'alpha'
            ])->nullable();

            $table->text('catatan')->nullable();
            $table->string('lampiran')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'tanggal_absensi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
