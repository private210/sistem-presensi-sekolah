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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nama_lengkap');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('alamat')->nullable();
            $table->date('tanggal_lahir');
            $table->string('foto')->nullable();
            $table->boolean('is_active')->default(true);
            // $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
