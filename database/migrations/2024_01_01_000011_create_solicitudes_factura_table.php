<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('solicitudes_factura')) {
            Schema::create('solicitudes_factura', function (Blueprint $t) {
                $t->id();
                $t->string('referencia', 20)->unique();
                $t->enum('tipo_receptor', ['particular','empresa'])->default('particular');
                $t->string('nombre_cliente', 150);
                $t->string('nombre_empresa', 150)->nullable();
                $t->string('nif_cif', 15);
                $t->string('email', 200);
                $t->string('direccion', 255);
                $t->string('codigo_postal', 10);
                $t->string('ciudad', 100);
                $t->date('fecha_consumo');
                $t->decimal('importe_ticket', 10, 2);
                $t->string('ticket_filename')->nullable();
                $t->string('ticket_path')->nullable();
                $t->string('ticket_mime', 80)->nullable();
                $t->string('observaciones', 500)->nullable();
                $t->boolean('acepta_lopd')->default(true);
                $t->string('ip_solicitante', 45)->nullable();
                $t->string('user_agent', 500)->nullable();
                $t->timestamps(); // created_at + updated_at
            });
        } else {
            // La tabla ya existe (creada con PHP puro sin timestamps) — añadir columnas si faltan
            if (!Schema::hasColumn('solicitudes_factura', 'created_at')) {
                Schema::table('solicitudes_factura', function (Blueprint $t) {
                    $t->timestamp('created_at')->nullable()->useCurrent();
                    $t->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
                });
                \DB::statement('UPDATE solicitudes_factura SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL');
            }
        }
    }
    public function down(): void {
        Schema::dropIfExists('solicitudes_factura');
    }
};
