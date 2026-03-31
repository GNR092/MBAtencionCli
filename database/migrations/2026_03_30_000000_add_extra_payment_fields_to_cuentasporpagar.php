<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->unsignedSmallInteger('meses_cubiertos')->default(1)->after('monto_pagado');
            $table->boolean('es_extra')->default(false)->after('meses_cubiertos');
            $table->index('es_extra');
        });

        DB::table('cuentasporpagar')
            ->whereColumn('monto_pagado', '>', 'saldo_neto')
            ->update([
                'meses_cubiertos' => 2,
                'es_extra' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->dropIndex(['es_extra']);
            $table->dropColumn(['meses_cubiertos', 'es_extra']);
        });
    }
};
