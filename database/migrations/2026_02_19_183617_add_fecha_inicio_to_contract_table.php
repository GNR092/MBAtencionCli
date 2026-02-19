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
        Schema::table('contract', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->after('importe_bruto_renta');
        });
    }

    public function down(): void
    {
        Schema::table('contract', function (Blueprint $table) {
            $table->dropColumn('fecha_inicio');
        });
    }
};
