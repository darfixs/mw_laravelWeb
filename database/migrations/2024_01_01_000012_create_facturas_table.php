<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('facturas')) {
            Schema::create('facturas', function (Blueprint $t) {
                $t->id();
                $t->foreignId('id_solicitud')->constrained('solicitudes_factura')->cascadeOnUpdate()->restrictOnDelete();
                $t->string('numero_factura', 20)->unique();
                $t->string('receptor_nombre', 150);
                $t->string('receptor_empresa', 150)->nullable();
                $t->string('receptor_nif', 15);
                $t->string('receptor_email', 200);
                $t->string('receptor_direccion', 255)->default('');
                $t->string('receptor_cp', 10)->default('00000');
                $t->string('receptor_ciudad', 100)->default('');
                $t->decimal('base_imponible', 10, 2)->default(0);
                $t->decimal('porcentaje_iva', 5, 2)->default(10);
                $t->decimal('cuota_iva', 10, 2)->default(0);
                $t->decimal('total_factura', 10, 2)->default(0);
                $t->string('concepto', 500)->default('Consumicion en Miss Whitney');
                $t->date('fecha_consumo');
                $t->enum('estado', ['pendiente','procesando','emitida','cancelada'])->default('pendiente');
                $t->datetime('fecha_emision')->nullable();
                $t->string('pdf_path', 500)->nullable();
                $t->string('notas_internas')->nullable();
                $t->string('admin_usuario', 80)->nullable();
                $t->timestamps();
            });
        } else {
            // Añadir timestamps si faltan
            if (!Schema::hasColumn('facturas', 'created_at')) {
                Schema::table('facturas', function (Blueprint $t) {
                    $t->timestamp('created_at')->nullable()->useCurrent();
                    $t->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
                });
                \DB::statement('UPDATE facturas SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL');
            }
        }
    }
    public function down(): void {
        Schema::dropIfExists('facturas');
    }
};
