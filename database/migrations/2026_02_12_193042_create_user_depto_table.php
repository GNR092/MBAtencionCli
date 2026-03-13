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

            $table->id('id_user_depto');

            $table->unsignedBigInteger('id_user_p');
            $table->foreign('id_user_p')->references('id_user_p')->on('user_proyectos');

            $table->string('nombre');
            $table->string('predial');

            $table->decimal('importe', 10, 2);

            $table->timestamps();
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
