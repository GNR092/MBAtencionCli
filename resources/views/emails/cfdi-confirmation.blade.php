<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Archivos CFDI aprobados</title>
</head>
<body>
    <h3>Estimado inversionista y/o representante:</h3>
    <p>Confirmo la recepción de su CFDI, en los próximos días se verificará el cumplimiento de los lineamientos de nuestras políticas de facturación, en caso de encontrarse en orden se aprobará en el sistema de pagos (Payana) y se programará su pago el día 15 de este mes (o si éste fuese inhábil al día hábil siguiente).</p>
    <p>Saludos cordiales!!</p>
    
    <ul>
        @foreach($batch->validXmlFiles as $xmlFile)
            <li>XML: <b>{{ $xmlFile->filename }}</b> - PDF: <b>{{ $xmlFile->pdf_filename }}</b></li>
        @endforeach
    </ul>
    
    <br>
    <p style="color:gray;">Este es un correo automático, por favor no respondas.</p>
</body>
</html>