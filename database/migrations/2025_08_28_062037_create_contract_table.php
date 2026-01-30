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
        Schema::create('contract', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // FK al usuario
            $table->boolean('is_admin')->default(false);
            $table->string('folio');
            $table->date('fecha');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->string('nombre');
            $table->string('tipo');
            $table->LONGBLOB('contenido');
            $table->string('proyecto');
            $table->decimal('importe_bruto_renta', 10, 2)->nullable();
            $table->date('fecha_creacion')->nullable();
            $table->date('fecha_terminacion')->nullable();
            $table->timestamps();

             // Clave foránea
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract');
    }
};
