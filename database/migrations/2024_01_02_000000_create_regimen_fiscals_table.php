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
        // Definimos el ID manualmente y sin autoincremento
        $table->unsignedInteger('id_regimen')->primary(); 
        $table->string('nombre_regimen');
        $table->decimal('tasa_retencion', 8, 4); 
        $table->timestamps();
    });

    // Insertar los datos con sus códigos SAT
    DB::table('regimen_fiscals')->insert([
        [
            'id_regimen'     => 606,
            'nombre_regimen' => 'Arrendamiento',
            'tasa_retencion' => 0.1000,
            'created_at'     => now(),
            'updated_at'     => now(),
        ],
        [
            'id_regimen'     => 621,
            'nombre_regimen' => 'RIF',
            'tasa_retencion' => 0.1000, // Ajustado según sea el caso
            'created_at'     => now(),
            'updated_at'     => now(),
        ],
        [
            'id_regimen'     => 626,
            'nombre_regimen' => 'RESICO',
            'tasa_retencion' => 0.0125,
            'created_at'     => now(),
            'updated_at'     => now(),
        ],
        [
            'id_regimen'     => 601,
            'nombre_regimen' => 'General de Ley Personas Morales',
            'tasa_retencion' => 0.0000,
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