<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preload" href="/fonts/DoulosSILR.ttf" as="font" type="font/ttf" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <style>
        /* Definimos la fuente localmente para evitar problemas de caché de Vite */
        @font-face {
            font-family: 'Dancing Script'; /* Mantenemos el nombre que usas en tu lógica */
            src: url('/fonts/DoulosSILR.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        /* Clase forzada para asegurar que se aplique */
        .fuente-personalizada {
            font-family: 'Dancing Script', serif !important;
            /* Cambié 'cursive' por 'serif' porque Doulos es serif.
               Si falla, se verá Times New Roman en lugar de Comic Sans */
        }

        /* Animaciones (por si el CSS externo falla) */
        @keyframes zoom-out-logo {
            0% { transform: scale(2.5) translateY(40%); filter: blur(4px); }
            100% { transform: scale(1) translateY(0); filter: blur(0); }
        }
        .animate-logo-entrance {
            animation: zoom-out-logo 10s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slide-up-form {
            0% { opacity: 0; transform: translateY(100px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-form-entrance {
            animation: slide-up-form 5s cubic-bezier(0.16, 1, 0.3, 1) 0.4s backwards;
        }
    </style>
</head>
<body>
<figure class="relative h-screen w-screen overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center bg-[#3c3c3c]"></div>
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative flex flex-col items-center justify-center h-full text-white">

        <img src="/uploads/Logo-Png.svg" alt="logo" class="w-52 h-auto animate-logo-entrance z-10">

        <div class="flex justify-center w-full px-4 text-center"
             x-data="{
        linea1: 'Bienvenido a la comunidad de inversionistas'.split(' '),
        linea2: 'que vive de sus rentas garantizadas.'.split(' '),
        show: false,
        init() {
            setTimeout(() => { this.show = true }, 1000);
        }
     }">

            <h1 class="fuente-personalizada text-3xl md:text-5xl text-dorado-400 leading-tight drop-shadow-lg text-center">
        <span class="block mb-2">
            <template x-for="(word, index) in linea1">
                <span
                    x-html="word + '&nbsp;'"
                    class="inline-block transition-all duration-700"
                    :style="`transition-delay: ${index * 150}ms; transform: ${show ? 'translateY(0)' : 'translateY(20px)'}; opacity: ${show ? 1 : 0};`"
                ></span>
            </template>
        </span>

                <span class="block">
            <template x-for="(word, index) in linea2">
                <span
                    x-html="word + '&nbsp;'"
                    class="inline-block transition-all duration-700"
                    :style="`transition-delay: ${(index + linea1.length) * 150}ms; transform: ${show ? 'translateY(0)' : 'translateY(20px)'}; opacity: ${show ? 1 : 0};`"
                ></span>
            </template>
        </span>
            </h1>
        </div>

        <div class="p-6 rounded-lg animate-form-entrance w-full max-w-sm mt-8">
            <h2 class="text-2xl font-bold mb-6 text-center uppercase tracking-widest">Iniciar Sesión</h2>
            <form id="login-form" onsubmit="loginUsuario(event)">
                <div class="py-2">
                    <label for="email" class="text-[#d8c495] block text-xs font-bold uppercase mb-1">Correo Electrónico</label>
                    <input type="email" id="email" autocomplete="email" required
                           class="w-full text-white bg-white/10 rounded-lg border-transparent focus:border-[#d8c495] focus:ring-[#d8c495] transition-all placeholder-gray-400"/>
                </div>

                <div class="py-2">
                    <label for="password" class="text-[#d8c495] block text-xs font-bold uppercase mb-1">Contraseña</label>
                    <input type="password" id="password" autocomplete="new-password" required
                           class="w-full text-white bg-white/10 rounded-lg border-transparent focus:border-[#d8c495] focus:ring-[#d8c495] transition-all placeholder-gray-400"/>
                </div>

                <button type="submit"
                        class="group py-3 mt-6 w-full px-4 text-[#3c3c3c] bg-white rounded-lg font-bold hover:bg-[#d8c495] transition-all duration-300 shadow-lg">
                    <span class="block group-hover:hidden">
                        INGRESAR
                    </span>

                    <span class="hidden group-hover:block">
                       ¡QUIERO MIS RENTAS!
                     </span>
                </button>

                <div id="loginMensaje" class="mt-4 text-center"></div>
            </form>

            @if(session('error'))
                <div class="mt-4 p-3 bg-red-900/50 border border-red-500 text-red-100 rounded text-sm text-center">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</figure>

<script src="{{ asset('js/confirmaUsuarios.js') }}"></script>

</body>
</html>
