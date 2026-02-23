<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            // Agregar índice regular primero para que la FK de id_contract lo use
            $table->index('id_contract', 'idx_contract_id');
        });
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->dropUnique('uq_contract_mes_pago');
        });
    }

    public function down(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->unique(['id_contract', 'mes_pago'], 'uq_contract_mes_pago');
            $table->dropIndex('idx_contract_id');
        });
    }
};
