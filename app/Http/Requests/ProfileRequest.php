<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'alamat' => 'required|string|max:255',
            'identitas' => 'required|in:KTP,SIM',
            'nomor_identitas' => 'required|string|max:50',
            'upload_identitas' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'alamat.required' => 'Alamat tidak boleh kosong.',
            'identitas.required' => 'Jenis identitas wajib dipilih.',
            'identitas.in' => 'Identitas harus berupa KTP atau SIM.',
            'upload_identitas.required' => 'Foto identitas wajib diunggah.',
        ];
    }
}
