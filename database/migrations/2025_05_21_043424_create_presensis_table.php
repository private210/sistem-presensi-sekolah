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
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('wali_kelas_id')->constrained('wali_kelas');
            $table->date('tanggal_presensi');
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpa'])->nullable();
            $table->string('keterangan')->nullable();
            $table->integer('pertemuan_ke');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
