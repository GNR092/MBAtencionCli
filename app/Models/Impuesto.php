<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuesto extends Model
{
    use HasFactory;

    // Nombre exacto de la tabla (ya que no sigue la convención plural)
    protected $table = 'impuesto';

    // Tu clave primaria real
    protected $primaryKey = 'impuesto_id';

    // Indica que la PK es autoincremental
    public $incrementing = true;

    // Tipo de clave primaria
    protected $keyType = 'int';

    // Permitir asignación masiva para estos campos
    protected $fillable = [
        'xml_file_id',     // 🔹 Relación con XML
        'tipoFactor',
        'regimenFiscal',
        'importeBase',
        'tasaCuota',
        'isr',
    ];

    // Relación con XML File
public function xmlFile() {
    return $this->belongsTo(XmlFile::class, 'xml_file_id');

}
}