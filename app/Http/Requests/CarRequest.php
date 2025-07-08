<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'merk_mobil' => 'required|string|max:255',
            'nama_mobil' => 'required|string|max:255',
            'harga_mobil' => 'required|numeric|min:0',
            'jumlah_mobil' => 'required|integer|min:0',
            'jumlah_kursi' => 'required|integer|min:1',
            'transmisi' => 'required|in:Manual,Matic',
            'gambar_mobil' => 'nullable|string',
        ];
    }
}