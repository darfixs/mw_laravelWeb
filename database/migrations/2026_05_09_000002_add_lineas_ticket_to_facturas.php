<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('facturas') && !Schema::hasColumn('facturas', 'lineas_ticket')) {
            Schema::table('facturas', function (Blueprint $t) {
                $t->json('lineas_ticket')->nullable()->after('concepto');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('facturas', 'lineas_ticket')) {
            Schema::table('facturas', function (Blueprint $t) {
                $t->dropColumn('lineas_ticket');
            });
        }
    }
};
