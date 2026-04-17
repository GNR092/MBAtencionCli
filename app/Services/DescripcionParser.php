<?php

namespace App\Services;

class DescripcionParser
{
    private const MESES = [
        'enero' => 1, 'ene' => 1,
        'febrero' => 2, 'feb' => 2,
        'marzo' => 3, 'mar' => 3,
        'abril' => 4, 'abr' => 4,
        'mayo' => 5, 'may' => 5,
        'junio' => 6, 'jun' => 6,
        'julio' => 7, 'jul' => 7,
        'agosto' => 8, 'ago' => 8,
        'septiembre' => 9, 'sep' => 9, 'sept' => 9,
        'octubre' => 10, 'oct' => 10,
        'noviembre' => 11, 'nov' => 11,
        'diciembre' => 12, 'dic' => 12,
    ];

    private const NOMBRES_MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    private function normalizar(string $texto): string
    {
        // Reemplazar saltos de linea por espacio
        $texto = str_replace(["\n", "\r", "\t"], ' ', $texto);
        // Reducir espacios multiples a uno
        $texto = preg_replace('/\s+/', ' ', trim($texto));

        return $texto;
    }

    private function normalizarComparacion(string $texto): string
    {
        $texto = $this->normalizar($texto);
        $texto = mb_strtolower($texto);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
        $texto = $ascii !== false ? $ascii : $texto;
        $texto = preg_replace('/[^a-z0-9]+/i', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', trim((string) $texto));

        return $texto;
    }

    private function detectarProyectoSoloPrimeraPalabra(): bool
    {
        return (bool) config('descripcion_parser.solo_primera_palabra_proyecto', false);
    }

    /**
     * Extrae numero(s) de departamento de la descripcion.
     * Retorna un array porque puede haber multiples deptos (ej: "A3,A4,A6,A8").
     *
     * Patrones detectados:
     * - "Depto 2203"
     * - "Departamento 73205"
     * - "DEPARTAMENTO A5"
     * - "departamento 6206"
     * - "Departamento A107Aldea" (pegado al proyecto)
     * - "depto A3,A4,A6,A8" (multiples)
     */
    public function extraerDepartamentos(string $descripcion): array
    {
        $descripcion = $this->normalizar($descripcion);

        // Patron principal: palabra clave + numero(s) de depto
        // Captura alfanumericos separados por comas, o un solo depto que puede estar pegado a otra palabra
        $patrones = [
            // Multiples deptos separados por coma: "depto A3,A4,A6,A8"
            '/\b(?:depto|departamento|dept|dpto|dep)\.?\s*(?:no\.?\s*)?#?\s*((?:[a-zA-Z]?\d+[a-zA-Z]?\s*,\s*)+[a-zA-Z]?\d+[a-zA-Z]?)\b/iu',
            // Depto alfanumerico que puede estar pegado a la siguiente palabra: "A107Aldea"
            '/\b(?:depto|departamento|dept|dpto|dep)\.?\s*(?:no\.?\s*)?#?\s*([a-zA-Z]?\d+[a-zA-Z]?)/iu',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $descripcion, $matches)) {
                $raw = trim($matches[1]);

                // Si tiene comas, separar multiples deptos
                if (str_contains($raw, ',')) {
                    $deptos = array_map('trim', explode(',', $raw));

                    return array_values(array_filter($deptos));
                }

                return [strtoupper($raw)];
            }
        }

