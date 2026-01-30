<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error 405 - Método no permitido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            text-align: center;
            padding-top: 80px;
            color: #333;
        }
        .card {
            background: white;
            padding: 40px;
            width: 450px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #c0392b;
        }
        p {
            font-size: 16px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            background: #c9a143;
            padding: 10px 20px;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            background: #b18d39;
        }
    </style>
</head>
<body>

    <div class="card">
        <h1>🚫 Error 405</h1>
        <p>El método solicitado no está permitido para esta ruta.</p>
        <p>Es posible que se haya intentado acceder directamente a una acción restringida.</p>

        <a href="{{ url('/cuentas-por-pagar') }}">Volver al panel</a>
    </div>

</body>
</html>
