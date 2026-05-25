<?php
namespace App\Http\Requests;

class ReservaRequest extends AppRequest
{
    public function rules(): array
    {
        return [
            'nombre'   => 'required|string|max:120',
            'telefono' => 'required|string|max:20',
            'email'    => 'nullable|email|max:200',
            'fecha'    => 'required|date',
            'hora'     => 'required|string|max:10',
            'personas' => 'required|integer|min:1|max:50',
            'notas'    => 'nullable|string|max:500',
        ];
    }
}
