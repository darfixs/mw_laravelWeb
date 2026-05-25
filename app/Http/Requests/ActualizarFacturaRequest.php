<?php
namespace App\Http\Requests;

class ActualizarFacturaRequest extends AppRequest
{
    public function rules(): array
    {
        return [
            'nombre_cliente'           => 'required|string|max:150',
            'empresa'                  => 'nullable|string|max:150',
            'nif_cif'                  => 'required|string|max:15',
            'email'                    => 'required|email|max:200',
            'fecha_consumo'            => 'required|date',
            'importe'                  => 'required|numeric|min:0.01',
            'estado'                   => 'required|in:pendiente,procesando,emitida,cancelada',
            'obs_cliente'              => 'nullable|string|max:500',
            'lineas_ticket'            => 'nullable|array',
            'lineas_ticket.*.cantidad' => 'nullable|integer|min:1',
            'lineas_ticket.*.concepto' => 'nullable|string|max:200',
            'lineas_ticket.*.importe'  => 'nullable|numeric|min:0',
        ];
    }
}
