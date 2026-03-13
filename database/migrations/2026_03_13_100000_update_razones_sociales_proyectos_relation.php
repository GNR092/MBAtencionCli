<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_razon_social')->nullable()->unique()->after('nombre_proyecto');
            $table->foreign('id_razon_social')->references('id_razon_social')->on('razones_sociales')->onDelete('set null');
        });

        Schema::table('razones_sociales', function (Blueprint $table) {
            $table->dropForeign(['id_proyecto']);
            $table->dropColumn('id_proyecto');
        });
    }

    public function down(): void
    {
        Schema::table('razones_sociales', function (Blueprint $table) {
            $table->unsignedBigInteger('id_proyecto')->after('id_razon_social');
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')->onDelete('cascade');
        });

        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign(['id_razon_social']);
            $table->dropColumn('id_razon_social');
        });
    }
};
