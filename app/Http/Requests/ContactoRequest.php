<?php
namespace App\Http\Requests;

class ContactoRequest extends AppRequest
{
    public function rules(): array
    {
        return [
            'nombre'  => 'required|string|max:120',
            'email'   => 'required|email|max:200',
            'asunto'  => 'nullable|string|max:120',
            'mensaje' => 'required|string|max:2000',
        ];
    }
}
