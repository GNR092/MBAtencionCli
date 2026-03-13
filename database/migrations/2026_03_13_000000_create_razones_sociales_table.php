<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('razones_sociales', function (Blueprint $table) {
            $table->id('id_razon_social')->primary();

            $table->unsignedBigInteger('id_proyecto');
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')->onDelete('cascade');

            $table->string('nombre_razon_social');
            $table->string('rfc', 13)->unique();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('razones_sociales');
    }
};
