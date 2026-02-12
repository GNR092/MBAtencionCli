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
        Schema::create('user_depto', function (Blueprint $table) {
            // ID autoincremental personalizado
            $table->id('id_user_depto');

            // Foreign Key
            // NOTA: Asegúrate de que 'user_ps' es el nombre real de la tabla a la que haces referencia.
            // Si la tabla se llama diferente (ej: 'users'), cámbialo en el método on().
            $table->unsignedBigInteger('id_user_p');
            $table->foreign('id_user_p')->references('id_user_p')->on('user_proyectos');

            // Campos de texto
            $table->string('nombre');
            $table->string('predial');

            // Decimal (10 dígitos en total, 2 decimales)
            $table->decimal('importe', 10, 2);

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_depto');
    }
};
