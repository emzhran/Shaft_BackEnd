<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {   
        $rules = [];

        if ($this->isMethod('post')) {
            $rules = [
                'car_id' => ['required', 'integer', 'exists:cars,id'],
                'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
                'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
                'metode_pembayaran' => ['required', 'string', 'max:255'],
            ];
        }

        if ($this->isMethod('put')) {
            $rules = [
                'tanggal_mulai' => ['nullable', 'date', 'after_or_equal:today'],
                'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
                'metode_pembayaran' => ['nullable', 'string', 'max:255'],
                'status_pemesanan' => ['nullable', 'string', Rule::in(['Dibatalkan'])],
                'rating' => 'nullable|integer|min:1|max:5',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'car_id.required' => 'ID mobil wajib diisi.',
            'car_id.integer' => 'ID mobil harus berupa angka.',
            'car_id.exists' => 'Mobil tidak ditemukan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib diisi.',
            'status_pemesanan.in' => 'Status pesanan hanya dapat diubah menjadi "Dibatalkan".',
        ];
    }

}
