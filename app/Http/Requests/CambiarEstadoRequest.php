<?php
namespace App\Http\Requests;

class CambiarEstadoRequest extends AppRequest
{
    public function rules(): array
    {
        return [
            'estado' => 'required|in:pendiente,procesando,emitida,cancelada',
        ];
    }
}
