<?php
namespace App\Http\Requests;

class SolicitudFacturaRequest extends AppRequest
{
    public function rules(): array
    {
        return [
            'tipo_receptor'  => 'required|in:particular,empresa',
            'nombre_cliente' => 'required|string|max:150',
            'nombre_empresa' => 'nullable|string|max:150',
            'nif_cif'        => 'required|string|max:15',
            'email'          => 'required|email|max:200',
            'direccion'      => 'required|string|max:255',
            'codigo_postal'  => 'required|string|max:10',
            'ciudad'         => 'required|string|max:100',
            'fecha_consumo'  => 'required|date',
            'importe_ticket' => 'required|numeric|min:0.01',
            'observaciones'  => 'nullable|string|max:500',
            'atendido_por'   => 'nullable|string|max:80',
            'lineas_ticket'  => 'nullable|string',
            'acepta_lopd'    => 'required|accepted',
            'ticket'         => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }
}
