<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MB Signature Properties</title>

    {{-- Assets y Fuentes --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <style>
    /* --- 1. FONDO DE MÁRMOL ESTÁTICO --- */
    #bg-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -10;
        /* Al fondo absoluto */
        /* background-image: url("{{ asset('images/marmol2.svg') }}"); */
        background-color: #3c3c3c;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    /* --- 2. LOGO MARCA DE AGUA (ESTÁTICO) --- */
    .watermark-logo {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.7);
        /* Escala pequeña (igual al final de la animación) */
        width: 300px;
        opacity: 0.3;
        /* Opacidad baja */
        pointer-events: none;
        /* No bloquea clicks */
        z-index: -5;
        /* Detrás del contenido, delante del fondo */
    }

    /* --- 3. TUS ESTILOS ORIGINALES (Glassmorphism & Sidebar) --- */
    #main-content {
        background-color: transparent !important;
        margin-left: 0 !important;
        padding: 0 !important;
    }

    /* Sidebar con efecto Glassmorphism */
    :root {
        --admin-header-height: 64px;
    }

    #sidebar {
        position: fixed;
        top: var(--admin-header-height);
        left: -100%;
        height: calc(100vh - var(--admin-header-height));
        width: 280px;
        z-index: 50;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: rgba(11, 22, 36, 0.92) !important;
        backdrop-filter: blur(10px);
        border-right: 1px solid rgba(216, 196, 149, 0.2);
    }

    #sidebar.sidebar-active {
        left: 0;
    }

    #sidebar-overlay {
        display: none;
        position: fixed;
        top: var(--admin-header-height);
        right: 0;
        bottom: 0;
        left: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(2px);
        z-index: 40;
    }

    #sidebar-overlay.active {
        display: block;
    }

    @media (min-width: 1024px) {
        #sidebar {
            position: relative;
            top: 0;
            left: 0;
            height: 100%;
            width: 260px;
            z-index: 20;
            background-color: rgba(17, 33, 52, 0.90) !important;
        }

        #sidebar.sidebar-collapsed {
            margin-left: -260px;
        }
    }

    /* Links activos del sidebar */
    .sidebar-link.active {
        background: rgba(255, 255, 255, 0.1);
        color: #d8c495 !important;
        border-left: 2px solid #d8c495;
    }

    /* Tablas */
    .table-container {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px;
        background: rgba(17, 33, 52, 0.6);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(216, 196, 149, 0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    th {
        background-color: rgba(11, 22, 36, 1);
        color: #d8c495;
        padding: 14px;
        text-align: left;
        font-size: 0.85rem;
        text-transform: uppercase;
        border-bottom: 2px solid #d8c495;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid rgba(216, 196, 149, 0.1);
        font-size: 0.9rem;
        color: #fff;
    }

    /* Componentes */
    .dropdown .accordion-content {
        display: none;
    }

    .dropdown.active .accordion-content {
        display: block;
    }

    .arrow {
        transition: transform 0.3s ease;
        display: inline-block;
        margin-left: 5px;
    }

    .dropdown.active .arrow {
        transform: rotate(180deg);
    }

    .fade-in-content {
        animation: fadeIn 0.6s ease-out forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
</head>

<body class="antialiased text-white h-full overflow-hidden">

    <div id="bg-container">
        <div style="width: 100%; height: 100%; background-color: rgba(0,0,0,0.3);"></div>
    </div>

    <img src="{{ asset('images/MB_SP.svg') }}" alt="Marca de Agua" class="watermark-logo">

    <div id="sidebar-overlay"></div>

    <div class="h-screen flex flex-col relative">
        <header
            class="sticky top-0 z-30 flex items-center justify-between px-4 py-3 text-white shadow-lg border-b border-[#d8c495]/30 bg-[#112134]/80 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" class="p-1 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1
                    class="text-xs md:text-sm font-bold uppercase tracking-wider text-[#d8c495] truncate max-w-30 md:max-w-none">
                    {{ currentUser()->name }}
                </h1>

                <div class="relative ml-2" id="notification-bell-container">
                    <button class="relative p-1 hover:text-[#d8c495] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span id="notification-badge"
                            class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white hidden">0</span>
                    </button>
                    <div id="notification-dropdown"
                        class="absolute right-0 z-[80] mt-3 hidden w-80 max-w-[calc(100vw-1rem)] rounded-xl bg-white shadow-2xl text-gray-800 border border-gray-200 overflow-hidden">
                        <div id="notification-list" class="max-h-72 overflow-y-auto"></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                @php
                    $mesActual = request('month', date('Y-m'));
                    $anoDisplay = substr($mesActual, 0, 4);
                @endphp
                <x-mes-selector 
                    :mes-actual="$mesActual" 
                    :ano-inicial="$anoDisplay"
                />
                <a class="shrink-0">
                    <img src="/uploads/Logo-Png.svg" alt="Logo" class="h-7 md:h-9">
                </a>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <aside id="sidebar">
                <div id="sidebar-content" class="flex flex-col h-full p-4 overflow-y-auto custom-scrollbar">
                    <nav class="space-y-2">
                        <div class="dropdown">
                            <button type="button"
                                class="dropdown-toggle flex w-full items-center justify-between p-3 rounded-lg hover:bg-white/10 transition-all text-sm font-medium text-white">
                                <span>Finanzas y Contabilidad</span>
                                <span class="arrow text-[10px]">&#9660;</span>
                            </button>
                            <div class="accordion-content hidden space-y-1 mt-1 bg-black/20 rounded-lg">
                                <a href="{{ route('tablas-control.index') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Tablas
                                    de control</a>
                                <a href="/cuentas-por-pagar"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Cuentas
                                    por pagar</a>
                                <a href="/retroactivo"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Retroactivo</a>
                                <a href="/facturas"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Facturas</a>
                                <a href="/impuestos"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Impuestos</a>
                                <a href="/efectivo"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Efectivo</a>
                            </div>
                        </div>

                        {{-- Sección: Gestión Empresarial y Legal --}}
                        <div class="dropdown">
                            <button type="button"
                                class="dropdown-toggle flex w-full items-center justify-between p-3 rounded-lg hover:bg-white/10 transition-all text-sm font-medium text-white">
                                <span>Gestión Empresarial y Legal</span>
                                <span class="arrow text-[10px]">&#9660;</span>
                            </button>
                            <div class="accordion-content hidden space-y-1 mt-1 bg-black/20 rounded-lg">
                                <a href="/lista-de-inversionistas"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Lista
                                    de inversionistas</a>
                                <a href="/subir-archivo"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Administración
                                    de contratos</a>
                                <a href="/incrementos"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Incrementos
                                    de Importe</a>

                                {{-- NUEVOS BOTONES (Ajustados al diseño exacto de la captura) --}}
                                <a href="{{ route('regimen-fiscal.index') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Gestión
                                    de régimen fiscal</a>
                                <a href="{{ route('proyectos.index') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Altas
                                    de proyectos</a>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button type="button"
                                class="dropdown-toggle flex w-full items-center justify-between p-3 rounded-lg hover:bg-white/10 transition-all text-sm font-medium text-white">
                                <span>Operaciones y Atención al Cliente</span>
                                <span class="arrow text-[10px]">&#9660;</span>
                            </button>
                            <div class="accordion-content hidden space-y-1 mt-1 bg-black/20 rounded-lg">
                                <a href="/registro_user"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Registro
                                    de Usuarios</a>
                                <a href="/admi_user"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Administrador
                                    de Usuarios</a>
                                <a href="/enviar-avisos"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Avisos</a>
                                <a href="{{ route('admin.users.chat-directory') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Directorio
                                    (Chat)</a>
                                <a href="{{ route('admin.logos.index') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Gestión
                                    Logos</a>
                                <a href="{{ route('admin.anuncios.index') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Gestión
                                    Anuncios</a>
                                <a href="{{ route('usuarios.cumpleanios') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Cumpleaños</a>
                                <a href="{{ route('razones-sociales.index') }}"
                                    class="sidebar-link block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Razón Social</a>
                            </div>
                        </div>
                    </nav>

                    <a href="{{ route('logout') }}"
                        class="flex items-center justify-center w-full gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition-all hover:bg-red-700 active:scale-95 shadow-lg shadow-red-900/40">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        CERRAR SESIÓN
                    </a>

                </div>
            </aside>

            <main id="main-content" class="flex-1 overflow-y-auto custom-scrollbar">
                <div class="fade-in-content page-wrap">
                    {{-- Mensajes Flash --}}
                    @if(session('error'))
                        <div class="alert-flash alert-flash-error">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert-flash alert-flash-error">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert-flash alert-flash-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- SCRIPTS (Fuera del stack para que carguen siempre) --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mainContent = document.getElementById('main-content');
        const header = document.querySelector('header');

        const syncHeaderHeight = () => {
            if (!header) {
                return;
            }

            const height = Math.max(header.offsetHeight, 56);
            document.documentElement.style.setProperty('--admin-header-height', `${height}px`);
        };

        syncHeaderHeight();
        window.addEventListener('resize', syncHeaderHeight);

        // Restaurar estado del sidebar desde localStorage
        const savedState = localStorage.getItem('sidebar_state');
        if (savedState === 'collapsed' && window.innerWidth >= 1024) {
            sidebar.classList.add('sidebar-collapsed');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.toggle('sidebar-active');
                    sidebarOverlay.classList.toggle('active');
                } else {
                    sidebar.classList.toggle('sidebar-collapsed');
                    // Guardar estado en localStorage
                    const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
                    localStorage.setItem('sidebar_state', isCollapsed ? 'collapsed' : 'expanded');
                }
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-active');
                sidebarOverlay.classList.remove('active');
            });
        }

        // Resaltar opción activa del menú
        const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
        const sidebarLinks = document.querySelectorAll('.sidebar-link');

        const toPath = (href) => {
            try {
                const parsed = new URL(href, window.location.origin);
                return parsed.pathname.replace(/\/$/, '') || '/';
            } catch (_) {
                return '/';
            }
        };

        const isPathMatch = (current, target) => {
            if (!target || target === '#') return false;
            if (current === target) return true;
            return target !== '/' && current.startsWith(target + '/');
        };
        
        sidebarLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                const linkPath = toPath(href);
                const isMatch = isPathMatch(currentPath, linkPath);
                
                if (isMatch) {
                    link.classList.add('active');
                    // Abrir el dropdown padre si está cerrado
                    const dropdownParent = link.closest('.dropdown');
                    if (dropdownParent) {
                        const accordionContent = dropdownParent.querySelector('.accordion-content');
                        if (accordionContent && accordionContent.classList.contains('hidden')) {
                            accordionContent.classList.remove('hidden');
                        }
                        dropdownParent.classList.add('active');
                        const toggle = dropdownParent.querySelector('.dropdown-toggle');
                        if (toggle) {
                            const arrow = toggle.querySelector('.arrow');
                            if (arrow) arrow.style.transform = 'rotate(180deg)';
                        }
                    }
                }
            }

            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.remove('sidebar-active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const parentDropdown = this.parentElement;
                const currentlyActive = parentDropdown.classList.contains('active');

                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });

                if (!currentlyActive) {
                    parentDropdown.classList.add('active');
                }
            });
        });


        const notificationBellContainer = document.getElementById('notification-bell-container');
        const notificationBadge = document.getElementById('notification-badge');
        const notificationDropdown = document.getElementById('notification-dropdown');
        const notificationList = document.getElementById('notification-list');

        if (notificationBellContainer && notificationBadge && notificationDropdown && notificationList) {
            function fetchUnreadNotificationCount() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (!meta) return;

                fetch('{{ route('notificaciones.no-leidas') }}', {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': meta.getAttribute('content')
                            }
                        })
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(data => {
                        if (data.count > 0) {
                            notificationBadge.textContent = data.count;
                            notificationBadge.classList.remove('hidden');
                        } else {
                            notificationBadge.classList.add('hidden');
                        }
                    })
                    .catch(e => console.error(e));
            }

            function loadNotifications() {
                notificationList.innerHTML = '<p class="text-gray-500 text-sm p-4">Cargando...</p>';
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (!meta) return;

                fetch('{{ route('notificaciones.api-list') }}', {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': meta.getAttribute('content')
                            }
                        })
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(data => {
                        notificationList.innerHTML = data.html ||
                            '<p class="text-gray-500 text-sm p-4">No hay notificaciones.</p>';
                        attachNotificationClickHandlers();
                    })
                    .catch(e => {
                        notificationList.innerHTML =
                            '<p class="text-red-500 text-sm p-4">Error al cargar.</p>';
                    });
            }

            notificationBellContainer.addEventListener('click', function(event) {
                event.stopPropagation();
                const mesDropdown = document.getElementById('mesSelectorDropdown');
                if (mesDropdown) {
                    mesDropdown.classList.add('hidden');
                }
                notificationDropdown.classList.toggle('hidden');
                if (!notificationDropdown.classList.contains('hidden')) {
                    loadNotifications();
                    fetchUnreadNotificationCount();
                }
            });

            document.body.addEventListener('click', function(event) {
                if (!notificationBellContainer.contains(event.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });

            fetchUnreadNotificationCount();
            setInterval(fetchUnreadNotificationCount, 30000);

            function attachNotificationClickHandlers() {
                notificationList.querySelectorAll('.notificacion-nueva form button').forEach(button => {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();
                        const form = this.closest('form');
                        const item = this.closest('.notificacion-nueva');
                        const meta = document.querySelector('meta[name="csrf-token"]');

                        fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': meta.getAttribute('content')
                                },
                                body: new FormData(form)
                            })
                            .then(r => r.ok ? r.json() : Promise.reject(r))
                            .then(() => {
                                item.remove();
                                fetchUnreadNotificationCount();
                            });
                    });
                });
            }
        }
    });
    </script>
    @stack('scripts')
    @stack('modals')
</body>

</html>
