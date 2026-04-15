<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (! Schema::hasColumn('razones_sociales', 'id_proyecto')) {
            Schema::table('razones_sociales', function (Blueprint $table) {
                $table->unsignedBigInteger('id_proyecto')->nullable()->after('id_razon_social');
            });
        }

        if (Schema::hasColumn('proyectos', 'id_razon_social')) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('UPDATE razones_sociales AS rs SET id_proyecto = p.id_proyecto FROM proyectos AS p WHERE p.id_razon_social = rs.id_razon_social AND rs.id_proyecto IS NULL');
            } else {
                DB::statement('UPDATE razones_sociales rs INNER JOIN proyectos p ON p.id_razon_social = rs.id_razon_social SET rs.id_proyecto = p.id_proyecto WHERE rs.id_proyecto IS NULL');
            }
        }

        if (Schema::hasColumn('razones_sociales', 'id_proyecto')) {
            Schema::table('razones_sociales', function (Blueprint $table) {
                $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('proyectos', 'id_razon_social')) {
            Schema::table('proyectos', function (Blueprint $table) {
                $table->dropForeign(['id_razon_social']);
                $table->dropColumn('id_razon_social');
            });
        }
    }
};
