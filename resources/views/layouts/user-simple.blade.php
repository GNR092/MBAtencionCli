<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MB Signature - Simple</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Mantenemos tu configuración de scroll perfecta */
        html, body {
            height: 100%;
            overflow: hidden; /* Evita doble scrollbar en la ventana principal */
            background-color: #3c3c3c;
        }

        #content-overlay {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow-y: auto; /* El scroll ocurre aquí */
            padding: 2rem;
            /* Z-index superior para asegurar que el texto pase POR ENCIMA del logo */
            z-index: 10;
        }

        .inner-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Clase para centrar el logo marca de agua */
        .watermark-logo {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7); /* Centrado perfecto y escala pequeña */
            width: 300px; /* Tamaño base igual que en la animación */
            opacity: 0.3; /* Opacidad baja */
            pointer-events: none; /* Vital: permite dar click al contenido a través del logo */
            z-index: 0; /* Se queda detrás del contenido */
        }
    </style>
</head>
<body
    class="bg-cover bg-center bg-no-repeat bg-fixed text-white antialiased"
>

<img src="{{ asset('images/MB_SP.svg') }}" alt="Marca de Agua" class="watermark-logo">

<div class="relative z-10 bg-black/50 w-full min-h-screen">

    <div id="content-overlay">
        <div class="inner-wrapper">
            @if(session('error'))
                <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-4 mx-4 mt-4">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-4 mx-4 mt-4">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-4 mx-4 mt-4">
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>

</div>

</body>
</html>
