<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->string('uuid', 36)->nullable()->unique()->after('id_cuentas_por_pagar');
            $table->string('mes_pago', 7)->nullable()->after('uuid')->comment('Y-m extraído de mesesdepago->mes');
        });

        // Poblar mes_pago desde el JSON existente
        DB::statement("
            UPDATE cuentasporpagar
            SET mes_pago = JSON_UNQUOTE(JSON_EXTRACT(mesesdepago, '$.mes'))
            WHERE mesesdepago IS NOT NULL
              AND JSON_EXTRACT(mesesdepago, '$.mes') IS NOT NULL
        ");

        // Eliminar duplicados: mantener el de mayor id por (id_contract, mes_pago)
        DB::statement('
            DELETE c1 FROM cuentasporpagar c1
            INNER JOIN cuentasporpagar c2
              ON  c1.id_contract = c2.id_contract
              AND c1.mes_pago    = c2.mes_pago
              AND c1.id_cuentas_por_pagar < c2.id_cuentas_por_pagar
        ');

        // Generar UUID para registros existentes
        $rows = DB::table('cuentasporpagar')->whereNull('uuid')->pluck('id_cuentas_por_pagar');
        foreach ($rows as $id) {
            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $id)
                ->update(['uuid' => Str::uuid()->toString()]);
        }

        // Índice único compuesto para prevenir futuros duplicados
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->unique(['id_contract', 'mes_pago'], 'uq_contract_mes_pago');
        });
    }

    public function down(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->dropUnique('uq_contract_mes_pago');
            $table->dropUnique(['uuid']);
            $table->dropColumn(['uuid', 'mes_pago']);
        });
    }
};
