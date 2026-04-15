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

        // Poblar mes_pago desde el JSON existente (portable para MySQL/PostgreSQL)
        DB::table('cuentasporpagar')
            ->select('id_cuentas_por_pagar', 'mesesdepago')
            ->whereNotNull('mesesdepago')
            ->orderBy('id_cuentas_por_pagar')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $mesPago = $this->extractMesPago($row->mesesdepago);

                    if (! $mesPago) {
                        continue;
                    }

                    DB::table('cuentasporpagar')
                        ->where('id_cuentas_por_pagar', $row->id_cuentas_por_pagar)
                        ->update(['mes_pago' => $mesPago]);
                }
            }, 'id_cuentas_por_pagar');

        // Eliminar duplicados: mantener el de mayor id por (id_contract, mes_pago)
        $duplicateGroups = DB::table('cuentasporpagar')
            ->select(
                'id_contract',
                'mes_pago',
                DB::raw('MAX(id_cuentas_por_pagar) as keep_id'),
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('id_contract')
            ->whereNotNull('mes_pago')
            ->groupBy('id_contract', 'mes_pago')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            DB::table('cuentasporpagar')
                ->where('id_contract', $group->id_contract)
                ->where('mes_pago', $group->mes_pago)
                ->where('id_cuentas_por_pagar', '<>', $group->keep_id)
                ->delete();
        }

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

    private function extractMesPago(mixed $rawMesesDepago): ?string
    {
        $payload = $rawMesesDepago;

        if (is_string($rawMesesDepago)) {
            $decoded = json_decode($rawMesesDepago, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        if (is_object($payload)) {
            $payload = (array) $payload;
        }

        if (! is_array($payload)) {
            return null;
        }

        $mes = isset($payload['mes']) ? trim((string) $payload['mes']) : '';
        if (! preg_match('/^\d{4}-\d{2}$/', $mes)) {
            return null;
        }

        return $mes;
    }
};
