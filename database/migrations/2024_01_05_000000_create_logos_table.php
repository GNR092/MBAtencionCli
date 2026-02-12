<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('imagen_ruta'); // Referencia al archivo en storage
            $table->string('url_redireccion')->nullable(); // Por si no todos tienen link
            $table->integer('orden')->default(0); // Para organizar la prioridad
            $table->boolean('activo')->default(true); // Para prender/apagar sin borrar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logos');
    }
};
