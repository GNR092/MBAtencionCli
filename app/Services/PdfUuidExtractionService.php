<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class PdfUuidExtractionService
{
    public function extractUuidFromPdf(string $pdfPath): ?string
    {
        try {
            if (!file_exists($pdfPath)) {
                Log::error("PDF no encontrado");
                return null;
            }

            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);

            
            $text = $pdf->getText();

            Log::info("Texto extraído del PDF:");
            Log::debug(substr($text, 0, 1000));

            
            $uuidPattern = '/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}/';

            if (preg_match($uuidPattern, $text, $matches)) {
                $uuid = strtolower($matches[0]);
                Log::info("UUID válido encontrado en PDF: {$uuid}");
                return $uuid;
            }

            Log::error("No se encontró UUID CFDI en el texto del PDF");
            return null;

        } catch (\Throwable $e) {
            Log::error("Error al extraer UUID del PDF: " . $e->getMessage());
            return null;
        }
    }
}
