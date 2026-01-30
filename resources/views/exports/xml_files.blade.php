<table>
    <thead>
        <tr>
            <th>Factura</th>
            <th>UUID</th>
            <th>Fecha</th>
            <th>Proyecto</th>
            <th>Departamento</th>
            <th>Inversionista</th>
            <th>Tipo Factor</th>
            <th>Regimen Fiscal</th>
            <th>Base</th>
            <th>ISR Retenido</th>
        </tr>
    </thead>
    <tbody>
        @foreach($xmlFiles as $file)
            <tr>
                <td>{{ $file->id }}</td>
                <td>{{ $file->uuid }}</td>
                <td>{{ $file->created_at }}</td>
                <td>{{ $file->proyectos }}</td>
                <td>{{ $file->departamento }}</td>
                <td>{{ $file->emisor_name }}</td>
                <td>{{ $file->tipoFactor }}</td>
                <td>{{ $file->regimenFiscal }}</td>
                <td>{{ number_format($file->importeBase, 2) }}</td>
                <td>{{ number_format($file->isr, 2) }}</td>
            </tr>
        @endforeach

        <tr style="font-weight: bold; background-color: #f8f9fa;">
            <td colspan="4" align="right">TOTAL BASE:</td>
            <td>{{ number_format($totalBase, 2) }}</td>
            <td></td>
        </tr>
        <tr style="font-weight: bold; background-color: #f8f9fa;">
            <td colspan="5" align="right">TOTAL ISR RETENIDO:</td>
            <td>{{ number_format($totalISR, 2) }}</td>
        </tr>
    </tbody>
</table>