        return [];
    }

    /**
     * Extrae el numero de edificio de la descripcion.
     *
     * Patrones detectados:
     * - "Edif 2"
     * - "edificio 7"
     */
    public function extraerEdificio(string $descripcion): ?string
    {
        $descripcion = $this->normalizar($descripcion);

        $patrones = [
            '/\b(?:edif|edificio|torre|edif\.)\.?\s*(?:no\.?\s*)?#?\s*(\d+[a-zA-Z]?)\b/iu',
            '/\b(?:edif|edificio|torre|edif\.)\.?\s*[-–]\s*(\d+[a-zA-Z]?)\b/iu',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $descripcion, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extrae el subcondominio de la descripcion.
     *
     * Patrones detectados:
     * - "Sub condominio 5"
     * - "subcondominio campus university"
     * - "SUBCONDOMINIO 2"
     * - "Condominio Campus University City"
     */
    public function extraerSubcondominio(string $descripcion): ?string
    {
        $descripcion = $this->normalizar($descripcion);

        // Subcondominio con numero
        if (preg_match('/\b(?:sub\s*condominio|subcondominio)\s+(\d+)\b/iu', $descripcion, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Busca el nombre del proyecto en la descripcion comparando
     * contra la lista de proyectos de la base de datos.
     *
     * Proyectos encontrados en los XMLs:
     * - "Campus University City"
     * - "Campus residencia" (truncado como "RESIDEN")
     * - "campus university"
     * - "Aldea Borboleta II"
     * - "Residencial campus university"
     */
    public function extraerProyecto(string $descripcion, array $proyectos): ?array
    {
        $descripcionNormalizada = $this->normalizarComparacion($descripcion);
        if ($descripcionNormalizada === '') {
            return null;
        }

        $soloPrimeraPalabra = $this->detectarProyectoSoloPrimeraPalabra();
        if ($soloPrimeraPalabra) {
            $descripcionNormalizada = explode(' ', $descripcionNormalizada)[0] ?? '';
            if ($descripcionNormalizada === '') {
                return null;
            }
        }

        $mejorCoincidencia = null;
        $mejorPuntaje = 0;
        $candidatos = [];

        foreach ($proyectos as $proyecto) {
            // Soporta tanto array ['nombre' => '...'] como objeto Eloquent ->nombre
            $nombreProyecto = is_array($proyecto) ? ($proyecto['nombre_proyecto'] ?? '') : ($proyecto->nombre_proyecto ?? '');

            if (empty($nombreProyecto)) {
                continue;
            }

            $nombreNormalizado = $this->normalizarComparacion($nombreProyecto);
            if ($nombreNormalizado === '') {
                continue;
            }

            $candidatos[] = [
                'raw' => $proyecto,
                'nombre_normalizado' => $nombreNormalizado,
                'len' => strlen($nombreNormalizado),
            ];
        }

        if (empty($candidatos)) {
            return null;
        }

        if ($soloPrimeraPalabra) {
            foreach ($candidatos as $candidato) {
                $primeraPalabraProyecto = explode(' ', $candidato['nombre_normalizado'])[0] ?? '';

                if ($primeraPalabraProyecto === $descripcionNormalizada) {
                    return is_array($candidato['raw']) ? $candidato['raw'] : $candidato['raw']->toArray();
                }
            }

            return null;
        }

        // Priorizar nombres más largos evita que "Aldea Borboleta I" gane sobre "Aldea Borboleta II".
        usort($candidatos, fn ($a, $b) => $b['len'] <=> $a['len']);

        // 1) Coincidencia exacta por frase completa (con límites de palabra)
        foreach ($candidatos as $candidato) {
            $nombre = $candidato['nombre_normalizado'];
            $regex = '/(?:^|\s)'.preg_quote($nombre, '/').'(?:\s|$)/i';

            if (preg_match($regex, $descripcionNormalizada)) {
                return is_array($candidato['raw']) ? $candidato['raw'] : $candidato['raw']->toArray();
            }
        }

        $palabrasDescripcion = explode(' ', $descripcionNormalizada);

        // 2) Fallback por puntaje para textos truncados/variantes
        foreach ($candidatos as $candidato) {
            $nombreLower = $candidato['nombre_normalizado'];
            $palabrasProyecto = array_filter(
                explode(' ', $nombreLower),
                fn ($p) => mb_strlen($p) > 2 || in_array($p, ['i', 'ii', 'iii', 'iv', 'v', '1', '2', '3', '4', '5'], true)
            );

            if (empty($palabrasProyecto)) {
                continue;
            }

            $palabrasEncontradas = 0;
            foreach ($palabrasProyecto as $palabra) {
                // Coincidencia exacta por token
                if (in_array($palabra, $palabrasDescripcion, true)) {
                    $palabrasEncontradas++;

                    continue;
                }

                // Busqueda parcial inversa: si la palabra del proyecto contiene
                // alguna palabra truncada de la descripcion (min 4 chars)
                foreach ($palabrasDescripcion as $palDesc) {
                    if (mb_strlen($palDesc) >= 4 && mb_strpos($palabra, $palDesc) !== false) {
                        $palabrasEncontradas += 0.6;
                        break;
                    }
                    // Tambien al reves: "residen" esta dentro de "residencial"?
                    if (mb_strlen($palDesc) >= 4 && mb_strpos($palDesc, $palabra) !== false) {
                        $palabrasEncontradas += 0.6;
                        break;
                    }
                }
            }

            $puntaje = $palabrasEncontradas / count($palabrasProyecto);

            if ($puntaje > $mejorPuntaje && $puntaje >= 0.6) {
                $mejorPuntaje = $puntaje;
                $mejorCoincidencia = is_array($candidato['raw']) ? $candidato['raw'] : $candidato['raw']->toArray();
            }
        }

        return $mejorCoincidencia;
    }

    /**
     * Extrae la fecha (mes y año) de la descripcion.
     *
     * Patrones detectados en los XMLs:
     * - "SEPTIEMBRE DE 2025"
     * - "julio 2025"
     * - "Junio 2025"
     * - "AGOSTO 2025"
     * - "Enero 2025"
     * - "JULIO 2025"
     */
    public function extraerFecha(string $descripcion): ?array
    {
        $descripcion = $this->normalizar($descripcion);

        $nombresMeses = implode('|', array_keys(self::MESES));

        // Patron 1: "mes de julio 2025" o "SEPTIEMBRE DE 2025" o "Enero 2025"
        $patron1 = '/\b('.$nombresMeses.')\.?\s*(?:de|del)?\s*[-–]?\s*(\d{4})\b/iu';

        if (preg_match($patron1, $descripcion, $matches)) {
            $mesTexto = mb_strtolower(trim($matches[1]));
            $anio = (int) $matches[2];
            $mesNumero = self::MESES[$mesTexto] ?? null;

            if ($mesNumero) {
                return [
                    'mes' => $mesNumero,
                    'mes_nombre' => self::NOMBRES_MESES[$mesNumero],
                    'anio' => $anio,
                    'texto_original' => trim($matches[0]),
                ];
            }
        }

        // Patron 2: "mes de julio del 2025"
        $patron2 = '/\bmes\s+de\s+('.$nombresMeses.')\.?\s*(?:de|del)?\s*(\d{4})\b/iu';

        if (preg_match($patron2, $descripcion, $matches)) {
            $mesTexto = mb_strtolower(trim($matches[1]));
            $anio = (int) $matches[2];
            $mesNumero = self::MESES[$mesTexto] ?? null;

            if ($mesNumero) {
                return [
                    'mes' => $mesNumero,
                    'mes_nombre' => self::NOMBRES_MESES[$mesNumero],
                    'anio' => $anio,
                    'texto_original' => trim($matches[0]),
                ];
            }
        }

        // Patron 3: "09/2025" o "09-2025"
        if (preg_match('/\b(0?[1-9]|1[0-2])\s*[\/\-]\s*(\d{4})\b/', $descripcion, $matches)) {
            $mesNumero = (int) $matches[1];
            $anio = (int) $matches[2];

            return [
                'mes' => $mesNumero,
                'mes_nombre' => self::NOMBRES_MESES[$mesNumero],
                'anio' => $anio,
                'texto_original' => trim($matches[0]),
            ];
        }

        return null;
    }

    /**
     * Extrae el folio predial o tablaje catastral del texto de la descripcion.
     * Nota: Esto es diferente al nodo <CuentaPredial> del XML.
     *
     * Patrones detectados:
     * - "Folio Predial 457295"
     * - "tablaje catastral 20474"
     * - "CUENTA PREDIAL 540886"
     * - "NUMERO CATASTRAL 6403"
     */
    public function extraerFolioPredial(string $descripcion): ?string
    {
        $descripcion = $this->normalizar($descripcion);

        $patrones = [
            '/\b(?:folio\s+predial|cuenta\s+predial|tablaje\s+catastral|numero\s+catastral|num\.?\s+catastral)\s+(\d+)\b/iu',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $descripcion, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Detecta el tipo de concepto: arrendamiento, renta, etc.
     *
     * Patrones detectados:
     * - "Arrendamiento"
     * - "Renta"
     * - "RENTA"
     * - "Renta del local"
     */
    public function extraerTipoConcepto(string $descripcion): ?string
    {
        $descripcion = $this->normalizar($descripcion);

        $tipos = [
            'arrendamiento' => '/\barrendamiento\b/iu',
            'renta' => '/\brenta\b/iu',
            'mantenimiento' => '/\bmantenimiento\b/iu',
            'cuota' => '/\bcuota\b/iu',
        ];

        foreach ($tipos as $tipo => $patron) {
            if (preg_match($patron, $descripcion)) {
                return ucfirst($tipo);
            }
        }

        return null;
    }

    /**
     * Extrae todo de una sola vez para un concepto.
     */
    public function parsearDescripcion(string $descripcion, array $proyectos = []): array
    {
        return [
            'tipo_concepto' => $this->extraerTipoConcepto($descripcion),
            'departamentos' => $this->extraerDepartamentos($descripcion),
            'edificio' => $this->extraerEdificio($descripcion),
            'subcondominio' => $this->extraerSubcondominio($descripcion),
            'proyecto' => $this->extraerProyecto($descripcion, $proyectos),
            'fecha' => $this->extraerFecha($descripcion),
            'folio_predial' => $this->extraerFolioPredial($descripcion),
            'descripcion_original' => $descripcion,
        ];
    }

    /**
     * Parsea todos los conceptos de una factura de una vez.
     */
    public function parsearConceptos(array $conceptos, array $proyectos = []): array
    {
        $resultados = [];

        foreach ($conceptos as $concepto) {
            $descripcion = $concepto['Descripcion'] ?? $concepto['descripcion'] ?? '';
            $resultados[] = array_merge(
                $this->parsearDescripcion($descripcion, $proyectos),
                ['concepto_original' => $concepto]
            );
        }

        return $resultados;
    }
}
