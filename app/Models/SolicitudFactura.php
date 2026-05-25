<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SolicitudFactura extends Model
{
    protected $table = 'solicitudes_factura';

    // Laravel gestiona created_at / updated_at automáticamente
    // Las columnas existen tras el ALTER TABLE
    public $timestamps = true;

    protected $fillable = [
        'referencia','tipo_receptor','nombre_cliente','nombre_empresa',
        'nif_cif','email','direccion','codigo_postal','ciudad',
        'fecha_consumo','importe_ticket','ticket_filename','ticket_path',
        'ticket_mime','observaciones','atendido_por',
        'acepta_lopd','ip_solicitante','user_agent',
    ];

    public function factura()
    {
        return $this->hasOne(Factura::class, 'id_solicitud');
    }
}
