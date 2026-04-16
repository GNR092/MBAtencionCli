<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MB Signature Properties')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* --- 1. FONDO DE MÁRMOL (SVG) --- */
        #bg-container {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -5; /* Muy al fondo */

            /* AQUÍ ESTÁ EL CAMBIO: Usamos el archivo .svg */
            /*background-image: url("{{ asset('images/marmol2.svg') }}"); */
            background-color: #0d1f30;
            background-image:
                radial-gradient(circle at 12% 18%, rgba(216, 196, 149, 0.09), transparent 28%),
                radial-gradient(circle at 82% 82%, rgba(216, 196, 149, 0.06), transparent 30%),
                linear-gradient(160deg, rgba(8, 18, 28, 0.95), rgba(13, 31, 48, 0.96));

            /* 'cover' asegura que el SVG cubra toda la pantalla sin deformarse */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* --- 2. CORTINA NEGRA --- */
        #black-curtain {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background-color: #000;
            z-index: 40;
            transition: opacity 1s ease-out, visibility 1s;
        }

        /* --- 3. LOGO (PROTAGONISTA) --- */
        #logo-container {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            pointer-events: none;
        }

        #intro-logo {
            width: 300px;
            opacity: 0; /* Empieza invisible (fadeIn lo muestra) */

            /* ESTADO INICIAL: Gigante */
            transform: scale(3.5);

            /* Animación de entrada (aparecer) */
            animation: fadeInLogo 0.5s forwards ease-out;

            /* SOMBRA */
            filter: drop-shadow(0 0 20px rgba(0,0,0,0.5));

            /* TRANSICIÓN: Preparamos al logo para que anime su caída suavemente */
            transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.8s ease;
        }

        /* --- CLASE DE CAÍDA (SMASH) --- */
        .logo-smash {
            /* Cae a tamaño 0.7 */
            transform: scale(0.7) !important;

            /* Se vuelve translúcido (Marca de agua) */
            opacity: 0.3 !important;
        }

        /* Cuando termina todo, mandamos el contenedor al fondo */
        .logo-background-mode {
            z-index: -2 !important;
        }

        /* --- 4. CONTENIDO --- */
        #app-layout {
            position: relative;
            z-index: 10;
            background: transparent;
        }

        @keyframes fadeInLogo {
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            #intro-logo { width: 180px; }
        }
    </style>
</head>
<body>

<div id="logo-container">
    <img id="intro-logo" src="{{ asset('images/MB_SP.svg') }}" alt="MB Signature Logo">
</div>

<div id="black-curtain"></div>

<div id="bg-container">
    <div style="width: 100%; height: 100%; background-color: rgba(0,0,0,0.3);"></div>
</div>

<div id="app-layout">
    @yield('layout-content')
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoContainer = document.getElementById('logo-container');
        const logo = document.getElementById('intro-logo');
        const curtain = document.getElementById('black-curtain');

        if (logo && curtain) {

            setTimeout(() => {


                logo.classList.add('logo-smash');


                setTimeout(() => {
                    curtain.style.opacity = '0';
                    curtain.style.visibility = 'hidden';


                    logoContainer.classList.add('logo-background-mode');


                    setTimeout(() => {
                        curtain.remove();
                    }, 1000);

                }, 500);

            }, 1000);
        }
    });
</script>

@stack('scripts')
</body>
</html>
