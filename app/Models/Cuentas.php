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
        'uuid',
        'mes_pago',
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
        $mes = $this->mes_pago ?? null;

        if (! $mes) {
            return $this->importeBase;
        }

        $incremento = \DB::table('incrementos_importe')
            ->where('id_contract', $this->id_contract)
            ->whereRaw("DATE_FORMAT(fecha_inicio, '%Y-%m') <= ?", [$mes])
            ->where(function ($q) use ($mes) {
                $q->whereNull('fecha_fin')
                    ->orWhereRaw("DATE_FORMAT(fecha_fin, '%Y-%m') >= ?", [$mes]);
            })
            ->orderByDesc('fecha_inicio')
            ->value('importe_base');

        return $incremento ?? $this->importeBase;
    }
}
