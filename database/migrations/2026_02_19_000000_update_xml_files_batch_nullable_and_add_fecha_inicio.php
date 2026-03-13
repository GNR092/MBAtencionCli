<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xml_files', function (Blueprint $table) {
            // Drop FK antes de modificar la columna
            $table->dropForeign(['batch_id']);

            // Hacer batch_id nullable: las facturas confirmadas por usuario
            // pueden existir sin pertenecer a un lote de carga
            $table->unsignedBigInteger('batch_id')->nullable()->change();

            $table->foreign('batch_id')
                ->references('id')
                ->on('xml_batches')
                ->onDelete('cascade');
        });

        // Agregar fecha_inicio si todavía no existe en la tabla
        if (! Schema::hasColumn('xml_files', 'fecha_inicio')) {
            Schema::table('xml_files', function (Blueprint $table) {
                $table->date('fecha_inicio')->nullable()->after('is_valid');
            });
        }
    }

    public function down(): void
    {
        Schema::table('xml_files', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->unsignedBigInteger('batch_id')->nullable(false)->change();
            $table->foreign('batch_id')
                ->references('id')
                ->on('xml_batches')
                ->onDelete('cascade');
        });

        if (Schema::hasColumn('xml_files', 'fecha_inicio')) {
            Schema::table('xml_files', function (Blueprint $table) {
                $table->dropColumn('fecha_inicio');
            });
        }
    }
};
