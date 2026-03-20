<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->boolean('es_retroactivo')->default(false)->after('mes_pago');
        });
    }

    public function down(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->dropColumn('es_retroactivo');
        });
    }
};
