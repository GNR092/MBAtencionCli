<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incrementos_importe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_contract'); // Relación con contratos
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('importe_base', 10, 2);
            
            // Opcional: para trazabilidad
            $table->timestamps();

            // Clave foránea
            $table->foreign('id_contract')
                  ->references('id')
                  ->on('contract')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incrementos_importe');
    }
};
