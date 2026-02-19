<?php

namespace App\Http\Controllers\Factura;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\XmlFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Log;
use App\Services\DescripcionParser;
use Illuminate\Support\Facades\Auth;

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
            return redirect()->route('facturacion');
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

        return view('User.UserFactView', compact('factura', 'totalFacturas', 'index', 'projectMismatch', 'parsedProjectId', 'parsedProjectName', 'selectedProjectId', 'selectedProjectName', 'userMismatch', 'user'));
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

        // Aquí deberías guardar la factura confirmada en la base de datos
        // Por ejemplo:
        // $xmlFile = XmlFile::create([
        //     'uuid' => $currentFacturaData['xmlData']['timbreFiscalDigital']['UUID'],
        //     'file_path' => $currentFacturaData['file_path'], // Asumiendo que guardaste la ruta del archivo original
        //     // ... otros campos relevantes
        // ]);

        // O marcarla como confirmada de alguna manera en la sesión si no se guarda en DB inmediatamente
        // $allFacturasData[$index]['confirmed'] = true;

        // Eliminar la factura confirmada de la sesión para que no se muestre de nuevo
        array_splice($allFacturasData, $index, 1);
        session()->put('factura_data', $allFacturasData);

        // Redirigir a la siguiente factura o a una página de éxito si no hay más
        if (!empty($allFacturasData)) {
            // Ajustar el índice si la factura eliminada no era la última
            $nextIndex = ($index < count($allFacturasData)) ? $index : count($allFacturasData) - 1;
            return redirect()->route('user.factura.view', ['index' => $nextIndex]);
        } else {
            session()->forget('factura_data');
            return view('User.FacturaSuccess');
        }
    }

    public function deleteFactura(Request $request, $index)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData) || $index < 0 || $index >= count($allFacturasData)) {
            return redirect()->back()->withErrors(['message' => 'Factura no encontrada o índice inválido para eliminar.']);
        }

        // Eliminar la factura de la sesión
        array_splice($allFacturasData, $index, 1);
        session()->put('factura_data', $allFacturasData);

        // Redirigir a la siguiente factura o a una página de éxito si no hay más
        if (!empty($allFacturasData)) {
            // Ajustar el índice si la factura eliminada no era la última
            $nextIndex = ($index < count($allFacturasData)) ? $index : count($allFacturasData) - 1;
            return redirect()->route('user.factura.view', ['index' => $nextIndex]);
        } else {
            session()->forget('factura_data');
            return view('User.FacturaSuccess');
        }
    }

    public function resetFactura()
    {
        session()->forget('factura_data');
        return redirect()->route('facturacion');
    }
}