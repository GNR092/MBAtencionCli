<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
        <!-- LOGO -->
    <img src="/public/uploads/Logo-Png.png" 
         alt="Logo"
         style="width: 200px; height: auto;">

    <!-- TÍTULO A LA DERECHA -->
    <div style="text-align: right; font-size: 12px; line-height: 16px;">
        <div style="font-size: 18px; font-weight: bold;">Estado de Cuenta</div>
        <div style="font-size: 13px;">Libretón Básico Cuenta Digital</div>
        <div style="font-size: 11px;">PÁGINA 1 / 1</div>
    </div>

    </div>

    <style>
        
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { font-size: 14px; margin-bottom: 2px; }
        .user-info { margin-bottom: 20px; }
        .user-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 8px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        tbody tr:nth-child(even) { background-color: #fafafa; }
        .total-row td { font-weight: bold; background-color: #f9f9f9; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
<!-- CONTENEDOR PARA ALINEAR TODA LA TABLA A LA DERECHA -->
<div style="width: 100%; display: flex; justify-content: flex-end; margin-left: 40%;">
    <!-- TABLA DE INFORMACIÓN — ESTILO BBVA -->
    <table style="width: 65%; border-collapse: collapse;">

        <tbody>
            <tr>
                <td style="width:180px; background:#d9d9d9; padding:6px; border:1px solid #999;">RFC:</td>
                <td style="padding:6px; border:1px solid #999;">
                    MSP220504I99
                </td>
            </tr>

            <tr>
                <td style=" background:#d9d9d9; padding:6px; border:1px solid #999">Denominación/Razón Social:</td>
                <td style="padding:6px; border:1px solid #999;">MB SIGNATURE PROPERTIES</td>
            </tr>

            <tr>
                <td style=" background:#d9d9d9; padding:6px; border:1px solid #999">Fecha</td>
                <td style="padding:6px; border:1px solid #999;">{{ now()->format('d/m/Y') }}</td>
            </tr>

            <tr>
                <td style="background:#d9d9d9; padding:6px; border:1px solid #999;">Régimen</td>
                <td style="padding:6px; border:1px solid #999;">Régimen General de Ley Personas Morales</td>
            </tr>

            <tr>
                <td style="background:#d9d9d9; padding:6px; border:1px solid #999;">Domicilio</td>
                <td style="padding:6px; border:1px solid #999;">Plaza City 32 - Calle 32, Av. Andrés García Lavín Ext. 298, Temozon Norte, Merida, Yucatán, Mexico</td>
            </tr>
        </tbody>
    </table>

</div>


<div class="user-info">
    <p><strong>Inversionista:</strong> {{ $usuario->name ?? $usuario->nombre ?? '—' }}</p>
    <p><strong>Email:</strong> {{ $usuario->email ?? '—' }}</p>
</div>
@php
    $totalCargos = 0;      // Deuda total (pendiente)
    $totalAbonos = 0;      // Total pagado

    foreach ($cuentas as $c) {

        // CARGOS = saldo pendiente (deuda acumulada todos los años)
        if (!is_null($c->saldo_pendiente)) {
            $totalCargos += (float) $c->saldo_pendiente;
        }

        // ABONOS = monto pagado (todos los años)
        if (!is_null($c->monto_pagado)) {
            $totalAbonos += (float) $c->monto_pagado;
        }
    }

    // SALDO FINAL = lo que falta por pagar en total
    $saldoFinal = $totalCargos - $totalAbonos;
@endphp


<table>
    <thead>
        <tr>
            <th>ID Cuenta</th>
            <th>Proyecto</th>
            <th>Estado</th>
            <th>Saldo neto</th>
            <th>Mes de Pago</th>
            <th>Fecha de Registro</th>
        </tr>
    </thead>
    <tbody>
        @forelse($cuentas->where('estado', 'pagado') as $c)
        <tr>
            <td>{{ $c->id_cuentas_por_pagar ?? $c->id }}</td>
            <td>{{ $c->proyecto ?? $c->proyectos ?? '—' }}</td>
            <td>{{ $c->estado }}</td>
            <td>${{ number_format($c->saldo_neto ?? $c->saldo_neto ?? 0, 2) }}</td>
            <td>{{ optional(json_decode($c->mesesdepago))->mes ?? '—' }}</td>
            <td>{{ optional($c->created_at)->format('d/m/Y') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center;">No hay cuentas para este usuario.</td>
        </tr>
        @endforelse

        <!-- FILA DE TOTALES -->
        <tr class="total-row">
            <td colspan="3">Importe total de la renta</td>
            <td>${{ number_format($totalCargos, 2) }}</td>
            <td colspan="2"></td>
        </tr>
        <tr class="total-row">
            <td colspan="3">Pagos Acumulados</td>
            <td>${{ number_format($totalAbonos, 2) }}</td>
            <td colspan="2"></td>
        </tr>
        <tr class="total-row">
            <td colspan="3">Saldo Pendiente por cobrar</td>
            <td>${{ number_format($saldoFinal, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

<div class="footer">
    Documento generado automáticamente — MB Signature Properties  — Todos los derechos reservados
</div>

</body>
</html>
