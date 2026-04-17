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

        html, body {
            min-height: 100svh;
            margin: 0;
            background: #0d1f30;
        }

        /* Clase forzada para asegurar que se aplique */
        .fuente-personalizada {
            font-family: 'Dancing Script', serif;
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

        .login-hero-title {
            font-size: clamp(1.7rem, 5vw, 3rem);
            line-height: 1.2;
            letter-spacing: 0;
            text-transform: none;
        }

        @media (min-width: 768px) {
            .login-hero-title {
                font-size: clamp(2.3rem, 4.2vw, 3.6rem);
            }
        }

        @media (max-height: 520px) and (orientation: landscape) {
            .login-shell {
                justify-content: flex-start;
                padding-top: 0.9rem;
                padding-bottom: 0.9rem;
            }

            .login-logo {
                width: 6.8rem;
            }

            .login-hero-copy {
                margin-top: 0.3rem;
            }

            .login-hero-title {
                font-size: 1.3rem;
                line-height: 1.1;
            }

            .login-card {
                margin-top: 0.8rem;
                padding-top: 0.9rem;
                padding-bottom: 0.9rem;
            }
        }

        .form-hint {
            margin-top: 0.3rem;
            display: block;
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.55);
        }

        .field-error {
            margin-top: 0.3rem;
            display: none;
            font-size: 0.72rem;
            color: rgb(252, 165, 165);
        }

        .field-error[data-visible='true'] {
            display: block;
        }

        .input-error {
            border-color: rgba(248, 113, 113, 0.85) !important;
            box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.15);
        }

        .btn-loading {
            cursor: wait;
            opacity: 0.88;
        }

        .password-toggle {
            position: absolute;
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            border: 1px solid rgba(216, 196, 149, 0.25);
            background: rgba(216, 196, 149, 0.08);
            color: rgba(255, 255, 255, 0.82);
            border-radius: 0.6rem;
            padding: 0.2rem 0.55rem;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .password-toggle:hover {
            background: rgba(216, 196, 149, 0.18);
            color: white;
        }

        .password-toggle:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px rgba(216, 196, 149, 0.35);
        }
    </style>
</head>
<body class="bg-[#0d1f30]">
<figure class="relative h-[100svh] min-h-[100svh] w-screen overflow-y-auto overflow-x-hidden">
    <div class="absolute inset-0 bg-cover bg-center bg-[#0d1f30]"></div>
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="login-shell relative flex flex-col items-center justify-center h-full text-white">

        <img src="/uploads/Logo-Png.svg" alt="Logo de MB Capital" class="login-logo w-40 md:w-52 h-auto animate-logo-entrance z-10">

        <div class="login-hero-copy flex justify-center w-full px-4 text-center"
             x-data="{
        linea1: 'Bienvenido a la comunidad de inversionistas'.split(' '),
        linea2: 'que vive de sus rentas garantizadas.'.split(' '),
        show: false,
        init() {
            setTimeout(() => { this.show = true }, 1000);
        }
     }">

            <h1 class="login-hero-title fuente-personalizada text-dorado-400 drop-shadow-lg text-center">
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

        <div class="login-card p-5 md:p-6 rounded-2xl animate-form-entrance w-full max-w-sm mt-6 md:mt-8 bg-[#0d1f30]/80 backdrop-blur-md border border-[#d8c495]/20 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-center uppercase tracking-widest text-white">Iniciar sesion</h2>
            <form id="login-form" action="{{ route('login') }}" method="POST" autocomplete="on" aria-label="Formulario de inicio de sesion" onsubmit="loginUsuario(event)">
                @csrf
                <div class="py-2">
                    <label for="email" class="text-[#d8c495] block text-xs font-bold uppercase mb-1">Correo Electronico</label>
                    <input type="email" id="email" name="email" inputmode="email" autocapitalize="none" spellcheck="false" autocomplete="email" aria-describedby="email-hint email-error" aria-required="true" aria-invalid="false" required autofocus
                           class="w-full text-white bg-white/5 rounded-xl border border-white/10 px-4 py-3 focus:border-[#d8c495] focus:ring-2 focus:ring-[#d8c495] focus:outline-none transition-all placeholder-gray-400"/>
                    <span id="email-hint" class="form-hint">Ingresa tu correo registrado.</span>
                    <span id="email-error" class="field-error" data-visible="false"></span>
                </div>

                <div class="py-2">
                    <label for="password" class="text-[#d8c495] block text-xs font-bold uppercase mb-1">Contrasena</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" autocomplete="current-password" aria-describedby="password-hint password-error" aria-required="true" aria-invalid="false" required
                               class="w-full text-white bg-white/5 rounded-xl border border-white/10 px-4 py-3 pr-20 focus:border-[#d8c495] focus:ring-2 focus:ring-[#d8c495] focus:outline-none transition-all placeholder-gray-400"/>
                        <button type="button" id="togglePassword" class="password-toggle" aria-label="Mostrar contrasena" aria-controls="password" aria-pressed="false">
                            Mostrar
                        </button>
                    </div>
                    <span id="password-hint" class="form-hint">Usa la contrasena de tu cuenta.</span>
                    <span id="password-error" class="field-error" data-visible="false"></span>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3 text-xs text-white/75">
                    <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border border-white/30 bg-white/10 text-[#d8c495] focus:ring-[#d8c495]">
                        <span>Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[#d8c495] hover:text-white transition-colors">Olvide mi contrasena</a>
                    @else
                        <span class="text-white/45">Contacta al administrador si olvidaste tu contrasena.</span>
                    @endif
                </div>

                <button id="submitLoginButton" type="submit"
                        class="group py-3 mt-6 w-full px-4 text-[#0d1f30] bg-[#d8c495] rounded-xl font-bold hover:bg-[#c9a143] transition-all duration-300 shadow-lg">
                    <span class="block group-hover:hidden">
                        INGRESAR
                    </span>

                    <span class="hidden group-hover:block">
                       QUIERO MIS RENTAS!
                     </span>
                </button>

                <div id="loginMensaje" class="mt-4 text-center" role="status" aria-live="polite" aria-atomic="true"></div>
            </form>

            @if(session('error'))
                <div class="mt-4 p-3 bg-red-900/50 border border-red-500 text-red-100 rounded text-sm text-center" role="alert" aria-live="assertive">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</figure>

<script src="{{ asset('js/confirmaUsuarios.js') }}"></script>

</body>
</html>
