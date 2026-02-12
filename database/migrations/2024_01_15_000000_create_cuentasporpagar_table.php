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
        Schema::create('cuentasporpagar', function (Blueprint $table) {
            $table->id('id_cuentas_por_pagar');
            $table->enum('estado', ['pendiente', 'pagado','parcial','vencido'])->default('pendiente');
            $table->decimal('saldo_neto', 15, 2)->nullable();
            $table->decimal('monto_pagado', 15, 2)->nullable();
            $table->decimal('saldo_pendiente', 15, 2)->nullable();
            $table->unsignedBigInteger('id_contract'); 
            $table->foreign('id_contract')->references('id')->on('contract')->onDelete('cascade');
            $table->unsignedBigInteger('xml_file_id');
            $table->foreign('xml_file_id')->references('id')->on('xml_files')->onDelete('cascade');
            $table->json('mesesdepago')->nullable();       
            $table->json('mesespagados')->nullable();      
            $table->json('mesespendientes')->nullable();   
            $table->decimal('isr', 15, 2)->nullable();
            $table->decimal('tasaCuota', 15, 11)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentasporpagar');
    }
};
