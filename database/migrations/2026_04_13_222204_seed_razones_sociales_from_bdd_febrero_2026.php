<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $items = [
            ['nombre_razon_social' => 'MB SIGNATURE PROPERTIES', 'rfc' => 'MSP220504I99'],
            ['nombre_razon_social' => 'MBSP RENTAS', 'rfc' => 'MRE230623IH5'],
            ['nombre_razon_social' => 'RESIDENCIAS UNIVERSITARIAS DEL MAYAB', 'rfc' => 'RUM000000TEMP'],
        ];

        $existing = DB::table('razones_sociales')
            ->pluck('nombre_razon_social')
            ->filter()
            ->map(fn ($value) => mb_strtoupper(trim((string) $value)))
            ->values()
            ->all();

        $existingLookup = array_fill_keys($existing, true);
        $existingRfc = DB::table('razones_sociales')
            ->pluck('rfc')
            ->filter()
            ->map(fn ($value) => mb_strtoupper(trim((string) $value)))
            ->values()
            ->all();
        $existingRfcLookup = array_fill_keys($existingRfc, true);
        $now = now();
        $rows = [];

        foreach ($items as $item) {
            $cleanName = preg_replace('/\s+/u', ' ', trim((string) ($item['nombre_razon_social'] ?? '')));
            $cleanRfc = mb_strtoupper(trim((string) ($item['rfc'] ?? '')));
            if ($cleanName === null || $cleanName === '' || $cleanRfc === '') {
                continue;
            }

            $key = mb_strtoupper($cleanName);
            if (isset($existingLookup[$key]) || isset($existingRfcLookup[$cleanRfc])) {
                continue;
            }

            $existingLookup[$key] = true;
            $existingRfcLookup[$cleanRfc] = true;
            $rows[] = [
                'nombre_razon_social' => $cleanName,
                'rfc' => $cleanRfc,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('razones_sociales')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op para evitar eliminar registros preexistentes.
    }
};
