<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>403 - No autorizado</title>
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
            color: #e74c3c;
        }
        p { font-size: 16px; }
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
        a:hover { background: #b18d39; }
    </style>
</head>
<body>

    <div class="card">
        <h1>🔒 Acceso no permitido</h1>
        <p>No tienes permisos para acceder a esta sección o ejecutar esta acción.</p>
        <a href="{{ url('/') }}">Volver al inicio</a>
    </div>

</body>
</html>
