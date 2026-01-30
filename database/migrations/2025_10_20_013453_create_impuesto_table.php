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
        Schema::create('impuesto', function (Blueprint $table) {
            $table->id('impuesto_id');
            $table->string('tipoFactor', 255)->nullable();
            $table->string('regimenFiscal', 255)->nullable();
            $table->decimal('importeBase', 15, 2);
            $table->decimal('tasaCuota', 15, 11);
            $table->decimal('isr', 15, 2);
            $table->unsignedBigInteger('xml_file_id');
            $table->foreign('xml_file_id')
                  ->references('id')
                  ->on('xml_files')
                  ->onDelete('cascade'); // Si se elimina una factura, se eliminan sus impuestos


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impuesto');
    }
};
