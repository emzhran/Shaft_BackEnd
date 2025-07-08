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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('alamat')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('role_id')->default(2)->constrained('roles')->onDelete('cascade'); // Default ke customer
            $table->enum('status_akun', ['Terverifikasi', 'Belum Terverifikasi'])->default('Belum Terverifikasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};