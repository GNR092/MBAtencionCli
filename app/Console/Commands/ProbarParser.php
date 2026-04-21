<?php

namespace App\Console\Commands;

use App\Models\Proyecto;
use App\Services\DescripcionParser;
use Illuminate\Console\Command;

class ProbarParser extends Command
{
    protected $signature = 'parser:probar {descripcion?}';

    protected $description = 'Prueba el DescripcionParser con una descripción';

    public function handle(DescripcionParser $parser): int
    {
        $descripcion = $this->argument('descripcion');

        if (! $descripcion) {
            $plantillaPath = base_path('Plantilla.txt');
            if (! file_exists($plantillaPath)) {
                $this->error('No se encontró Plantilla.txt y no se proporcionó descripción.');

                return 1;
            }
            $descripcion = trim(file_get_contents($plantillaPath));
            $this->info("Usando Plantilla.txt:\n");
        }

        $this->info("Descripción: $descripcion\n");

        $proyectos = Proyecto::all()->toArray();

        $resultado = $parser->parsearDescripcion($descripcion, $proyectos);

        $this->table(
            ['Campo', 'Valor'],
            [
                ['Tipo Concepto', $resultado['tipo_concepto'] ?? '❌ No detectado'],
                ['Departamentos', implode(', ', $resultado['departamentos']) ?: '❌ No detectado'],
                ['Edificio', $resultado['edificio'] ?? '❌ No detectado'],
                ['Subcondominio', $resultado['subcondominio'] ?? 'N/A'],
                ['Fecha', $resultado['fecha']
                    ? $resultado['fecha']['mes_nombre'].' '.$resultado['fecha']['anio']
                    : '❌ No detectado'],
                ['Folio Predial', $resultado['folio_predial'] ?? '❌ No detectado'],
                ['Proyecto', $resultado['proyecto']
                    ? ($resultado['proyecto']['nombre_proyecto'] ?? json_encode($resultado['proyecto']))
                    : '❌ No detectado'],
            ]
        );

        return 0;
    }
}
