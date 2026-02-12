<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anuncios', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('adjunto_ruta')->nullable(); // Para el PDF o Imagen
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('baja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anuncios');
    }
};
