<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'alamat',
        'identitas',
        'nomor_identitas',
        'upload_identitas',
    ];

    protected $casts = [
        'upload_identitas' => 'string', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}