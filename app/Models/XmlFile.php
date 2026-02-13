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
        'uuid',
        'is_valid',
        'fecha_inicio',
        'validation_errors',
        'emisor_name',
        'receptor_name',
        'file_path',
        'pdf_filename',
        'pdf_path',
        'pdf_uploaded',
        'departamento',
        'mes',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'pdf_uploaded' => 'boolean',
        'validation_errors' => 'array'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function impuestos()
    {
        return $this->hasMany(Impuesto::class, 'xml_file_id');
    }



    public function batch()
    {
        return $this->belongsTo(XmlBatch::class, 'batch_id');
    }
}