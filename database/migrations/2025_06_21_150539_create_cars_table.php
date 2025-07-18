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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('merk_mobil');
            $table->string('nama_mobil');
            $table->decimal('harga_mobil', 10, 2);
            $table->integer('jumlah_mobil');
            $table->integer('jumlah_kursi');
            $table->enum('transmisi', ['Manual', 'Matic']);
            $table->longText('gambar_mobil')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};