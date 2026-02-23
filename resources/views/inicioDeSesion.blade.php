<!DOCTYPE html>
<html>
<head>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preload" as="image" href="/uploads/background.jpg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
<figure class="relative h-screen w-screen overflow-hidden">
    <!-- <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/uploads/background.jpg')"></div> -->
    <div class="absolute inset-0 bg-cover bg-center bg-[#3c3c3c]"></div>
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative flex flex-col items-center justify-center h-full text-white">

        <img src="./uploads/Logo-Png.svg" alt="logo" class="w-52 h-auto animate-logo-entrance z-10">

        <div class="flex justify-center w-full"
             x-data="{
        text: '',
        fullText: 'Bienvenido a la comunidad de inversionistas sin dolores de cabeza.',
        speed: 100,
        startDelay: 2000, // Tiempo de espera en milisegundos (2s)
        init() {
            this.text = '';

            // Usamos setTimeout para retrasar el inicio del setInterval
            setTimeout(() => {
                let i = 0;
                let interval = setInterval(() => {
                    if (i < this.fullText.length) {
                        this.text += this.fullText.charAt(i);
                        i++;
                    } else {
                        clearInterval(interval);
                    }
                }, this.speed);
            }, this.startDelay);
        }
     }">

            <h1 x-text="text"
                style="font-family: 'Dancing Script', cursive; min-height: 1.5em;"
                class="text-4xl text-dorado-400">
            </h1>
        </div>

        <div class=" p-6 rounded-lg animate-form-entrance ">
            <h2 class="text-2xl font-bold mb-6 text-center">Iniciar Sesión</h2>
            <form id="login-form" onsubmit="loginUsuario(event)">
                <div class="py-2">
                    <label for="email" class="text-white block text-sm font-medium">Correo Electrónico</label>
                    <input type="email" id="email" autocomplete="email" required
                           class="w-full text-white bg-gray-800 rounded-lg border-transparent focus:border-white focus:ring-0 transition-all"/>
                </div>

                <div class="py-2">
                    <label for="password" class="text-white block text-sm font-medium">Contraseña</label>
                    <input type="password" id="password" autocomplete="new-password" required
                           class="w-full text-white bg-gray-800 rounded-lg border-transparent focus:border-white focus:ring-0 transition-all"/>
                </div>

                <button type="submit"
                        class="group py-2 mt-4 w-full py-2 px-4 text-black bg-white rounded-lg font-semibold hover:bg-[#FFBF00] transition-colors ">
                    <span class="block group-hover:hidden">
                        Iniciar Sesión
                    </span>

                    <span class="hidden group-hover:block">
                       ¡Quiero Mis Rentas!
                     </span>
                </button>

                <div id="loginMensaje"></div>
            </form>

            @if(session('error'))
                <div class="mt-4 p-2 bg-red-500/20 border border-red-500 text-red-100 rounded text-sm text-center">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</figure>
<script src="/js/confirmaUsuarios.js"></script>

</body>
</html>
