<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('solicitudes_factura') && !Schema::hasColumn('solicitudes_factura', 'atendido_por')) {
            Schema::table('solicitudes_factura', function (Blueprint $t) {
                $t->string('atendido_por', 80)->nullable()->after('observaciones');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('solicitudes_factura', 'atendido_por')) {
            Schema::table('solicitudes_factura', function (Blueprint $t) {
                $t->dropColumn('atendido_por');
            });
        }
    }
};
