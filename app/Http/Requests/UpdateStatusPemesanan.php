<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPemesanan extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status_pemesanan' => [
                'required',
                'in:Pending,Dikonfirmasi,Dibatalkan,Selesai',
            ],
        ];
    }
}
