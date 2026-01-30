<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuentas extends Model
{
    use HasFactory;

    protected $table = 'cuentasporpagar';
    protected $fillable = [
        'id_cuentas_por_pagar',
        'estado',
        'saldo_neto',
        'monto_pagado',
        'saldo_pendiente',
        'id_contract',
        'created_at',
        'updated_at',
        'mesesdepago',
        'mesespagados',
        'mesespendientes',
        'xml_file_id',
        'isr',
        'tasaCuota',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function getImporteBaseFinalAttribute()
{
    $mes = json_decode($this->mesesdepago)->mes ?? null;

    if (!$mes) {
        return $this->importeBase; // devolver el que ya tiene si no hay mes
    }

    // Buscar incremento activo
    $incremento = \DB::table('incrementos_importe')
        ->where('id_contract', $this->id_contract)
        ->whereRaw("DATE_FORMAT(fecha_inicio, '%Y-%m') <= ?", [$mes])
        ->whereRaw("DATE_FORMAT(fecha_fin, '%Y-%m') >= ?", [$mes])
        ->value('importe_base');

    return $incremento ?? $this->importeBase;
}

}