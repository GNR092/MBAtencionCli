<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetroactivoEliminado extends Model
{
    protected $table = 'retroactivos_eliminados';

    protected $fillable = [
        'cuenta_original_id',
        'id_contract',
        'xml_file_id',
        'uuid',
        'mes_pago',
        'es_retroactivo',
        'estado',
        'saldo_neto',
        'monto_pagado',
        'saldo_pendiente',
        'isr',
        'tasaCuota',
        'mesesdepago',
        'mesespagados',
        'mesespendientes',
        'eliminado_por',
        'motivo',
    ];

    protected $casts = [
        'es_retroactivo' => 'boolean',
        'saldo_neto' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'isr' => 'decimal:2',
        'tasaCuota' => 'decimal:6',
        'mesesdepago' => 'array',
        'mesespagados' => 'array',
        'mesespendientes' => 'array',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'id_contract');
    }

    public function xmlFile()
    {
        return $this->belongsTo(XmlFile::class, 'xml_file_id');
    }
}
