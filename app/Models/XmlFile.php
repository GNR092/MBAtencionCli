<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'batch_id',
        'filename',
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
        'proyectos',
        'departamento',
        'id_user',
        'mes',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'pdf_uploaded' => 'boolean',
        'validation_errors' => 'array'
    ];

public function impuestos() {
    return $this->hasMany(Impuesto::class, 'xml_file_id');
}



    public function batch()
    {
        return $this->belongsTo(XmlBatch::class, 'batch_id');
    }
}