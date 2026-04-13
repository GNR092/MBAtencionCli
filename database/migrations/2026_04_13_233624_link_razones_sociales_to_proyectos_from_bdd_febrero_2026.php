<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $razones = DB::table('razones_sociales')
            ->whereIn('nombre_razon_social', [
                'MBSP RENTAS',
                'MB SIGNATURE PROPERTIES',
                'RESIDENCIAS UNIVERSITARIAS DEL MAYAB',
            ])
            ->pluck('id_razon_social', 'nombre_razon_social');

        if ($razones->isEmpty()) {
            return;
        }

        $mapping = [
            'MBSP RENTAS' => [
                'Aldea Borboleta I',
                'Aldea Borboleta II',
                'Aldea Borboleta III',
                'RESIDENT 1',
                'RESIDENT 2',
                'CAMPUS RECIDENCIA',
                'TMZN 122',
            ],
            'MB SIGNATURE PROPERTIES' => [
                'Avenue Temozon',
                'GRAND TEMOZON',
                'MB RESORT MERIDA',
                'MB Resort Orlando',
            ],
            'RESIDENCIAS UNIVERSITARIAS DEL MAYAB' => [
                'RUM',
            ],
        ];

        $normalize = static function (string $value): string {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            $base = $ascii === false ? $value : $ascii;
            $base = mb_strtoupper($base);

            return preg_replace('/[^A-Z0-9]+/', '', $base) ?? '';
        };

        $proyectos = DB::table('proyectos')
            ->select('id_proyecto', 'nombre_proyecto')
            ->get();

        $proyectosLookup = [];
        foreach ($proyectos as $proyecto) {
            $key = $normalize((string) $proyecto->nombre_proyecto);
            if ($key === '' || isset($proyectosLookup[$key])) {
                continue;
            }

            $proyectosLookup[$key] = (int) $proyecto->id_proyecto;
        }

        foreach ($mapping as $razonNombre => $projectNames) {
            $razonId = $razones->get($razonNombre);
            if ($razonId === null) {
                continue;
            }

            foreach ($projectNames as $projectName) {
                $projectKey = $normalize($projectName);
                $projectId = $proyectosLookup[$projectKey] ?? null;

                if ($projectId === null) {
                    continue;
                }

                DB::table('proyectos')
                    ->where('id_proyecto', $projectId)
                    ->update([
                        'id_razon_social' => $razonId,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        $projectNames = [
            'Aldea Borboleta I',
            'Aldea Borboleta II',
            'Aldea Borboleta III',
            'RESIDENT 1',
            'RESIDENT 2',
            'CAMPUS RECIDENCIA',
            'TMZN 122',
            'Avenue Temozon',
            'GRAND TEMOZON',
            'MB RESORT MERIDA',
            'MB Resort Orlando',
            'RUM',
        ];

        $normalize = static function (string $value): string {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            $base = $ascii === false ? $value : $ascii;
            $base = mb_strtoupper($base);

            return preg_replace('/[^A-Z0-9]+/', '', $base) ?? '';
        };

        $targets = array_fill_keys(array_map($normalize, $projectNames), true);
        $ids = DB::table('proyectos')
            ->select('id_proyecto', 'nombre_proyecto')
            ->get()
            ->filter(fn ($proyecto) => isset($targets[$normalize((string) $proyecto->nombre_proyecto)]))
            ->pluck('id_proyecto')
            ->all();

        if ($ids === []) {
            return;
        }

        DB::table('proyectos')
            ->whereIn('id_proyecto', $ids)
            ->update([
                'id_razon_social' => null,
                'updated_at' => now(),
            ]);
    }
};
