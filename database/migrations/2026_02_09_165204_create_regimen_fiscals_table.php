<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regimen_fiscals', function (Blueprint $table) {
            $table->id('id_regimen');
            $table->string('nombre_regimen');

            $table->decimal('tasa_retencion', 8, 4); 
            
            $table->timestamps();
        });

        // Insertar los datos
        DB::table('regimen_fiscals')->insert([
            [
                'nombre_regimen' => 'Arrendamiento',
                'tasa_retencion'    => 0.1000, // 10% ISR
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nombre_regimen' => 'RIF', // Nota: RIF ya no suele tener retención en muchos casos, verificar regla actual
                'tasa_retencion'    => 0.1000, 
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nombre_regimen' => 'RECICO',
                'tasa_retencion'    => 0.0125, // 1.25% ISR (Ahora sí se guardará bien)
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('regimen_fiscals');
    }
};