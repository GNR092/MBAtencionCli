<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign(['id_razon_social']);
            $table->dropUnique(['id_razon_social']);
            $table->foreign('id_razon_social')
                ->references('id_razon_social')
                ->on('razones_sociales')
                ->nullOnDelete();
        });
    }

    public function down(): void {}
};
