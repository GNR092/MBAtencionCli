<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - MB Signature</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos Críticos (Cargan antes que nada) -->
    <style>
        html, body {
            background-color: #112134 !important; /* Royal Blue */
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden; /* Evita scroll durante carga */
        }
        /* Loader de pantalla completa */
        #initial-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #112134;
            z-index: 99999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease-out, visibility 0.5s;
        }
        /* Animación del spinner */
        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(216, 196, 149, 0.3); /* Color secundario tenue */
            border-top: 4px solid #d8c495; /* Color secundario fuerte */
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Contenido principal oculto inicialmente */
        #app-content {
            opacity: 0;
            transition: opacity 0.6s ease-out;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

</head>

<body class="font-sans text-gray-900 antialiased">

    <!-- 1. LOADER INICIAL -->
    <div id="initial-loader">
        <div class="loader-spinner"></div>
    </div>

    <!-- 2. CONTENIDO DE LA APLICACIÓN (Wrapper) -->
    <div id="app-content">
        <div class="flex min-h-screen overflow-hidden">
            @php $current = $current ?? ''; @endphp

            <!-- Sidebar -->
            <aside id="sidebar"
                   class="fixed top-0 left-0 h-screen w-64 bg-[#112134] text-white flex flex-col shadow-xl z-50 transition-all duration-300 overflow-y-auto scrollbar-hide">

                <!-- Logo y título -->
                <div onclick="toggleSidebarSize()"
                     class="flex items-center gap-3 px-6 py-6 border-b border-white/10 cursor-pointer">
                    <div class="bg-[#d8c495] text-[#112134] p-2 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                        </svg>
                    </div>
                    <div class="sidebar-text transition-all duration-300">
                        <h1 class="text-lg font-semibold">Dashboard</h1>
                        <p class="text-xs text-gray-400">MB Signature</p>
                    </div>
                </div>

                <!-- Secciones -->
                <nav class="flex flex-col px-4 mt-6 text-sm font-medium">
                    <p class="px-4 mb-2 mt-4 text-xs font-semibold tracking-wider text-gray-400 uppercase sidebar-text">Finanzas y Contabilidad</p>
                    <a href="/cuentas-por-pagar" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Cuentas por pagar</a>
                    <a href="/facturas" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Facturas</a>
                    <a href="/inpuestos" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Inpuestos</a>

                    <p class="px-4 mb-2 mt-6 text-xs font-semibold tracking-wider text-gray-400 uppercase sidebar-text">Gestión Empresarial y Legal</p>
                    <a href="/lista-de-inversionistas" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Lista de inversionistas</a>
                    <a href="/subir-archivo" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Administración de contratos</a>
                    <a href="/incrementos" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Incrementos de Importe</a>

                    <p class="px-4 mb-2 mt-6 text-xs font-semibold tracking-wider text-gray-400 uppercase sidebar-text">Operaciones y Atención al Cliente</p>
                    <a href="/registro_user" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Registro de Usuarios</a>
                    <a href="/admi_user" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300 {{ request()->is('admi_user') ? 'bg-[#d8c495]/20 text-[#d8c495]' : '' }}">Administrador de Usuarios</a>
                    <a href="/enviar-avisos" class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 hover:bg-[#d8c495]/20 hover:text-[#d8c495] text-gray-300">Avisos</a>
                </nav>

                <!-- Logout -->
                <div class="mt-auto border-t border-white/10">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="relative group flex items-center justify-center md:justify-start gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-500/10 transition w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/>
                            </svg>
                            <span class="sidebar-text">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main content -->
            <main class="flex-1 overflow-y-auto px-4 pt-6 pb-8 ml-0 md:ml-[17rem] transition-all">
                <div id="main-content">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts Generales -->
    <script>
        // CONTROL DE CARGA (FOUC)
        window.addEventListener('load', function() {
            const loader = document.getElementById('initial-loader');
            const content = document.getElementById('app-content');

            // Ocultar loader
            loader.style.opacity = '0';
            loader.style.visibility = 'hidden';

            // Mostrar contenido
            content.style.opacity = '1';

            // Habilitar scroll de nuevo
            document.body.style.overflow = 'auto';
            document.documentElement.style.overflow = 'auto';
        });

        function toggleSidebarSize() {
            const sidebar = document.getElementById('sidebar');
            const texts = sidebar.querySelectorAll('.sidebar-text');
            sidebar.classList.toggle('w-64');
            sidebar.classList.toggle('w-20');
            texts.forEach(el => el.classList.toggle('hidden'));
            const main = document.querySelector('main');
            main.classList.toggle('md:ml-[17rem]');
            main.classList.toggle('md:ml-[5rem]');
        }
    </script>
    @stack('scripts')
</body>
</html>
