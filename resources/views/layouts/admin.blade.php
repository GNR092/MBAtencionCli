@extends('layouts.base')

@section('layout-content')

    {{-- Estilos específicos de la aplicación --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <style>
        /* --- AJUSTES PARA EL FONDO ANIMADO --- */
        #main-content {
            background-color: transparent !important;
        }

        /* Sidebar con efecto Glassmorphism */
        #sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            height: 100vh;
            width: 280px;
            z-index: 50;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: rgba(11, 22, 36, 0.92) !important;
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(216, 196, 149, 0.2);
        }

        /* Estado activo en móvil */
        #sidebar.sidebar-active { left: 0; }

        /* Overlay para cerrar menú móvil */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
            z-index: 40;
        }
        #sidebar-overlay.active { display: block; }

        /* Pantallas Grandes (Desktop) */
        @media (min-width: 1024px) {
            #sidebar {
                position: relative;
                left: 0;
                width: 260px;
                background-color: rgba(17, 33, 52, 0.90) !important;
            }
            #sidebar.sidebar-collapsed { margin-left: -260px; }
        }

        /* --- CONTENIDO Y TABLAS --- */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 8px;
            background: rgba(17, 33, 52, 0.6);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(216, 196, 149, 0.1);
        }

        table { width: 100%; border-collapse: collapse; min-width: 700px; }
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

        /* --- COMPONENTES --- */
        .dropdown .accordion-content { display: none; }
        .dropdown.active .accordion-content { display: block; }
        .arrow { transition: transform 0.3s ease; display: inline-block; margin-left: 5px; }
        .dropdown.active .arrow { transform: rotate(180deg); }

        /* Inputs con texto negro al escribir */
        input:not([type="button"]):not([type="submit"]), select, textarea {
            color: #000 !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
        }

        /* Animación suave */
        .fade-in-content {
            animation: fadeIn 0.6s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div id="sidebar-overlay"></div>

    <div class="h-screen flex flex-col relative z-10">
        <header class="relative z-30 flex items-center justify-between px-4 py-3 text-white shadow-lg border-b border-[#d8c495]/30 bg-[#112134]/80 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" class="p-1 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-xs md:text-sm font-bold uppercase tracking-wider text-[#d8c495] truncate max-w-[120px] md:max-w-none">
                    {{ currentUser()->name }}
                </h1>

                <div class="relative ml-2" id="notification-bell-container">
                    <button class="relative p-1 hover:text-[#d8c495] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                        <span id="notification-badge" class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white hidden">0</span>
                    </button>
                    <div id="notification-dropdown" class="absolute left-0 md:right-0 md:left-auto z-50 mt-3 hidden w-72 md:w-80 rounded-xl bg-white shadow-2xl text-gray-800 border border-gray-200">
                        <div id="notification-list" class="max-h-60 overflow-y-auto"></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <input type="month" id="start" name="start" value="{{ request('month', date('Y-m')) }}"
                       class="hidden md:block rounded-md border border-[#d8c495]/40 bg-white/10 px-2 py-1 text-sm font-semibold text-white outline-none">
                <a href="/" class="shrink-0">
                    <img src="/uploads/Logo-Png.png" alt="Logo" class="h-7 md:h-9">
                </a>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <aside id="sidebar">
                <div id="sidebar-content" class="flex flex-col h-full p-4 overflow-y-auto custom-scrollbar">
                    <nav class="space-y-2">
                        <div class="dropdown">
                            <button class="dropdown-toggle flex w-full items-center justify-between p-3 rounded-lg hover:bg-white/10 transition-all text-sm font-medium text-white">
                                <span>Finanzas y Contabilidad</span>
                                <span class="arrow text-[10px]">&#9660;</span>
                            </button>
                            <div class="accordion-content hidden space-y-1 mt-1 bg-black/20 rounded-lg">
                                <a href="/cuentas-por-pagar" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Cuentas por pagar</a>
                                <a href="/facturas" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Facturas</a>
                                <a href="/inpuestos" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Impuestos</a>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="dropdown-toggle flex w-full items-center justify-between p-3 rounded-lg hover:bg-white/10 transition-all text-sm font-medium text-white">
                                <span>Gestión Empresarial y Legal</span>
                                <span class="arrow text-[10px]">&#9660;</span>
                            </button>
                            <div class="accordion-content hidden space-y-1 mt-1 bg-black/20 rounded-lg">
                                <a href="/lista-de-inversionistas" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Lista de inversionistas</a>
                                <a href="/subir-archivo" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Administración de contratos</a>
                                <a href="/incrementos" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Incrementos de Importe</a>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="dropdown-toggle flex w-full items-center justify-between p-3 rounded-lg hover:bg-white/10 transition-all text-sm font-medium text-white">
                                <span>Operaciones y Atención al Cliente</span>
                                <span class="arrow text-[10px]">&#9660;</span>
                            </button>
                            <div class="accordion-content hidden space-y-1 mt-1 bg-black/20 rounded-lg">
                                <a href="/registro_user" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Registro de Usuarios</a>
                                <a href="/admi_user" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Administrador de Usuarios</a>
                                <a href="/enviar-avisos" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Avisos</a>
                                <a href="{{ route('admin.users.chat-directory') }}" class="block py-2.5 pl-8 text-xs text-gray-300 hover:text-[#d8c495] hover:bg-white/5 transition-colors">Directorio (Chat)</a>
                                <a href="{{ route('admin.logos.index') }}" class="block py-2.5 pl-8 text-xs text-[#d8c495] font-semibold hover:bg-white/5 transition-colors">Gestión Logos</a>
                                <a href="{{ route('admin.anuncios.index') }}" class="block py-2.5 pl-8 text-xs text-[#d8c495] font-semibold hover:bg-white/5 transition-colors">Gestión Anuncios</a>
                            </div>
                        </div>
                    </nav>


                        <a href="{{ route('logout') }}"
                           class="flex items-center justify-center w-full gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition-all hover:bg-red-700 active:scale-95 shadow-lg shadow-red-900/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            CERRAR SESIÓN
                        </a>

                </div>
            </aside>

            <main id="main-content" class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar">
                <div class="max-w-7xl mx-auto fade-in-content">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @push('scripts')
<script>
    // Sidebar toggle handler mejorado
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth < 1024) {
            // Lógica Móvil
            sidebar.classList.toggle('sidebar-active');
            sidebarOverlay.classList.toggle('active');
        } else {
            // Lógica Desktop (colapso lateral)
            sidebar.classList.toggle('sidebar-collapsed');
        }
    });

    // Cerrar sidebar al hacer clic en el overlay (solo móvil)
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('sidebar-active');
        sidebarOverlay.classList.remove('active');
    });
    document.addEventListener('DOMContentLoaded', function () {
        // Month change handler
        document.getElementById('start').addEventListener('change', function() {
            const month = this.value;
            if (month) {
                const url = new URL(window.location.href);
                url.searchParams.set('month', month);
                window.location.href = url.toString();
            }
        });

        // Dropdown toggle handler (for sidebar)
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const parentDropdown = this.parentElement;
                const currentlyActive = parentDropdown.classList.contains('active');

                // Close all dropdowns
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });

                // If the clicked dropdown was not already active, open it
                if (!currentlyActive) {
                    parentDropdown.classList.add('active');
                }
            });
        });

        // Sidebar toggle handler
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const sidebarToggle = document.getElementById('sidebar-toggle');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-content-collapsed');
        });

        // Notification Bell Logic
        const notificationBellContainer = document.getElementById('notification-bell-container');
        const notificationBadge = document.getElementById('notification-badge');
        const notificationDropdown = document.getElementById('notification-dropdown');
        const notificationList = document.getElementById('notification-list');

        // Only proceed if notification elements exist (for cases where this script might be loaded in pages without the bell)
        if (notificationBellContainer && notificationBadge && notificationDropdown && notificationList) {
            function fetchUnreadNotificationCount() {
                fetch('{{ route('notifications.unreadCount') }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.count > 0) {
                        notificationBadge.textContent = data.count;
                        notificationBadge.classList.remove('hidden');
                    } else {
                        notificationBadge.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching unread notification count:', error);
                    notificationBadge.classList.add('hidden'); // Hide badge on error
                });
            }

            function loadNotifications() {
                notificationList.innerHTML = '<p class="text-gray-500 text-sm p-4">Cargando notificaciones...</p>'; // Show loading message
                fetch('{{ route('notificaciones.index') }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.html) {
                        notificationList.innerHTML = data.html;
                    } else {
                        notificationList.innerHTML = '<p class="text-gray-500 text-sm p-4">No hay notificaciones disponibles.</p>';
                    }
                    attachNotificationClickHandlers();
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    notificationList.innerHTML = '<p class="text-red-500 text-sm p-4">Error al cargar notificaciones.</p>';
                });
            }

            // Toggle dropdown visibility
            notificationBellContainer.addEventListener('click', function (event) {
                event.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
                if (!notificationDropdown.classList.contains('hidden')) {
                    loadNotifications();
                    fetchUnreadNotificationCount();
                }
            });

            // Close dropdown when clicking outside
            document.body.addEventListener('click', function (event) {
                if (!notificationBellContainer.contains(event.target) && !notificationDropdown.classList.contains('hidden')) {
                    notificationDropdown.classList.add('hidden');
                }
            });

            // Initial fetch and periodic refresh for count
            fetchUnreadNotificationCount();
            setInterval(fetchUnreadNotificationCount, 30000);

            function attachNotificationClickHandlers() {
                notificationList.querySelectorAll('.notificacion-nueva form button[type="submit"]').forEach(button => {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();

                        const form = this.closest('form');
                        const notificationItem = this.closest('.notificacion-nueva');

                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: new FormData(form)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(() => {
                            notificationItem.remove();
                            fetchUnreadNotificationCount();
                            if (notificationList.querySelectorAll('.notificacion-nueva').length === 0) {
                                notificationList.innerHTML = '<p class="text-gray-500 text-sm p-4">No tienes notificaciones nuevas.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error marking notification as read:', error);
                        });
                    });
                });
            }
        } // End if notification elements exist
    });
</script>
    @endpush
@endsection
