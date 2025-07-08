<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'merk_mobil',
        'nama_mobil',
        'harga_mobil',
        'jumlah_mobil',
        'jumlah_kursi',
        'transmisi',
        'gambar_mobil',
    ];

    protected $casts = [
        'harga_mobil' => 'double', 
        'jumlah_mobil' => 'integer',
        'jumlah_kursi' => 'integer',
    ];
}