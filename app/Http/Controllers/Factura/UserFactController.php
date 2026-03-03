<?php

namespace App\Http\Controllers\Factura;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\XmlFile;
use App\Models\Impuesto;
use Illuminate\Support\Facades\Storage;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Log;
use App\Services\DescripcionParser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\CuentasPorCobrar;

class UserFactController extends Controller
{
    public function showInvoice(Request $request, $index = 0)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData)) {
            return view('User.FacturaError');
        }

        $totalFacturas = count($allFacturasData);

        if ($index < 0 || $index >= $totalFacturas) {
            session()->forget('factura_data');
            return redirect()->route('facturas.index');
        }

        $currentFacturaData = $allFacturasData[$index];

        if (!is_array($currentFacturaData) || !isset($currentFacturaData['xmlData'])) {
            return redirect()->back()->withErrors(['message' => 'Datos de factura inválidos para el índice actual.']);
        }

        $xmlData = $currentFacturaData['xmlData'];

        $factura = $this->MapingFacturas($xmlData);

        // Validar que el emisor de la factura coincida con el usuario autenticado
        $user = Auth::user();
        $userMismatch = false;
        if (mb_strtolower(trim($factura['emisor_nombre'])) !== mb_strtolower(trim($user->name))) {
            $userMismatch = true;
        }

        $proyecto = Proyecto::find($currentFacturaData['select_project']);
        $factura['idproyect'] = $proyecto->id_proyecto ?? null;
        $factura['nombre_proyecto'] = $proyecto->nombre_proyecto ?? 'Proyecto Desconocido';

        $AllProyects = Proyecto::all()->toArray();
        $parser = new DescripcionParser();

        $des = $factura['conceptos'][0]['descripcion'];
        $parsedProject = $parser->parsearDescripcion($des, $AllProyects);
        $parsedProjectInfo = $parsedProject['proyecto'] ?? null;
        $parsedProjectId = $parsedProjectInfo['id_proyecto'] ?? null;
        $parsedProjectName = $parsedProjectInfo['nombre_proyecto'] ?? 'No detectado';
        $selectedProjectId = $currentFacturaData['select_project'];
        $selectedProjectName = $proyecto->nombre_proyecto ?? 'N/A';

        $projectMismatch = false;
        if ($parsedProjectId === null || (int)$parsedProjectId !== (int)$selectedProjectId) {
            $projectMismatch = true;
        }

        $uuid = $factura['uuid'] !== 'N/A' ? $factura['uuid'] : null;
        $uuidExists = $uuid && XmlFile::where('uuid', $uuid)->exists();

        return view('User.UserFactView', compact('factura', 'totalFacturas', 'index', 'projectMismatch', 'parsedProjectId', 'parsedProjectName', 'selectedProjectId', 'selectedProjectName', 'userMismatch', 'user', 'uuidExists'));
    }

    private function MapingFacturas(array $xmlData): array
    {
        // Mapear datos del comprobante
        $comprobante = $xmlData['comprobante'] ?? [];
        $emisor = $xmlData['emisor'] ?? [];
        $receptor = $xmlData['receptor'] ?? [];
        $impuestos = $xmlData['impuestos'] ?? [];
        $timbre = $xmlData['timbreFiscalDigital'] ?? [];

        $factura = [
            'folio'          => $comprobante['Folio'] ?? 'N/A',
            'fecha'          => $comprobante['Fecha'] ?? 'N/A',
            'forma_pago'     => $comprobante['FormaPago'] ?? 'N/A',
            'no_certificado' => $comprobante['NoCertificado'] ?? 'N/A',
            'subtotal'       => (float) ($comprobante['SubTotal'] ?? 0),
            'total'          => (float) ($comprobante['Total'] ?? 0),

            'emisor_rfc'     => $emisor['Rfc'] ?? 'N/A',
            'emisor_nombre'  => $emisor['Nombre'] ?? 'N/A',
            'emisor_regimen' => $emisor['RegimenFiscal'] ?? 'N/A',

            'receptor_rfc'        => $receptor['Rfc'] ?? 'N/A',
            'receptor_nombre'     => $receptor['Nombre'] ?? 'N/A',
            'receptor_domicilio'  => $receptor['DomicilioFiscalReceptor'] ?? 'N/A',
            'receptor_uso_cfdi'   => $receptor['UsoCFDI'] ?? 'N/A',

            'conceptos'              => [],
            'impuestos_traslados'    => [],
            'impuestos_retenciones'  => [],
            'total_retenciones'      => (float) ($impuestos['TotalImpuestosRetenidos'] ?? 0),

            'uuid'               => $timbre['UUID'] ?? 'N/A',
            'fecha_timbrado'     => $timbre['FechaTimbrado'] ?? 'N/A',
            'no_certificado_sat' => $timbre['NoCertificadoSAT'] ?? 'N/A',
            'rfc_prov_certif'    => $timbre['RfcProvCertif'] ?? 'N/A',
            'sello_cfd'          => $xmlData['sello_cfd'] ?? 'N/A',
            'sello_sat'          => $xmlData['sello_sat'] ?? 'N/A',
        ];

        // Procesar conceptos con sus impuestos
        foreach ($xmlData['conceptos'] ?? [] as $concept) {
            $concepto = [
                'clave_prod_serv' => $concept['ClaveProdServ'] ?? 'N/A',
                'descripcion'     => $concept['Descripcion'] ?? 'N/A',
                'cantidad'        => (float) ($concept['Cantidad'] ?? 0),
                'unidad'          => $concept['Unidad'] ?? 'N/A',
                'valor_unitario'  => (float) ($concept['ValorUnitario'] ?? 0),
                'importe'         => (float) ($concept['Importe'] ?? 0),
                'objeto_imp'      => $concept['ObjetoImp'] ?? 'N/A',
                'traslados'       => [],
                'retenciones'     => [],
                'cuenta_predial'  => $concept['CuentaPredial']['Numero'] ?? null,
            ];

            // Traslados del concepto
            foreach ($concept['Impuestos']['Traslados'] ?? [] as $traslado) {
                $trasladoData = [
                    'impuesto'    => $traslado['Impuesto'] ?? 'N/A',
                    'tipo_factor' => $traslado['TipoFactor'] ?? 'N/A',
                    'tasa'        => (float) ($traslado['TasaOCuota'] ?? 0),
                    'importe'     => (float) ($traslado['Importe'] ?? 0),
                ];
                $concepto['traslados'][] = $trasladoData;

                $factura['impuestos_traslados'][] = [
                    'impuesto'    => $trasladoData['impuesto'],
                    'tipo_factor' => $trasladoData['tipo_factor'],
                    'importe'     => $trasladoData['importe'],
                ];
            }

            // Retenciones del concepto
            foreach ($concept['Impuestos']['Retenciones'] ?? [] as $retencion) {
                $retencionData = [
                    'impuesto' => $retencion['Impuesto'] ?? 'N/A',
                    'tasa'     => (float) ($retencion['TasaOCuota'] ?? 0),
                    'importe'  => (float) ($retencion['Importe'] ?? 0),
                ];
                $concepto['retenciones'][] = $retencionData;

                $factura['impuestos_retenciones'][] = [
                    'impuesto' => $retencionData['impuesto'],
                    'importe'  => $retencionData['importe'],
                ];
            }

            $factura['conceptos'][] = $concepto;
        }

        return $factura;
    }

    public function confirmFactura(Request $request, $index)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData) || $index < 0 || $index >= count($allFacturasData)) {
            return redirect()->back()->withErrors(['message' => 'Factura no encontrada o índice inválido para confirmar.']);
        }

        $currentFacturaData = $allFacturasData[$index];
        $xmlData            = $currentFacturaData['xmlData'];
        $tmpFilePath        = $currentFacturaData['file_path'];
        $selectedProjectId  = $currentFacturaData['select_project'];

        $user    = Auth::user();
        $factura = $this->MapingFacturas($xmlData);

        // Extraer metadatos de la descripción del primer concepto
        $parser      = new DescripcionParser();
        $allProyects = Proyecto::all()->toArray();
        $des         = $factura['conceptos'][0]['descripcion'] ?? '';
        $parsedData  = $parser->parsearDescripcion($des, $allProyects);

        $departamento = implode(',', $parsedData['departamentos'] ?? []) ?: null;
        $mes          = $parsedData['fecha']['mes_nombre'] ?? null;

        // Ruta organizada en disco privado: facturas/{user_id}/xml/{año}/{mes}/{archivo}
        $carbonFecha = $this->parseFechaFactura($factura['fecha']);
        $filename    = basename($tmpFilePath);
        $newFilePath = $this->buildXmlStoragePath($user->id, $carbonFecha, $filename);

        $xmlFile = null;

        try {
            DB::transaction(function () use (
                $factura,
                $user,
                $selectedProjectId,
                $tmpFilePath,
                $newFilePath,
                $filename,
                $departamento,
                $mes,
                $carbonFecha,
                &$xmlFile
            ) {
                // Mover XML de almacenamiento temporal al almacenamiento privado organizado
                $contents = Storage::disk('tmp')->get($tmpFilePath);
                Storage::disk('local')->put($newFilePath, $contents);

                $xmlFile = XmlFile::create([
                    'filename'      => $filename,
                    'id_user'       => $user->id,
                    'id_proyecto'   => $selectedProjectId ?: null,
                    'uuid'          => $factura['uuid'] !== 'N/A' ? $factura['uuid'] : null,
                    'is_valid'      => true,
                    'fecha_inicio'  => $carbonFecha?->toDateString(),
                    'emisor_name'   => $factura['emisor_nombre'],
                    'receptor_name' => $factura['receptor_nombre'],
                    'file_path'     => $newFilePath,
                    'departamento'  => $departamento,
                    'mes'           => $mes,
                ]);

                $this->saveImpuestos($xmlFile->id, $factura, $user->id);

                // Eliminar el XML temporal solo si el registro en BD fue exitoso
                Storage::disk('tmp')->delete($tmpFilePath);
            });
        } catch (\Exception $e) {
            Log::error('Error al confirmar factura [index=' . $index . ']: ' . $e->getMessage());
            return redirect()->back()->withErrors(['message' => 'Ocurrió un error al guardar la factura. Por favor intenta de nuevo.']);
        }

        // Vincular el XML a la cuenta por cobrar correspondiente
        if ($xmlFile) {
            (new CuentasPorCobrar())->actualizarConXML($xmlFile);
        }

        array_splice($allFacturasData, $index, 1);
        session()->put('factura_data', $allFacturasData);

        if (!empty($allFacturasData)) {
            $nextIndex = ($index < count($allFacturasData)) ? $index : count($allFacturasData) - 1;
            return redirect()->route('user.factura.view', ['index' => $nextIndex]);
        }

        session()->forget('factura_data');
        return view('User.FacturaSuccess');
    }

    public function deleteFactura(Request $request, $index)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData) || $index < 0 || $index >= count($allFacturasData)) {
            return redirect()->back()->withErrors(['message' => 'Factura no encontrada o índice inválido para eliminar.']);
        }

        // Limpiar el archivo temporal del disco
        $tmpFilePath = $allFacturasData[$index]['file_path'] ?? null;
        if ($tmpFilePath && Storage::disk('tmp')->exists($tmpFilePath)) {
            Storage::disk('tmp')->delete($tmpFilePath);
        }

        array_splice($allFacturasData, $index, 1);
        session()->put('factura_data', $allFacturasData);

        if (!empty($allFacturasData)) {
            $nextIndex = ($index < count($allFacturasData)) ? $index : count($allFacturasData) - 1;
            return redirect()->route('user.factura.view', ['index' => $nextIndex]);
        }

        session()->forget('factura_data');
        return view('User.FacturaSuccess');
    }

    public function resetFactura()
    {
        // Limpiar todos los XMLs temporales antes de borrar la sesión
        $allFacturasData = session()->get('factura_data', []);
        foreach ($allFacturasData as $facturaData) {
            $tmpFilePath = $facturaData['file_path'] ?? null;
            if ($tmpFilePath && Storage::disk('tmp')->exists($tmpFilePath)) {
                Storage::disk('tmp')->delete($tmpFilePath);
            }
        }

        session()->forget('factura_data');
        return redirect()->route('user.facturacion');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Construye la ruta de almacenamiento organizada por usuario, año y mes.
     *
     * Estructura resultante en el disco 'local' (storage/app/private/):
     *   facturas/{user_id}/xml/{año}/{mes}/{archivo.xml}
     *
     * Esto permite:
     *   - Respaldar todos los archivos de un usuario con: facturas/{user_id}/
     *   - Verificar archivos por periodo: facturas/{user_id}/xml/2025/09/
     */
    private function buildXmlStoragePath(int $userId, ?Carbon $fecha, string $filename): string
    {
        $year  = $fecha?->format('Y') ?? now()->format('Y');
        $month = $fecha?->format('m') ?? now()->format('m');

        return "facturas/{$userId}/xml/{$year}/{$month}/{$filename}";
    }

    /**
     * Parsea la fecha del campo Fecha del CFDI (ISO 8601 o fecha simple).
     */
    private function parseFechaFactura(string $fecha): ?Carbon
    {
        try {
            return Carbon::parse($fecha);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Crea registros de Impuesto para cada traslado de cada concepto.
     * Si el concepto tiene retención de ISR (código 001), se asocia al mismo registro.
     * Si no existe ISR en el XML, se calcula usando el régimen fiscal del usuario.
     */
    private function saveImpuestos(int $xmlFileId, array $factura, int $userId): void
    {
        $regimenFiscal = $factura['emisor_regimen'];

        $tasaRetencion = 0;
        $regimenData = DB::table('regimen_fiscals')
            ->where('id_regimen', $regimenFiscal)
            ->first();
        if ($regimenData) {
            $tasaRetencion = floatval($regimenData->tasa_retencion);
        }

        foreach ($factura['conceptos'] as $concepto) {
            $importeBase = $concepto['importe'];

            foreach ($concepto['traslados'] as $traslado) {
                $isrImporte = collect($concepto['retenciones'])
                    ->where('impuesto', '001')
                    ->sum('importe');

                if ($isrImporte == 0 && $tasaRetencion > 0) {
                    $isrImporte = $importeBase * $tasaRetencion;
                }

                Impuesto::create([
                    'xml_file_id'   => $xmlFileId,
                    'tipoFactor'    => $traslado['tipo_factor'],
                    'regimenFiscal' => $regimenFiscal,
                    'importeBase'   => $importeBase,
                    'tasaCuota'     => $traslado['tasa'],
                    'tasaRetencion' => $tasaRetencion,
                    'isr'           => $isrImporte,
                ]);
            }
        }
    }
}