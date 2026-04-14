<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'filename',
        'id_user',
        'id_proyecto',
        'id_contract',
        'id_user_depto',
        'uuid',
        'is_valid',
        'fecha_inicio',
        'validation_errors',
        'validation_flags',
        'emisor_name',
        'receptor_name',
        'file_path',
        'pdf_filename',
        'pdf_path',
        'pdf_uploaded',
        'departamento',
        'predial_xml',
        'predial_status',
        'predial_observacion',
        'mes',
        'retroactivo',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'pdf_uploaded' => 'boolean',
        'validation_errors' => 'array',
        'validation_flags' => 'array',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'id_contract');
    }

    public function userDepto()
    {
        return $this->belongsTo(UserDepto::class, 'id_user_depto', 'id_user_depto');
    }

    public function impuestos()
    {
        return $this->hasMany(Impuesto::class, 'xml_file_id');
    }

    public function batch()
    {
        return $this->belongsTo(XmlBatch::class, 'batch_id');
    }

    public function getPdfExistsAttribute(): bool
    {
        $pdfPath = $this->pdf_path;

        if (empty($pdfPath)) {
            return (bool) $this->pdf_uploaded;
        }

        if (str_starts_with($pdfPath, 'pdf_files/')) {
            $fullPath = storage_path('app/public/'.$pdfPath);
        } elseif (str_starts_with($pdfPath, 'facturas/')) {
            $fullPath = storage_path('app/'.$pdfPath);
        } else {
            $fullPath = storage_path('app/public/pdf_files/'.$pdfPath);
        }

        return file_exists($fullPath);
    }

    public static function limpiarPdfHuérfanos(): int
    {
        $registrosLimpios = 0;
        $xmlFiles = static::whereNotNull('pdf_path')->where('pdf_path', '!=', '')->get();

        foreach ($xmlFiles as $xmlFile) {
            if (! $xmlFile->pdf_exists) {
                $xmlFile->update([
                    'pdf_path' => null,
                    'pdf_uploaded' => false,
                ]);
                $registrosLimpios++;
                \Log::info('Limpiado registro huérfano PDF - ID: '.$xmlFile->id.', UUID: '.$xmlFile->uuid);
            }
        }

        return $registrosLimpios;
    }
}
