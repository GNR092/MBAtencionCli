<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PdfUuidExtractionService;

class TestPdfUuid extends Command
{
    protected $signature = 'test:pdf-uuid {file}';
    protected $description = 'Probar extracción de UUID desde un PDF';

    private $pdfService;

    public function __construct(PdfUuidExtractionService $pdfService)
    {
        parent::__construct();
        $this->pdfService = $pdfService;
    }

    public function handle()
    {
        $file = $this->argument('file');
        $path = storage_path('app/pdf_files/' . $file);

        if (!file_exists($path)) {
            $this->error("❌ El archivo no existe: $path");
            return 1;
        }

        $uuid = $this->pdfService->extractUuidFromPdf($path);

        if ($uuid) {
            $this->info("✅ UUID detectado: " . $uuid);
        } else {
            $this->warn("❌ No se pudo extraer UUID del PDF.");
        }

        return 0;
    }
}
