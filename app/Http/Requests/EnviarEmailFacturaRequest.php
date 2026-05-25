<?php
namespace App\Http\Requests;

class EnviarEmailFacturaRequest extends AppRequest
{
    public function rules(): array
    {
        return [
            'email' => 'nullable|email|max:200',
        ];
    }
}
