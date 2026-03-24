<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>RAZON SOCIAL</th>
            <th>TIPO</th>
            <th>DEPARTAMENTO</th>
            <th>PROYECTO</th>
            <th>PERSONA</th>
            <th>FORMA DE PAGO</th>
            <th>NOMBRE INVERSIONISTA</th>
            <th>IMPORTE RENTA</th>
            <th>RETENCIÓN ISR</th>
            <th>RETENCIÓN IVA</th>
            <th>IMPORTE NETO</th>
            <th>IMPORTE PAGADA</th>
            <th>MES</th>
            <th>PAGADO / NO PAGADO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cuentas as $cuenta)
            <tr>
                <td>{{ $cuenta->id_cuentas_por_pagar }}</td>
                <td>{{ $cuenta->razon_social }}</td>
                <td>{{ $cuenta->contract_tipo }}</td>
                <td>{{ $cuenta->departamento ?? 'N/A' }}</td>
                <td>{{ $cuenta->proyecto }}</td>
                <td>{{ $cuenta->name }}</td>
                <td>{{ $cuenta->metodo_pago ?? 'N/A' }}</td>
                <td>{{ $cuenta->name }}</td>
                <td>{{ number_format($cuenta->importeBase, 2) }}</td>
                <td>{{ number_format($cuenta->isr ?? 0, 2) }}</td>
                <td>{{ number_format($cuenta->retencion_iva ?? 0, 2) }}</td>
                <td>{{ number_format($cuenta->saldo_neto, 2) }}</td>
                <td>{{ number_format($cuenta->monto_pagado, 2) }}</td>
                <td>{{ $cuenta->mes_pago ?? 'Sin mes' }}</td>
                <td>{{ strtoupper($cuenta->estado) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
