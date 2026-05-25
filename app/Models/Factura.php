<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'facturas';
    public $timestamps = true;

    protected $fillable = [
        'id_solicitud','numero_factura',
        'receptor_nombre','receptor_empresa','receptor_nif',
        'receptor_email','receptor_direccion','receptor_cp','receptor_ciudad',
        'base_imponible','porcentaje_iva','cuota_iva','total_factura',
        'concepto','lineas_ticket','fecha_consumo','estado','fecha_emision',
        'pdf_path','notas_internas','admin_usuario',
    ];

    protected $casts = [
        'fecha_emision'  => 'datetime',
        'base_imponible' => 'float',
        'cuota_iva'      => 'float',
        'total_factura'  => 'float',
        'lineas_ticket'  => 'array',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudFactura::class, 'id_solicitud');
    }
}
