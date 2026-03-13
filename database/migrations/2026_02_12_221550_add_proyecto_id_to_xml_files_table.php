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
        Schema::table('xml_files', function (Blueprint $table) {

            $table->unsignedBigInteger('id_proyecto')->nullable()->after('id_user');

            $table->foreign('id_proyecto')
                ->references('id_proyecto')
                ->on('proyectos')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xml_files', function (Blueprint $table) {

            $table->dropForeign(['id_proyecto']);

            $table->dropColumn('id_proyecto');
        });
    }
};
