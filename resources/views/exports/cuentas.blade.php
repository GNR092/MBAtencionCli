<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>INVERSIONISTA</th>
            <th>PROYECTO</th>
            <th>ESTADO</th>
            <th>MES</th>
            <th>IMPORTE BASE</th>
            <th>IMPORTE ISR</th>
            <th>SALDO NETO</th>
            <th>MONTO PAGADO</th>
            <th>SALDO PENDIENTE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cuentas as $cuenta)
            <tr>
                <td>{{$cuenta->id_cuentas_por_pagar}}</td>
                <td>{{ $cuenta->name }}</td>
                <td>{{$cuenta->proyecto}}</td>
                <td>{{ $cuenta->estado }}</td>
                <td>{{ json_decode($cuenta->mesesdepago)->mes ?? 'Sin mes' }}</td>
                <td>{{ number_format($cuenta->importe_base_final,2)}}</td>
                <td>{{ number_format($cuenta->isr,2) }}</td>
                <td>{{ number_format($cuenta->saldo_neto,2)}}</td>
                <td>{{ number_format($cuenta->monto_pagado,2)}}</td>
                <td>{{ number_format($cuenta->saldo_pendiente,2) }}</td>
            </tr>
        @endforeach

        <tr style="font-weight: bold; background-color: #f8f9fa;">
            <td colspan="4" align="right">TOTAL CUENTAS POR PAGAR:</td>
            <td>{{  number_format($totalPendiente, 2)}}</td>
        </tr>
        <tr style="font-weight: bold; background-color: #f8f9fa;">
            <td colspan="4" align="right">TOTAL CUENTAS PAGADAS:</td>
            <td>{{  number_format($totalPagado, 2)}}</td>
        </tr>
    </tbody>
</table>

