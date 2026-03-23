<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('retroactivos_eliminados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuenta_original_id');
            $table->unsignedBigInteger('id_contract');
            $table->unsignedBigInteger('xml_file_id')->nullable();
            $table->string('uuid', 36)->nullable();
            $table->string('mes_pago', 7);
            $table->boolean('es_retroactivo')->default(true);
            $table->enum('estado', ['pendiente', 'pagado', 'parcial', 'vencido']);
            $table->decimal('saldo_neto', 15, 2);
            $table->decimal('monto_pagado', 15, 2);
            $table->decimal('saldo_pendiente', 15, 2);
            $table->decimal('isr', 15, 2)->nullable();
            $table->decimal('tasaCuota', 11, 6)->nullable();
            $table->json('mesesdepago');
            $table->json('mesespagados');
            $table->json('mesespendientes');
            $table->string('eliminado_por', 255);
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->index('cuenta_original_id');
            $table->index('eliminado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retroactivos_eliminados');
    }
};
