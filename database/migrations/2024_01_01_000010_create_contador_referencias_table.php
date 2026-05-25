<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

return new class extends Migration {
    public function up(): void {
        // Crear solo si no existe — safe para re-ejecutar
        if (!Schema::hasTable('contador_referencias')) {
            Schema::create('contador_referencias', function (Blueprint $t) {
                $t->string('serie', 20)->primary();
                $t->unsignedInteger('ultimo_numero')->default(0);
            });
        }
        // Insertar series si no existen
        foreach (['MW-2024','MW-2025','MW-2026','MW-2027'] as $serie) {
            DB::table('contador_referencias')->insertOrIgnore([
                'serie' => $serie, 'ultimo_numero' => 0
            ]);
        }
    }
    public function down(): void {
        Schema::dropIfExists('contador_referencias');
    }
};
