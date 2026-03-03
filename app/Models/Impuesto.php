<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuesto extends Model
{
    use HasFactory;

    
    protected $table = 'impuesto';

    
    protected $primaryKey = 'impuesto_id';

    
    public $incrementing = true;

    
    protected $keyType = 'int';

    
    protected $fillable = [
        'xml_file_id',     
        'tipoFactor',
        'regimenFiscal',
        'importeBase',
        'tasaCuota',
        'tasaRetencion',
        'isr',
    ];

    
public function xmlFile() {
    return $this->belongsTo(XmlFile::class, 'xml_file_id');

}
}