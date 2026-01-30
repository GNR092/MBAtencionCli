<!DOCTYPE html>
<html >
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <meta name="csrf-token" content="{{ csrf_token() }}">

    </head>
    <body>
    <figure class="relative h-screen w-screen">
        <!-- Fondo con overlay -->
        
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/uploads/background.jpg')"></div>
        <div class="absolute inset-0 bg-black/40"></div>


    <!-- Contenido -->
        <div class="relative flex flex-col items-center justify-center h-full text-white">
            <img src="./uploads/Logo-Png.png" alt="logo" class="w-52 h-auto">

        <div class="bg-black p-6 rounded-lg">
            <h2 class="text-2xl font-bold mb-6">Iniciar Sesión</h2>
<form id="login-form" onsubmit="loginUsuario(event)">
    <div class="py-2">
        <label for="name" class="text-white block text-sm font-medium">Usuario</label>
        <input type="text" id="name" autocomplete="off" required class="text-white bg-gray-800 rounded-lg"/>
    </div>

    <div class="py-2">
        <label for="email" class="text-white block text-sm font-medium">Correo Electrónico</label>
        <input type="email" id="email" autocomplete="off" required class="text-white bg-gray-800 rounded-lg"/>
    </div>

    <div class="py-2">
        <label for="password" class="text-white block text-sm font-medium">Contraseña</label>
        <input type="password" id="password" autocomplete="new-password" required class="text-white bg-gray-800 rounded-lg"/>
    </div>

    <div class="py-2">
        <button type="submit" class="w-full py-2 px-4 text-black bg-white rounded-lg">
            Iniciar Sesión
        </button>
    </div>
    <div id="loginMensaje"></div>
</form>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

        </div>
    </figure>
    <script src="/js/confirmaUsuarios.js"></script>

    </body>
</html>
