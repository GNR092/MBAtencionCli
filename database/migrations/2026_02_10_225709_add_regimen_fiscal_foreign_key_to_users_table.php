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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_regimen')
                  ->references('id_regimen') // Campo en la otra tabla
                  ->on('regimen_fiscals')    // Nombre de la otra tabla
                  ->onDelete('set null');    // Si borras el régimen, el usuario queda con NULL en vez de dar error
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_regimen']);
        });
    }
};
