<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>MB SIGNATURE PROPERTIES</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/lt-afficher-neue" rel="stylesheet">

    <style>
        /* Estilos que no se pueden poner fácilmente como clases directas */
        body {
            background-color: #112134 !important;
            background-image: url('https://lawebdelasesor.com/wp-content/uploads/2025/06/Noche2.png') !important;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-position: center !important;
            background-attachment: fixed !important;
            font-family: 'Montserrat', sans-serif !important;
            color: #fff !important;
        }
        /* El overlay oscuro del body original */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: #112134;
            opacity: .9;
            z-index: -1;
        }

        /* Lógica de acordeón del sidebar */
        .dropdown .accordion-content { display: none; }
        .dropdown.active .accordion-content { display: block; }
        .dropdown.active .arrow { transform: rotate(180deg); }
        .arrow { transition: transform 0.3s ease; display: inline-block; margin-left: 5px; }

        /* Estilos para tablas que se inyectarán en las vistas hijas */
        table { width: 100% !important; border-collapse: collapse !important; margin-top: 20px !important; }
        th { background-color: rgba(17,33,52,.9) !important; color: #d8c495 !important; padding: 12px !important; text-align: left !important; font-weight: 700 !important; border-bottom: 2px solid #d8c495 !important; position: sticky !important; top: 0 !important; z-index: 1 !important; }
        td { border-bottom: 1px solid #d8c495 !important; padding: 10px !important; color: #fff !important; background-color: rgba(17,33,52,.78) !important; }
        tr:nth-child(even) td { background-color: rgba(25,42,66,.78) !important; }
        tr:hover td { background-color: rgba(28,50,88,.9) !important; }

        /* REGLA NUEVA: Texto negro para inputs donde el usuario escribe */
        input:not([type="button"]):not([type="submit"]),
        select,
        textarea {
            color: #000000 !important; /* Texto negro al escribir */
            background-color: rgba(255, 255, 255, 0.9) !important; /* Fondo claro para que se lea el negro */
        }

        /* Ajuste para placeholders (que se vean gris suave) */
        input::placeholder, textarea::placeholder {
            color: #666 !important;
        }
    </style>
</head>
<body>
<div class="h-screen flex flex-col">
    <header class="relative flex items-center justify-between p-2 text-white shadow-md border-b border-[#d8c495]/35"
            style="background-color: rgba(17,33,52,0.85) !important;">

        <div class="flex items-center">
            <button id="sidebar-toggle" class="mr-2 text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <h1 class="text-lg uppercase">BIENVENIDO {{ currentUser()->name }}</h1>

            <div class="relative inline-block ml-4" id="notification-bell-container">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 cursor-pointer text-white transition hover:text-[#d8c495]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
                <span id="notification-badge" class="absolute -top-2 -right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white hidden">
                        0
                    </span>

                <div id="notification-dropdown" class="absolute right-0 z-10 mt-2 hidden w-80 rounded-md bg-white shadow-lg">
                    <div class="py-1" role="menu">
                        <div class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700">Notificaciones</div>
                        <div id="notification-list" class="max-h-60 overflow-y-auto">
                            <p class="p-4 text-sm text-gray-500">Cargando notificaciones...</p>
                        </div>
                        <a href="{{ route('notificaciones.index') }}" class="block border-t border-gray-200 px-4 py-2 text-center text-sm text-blue-600 hover:bg-gray-100">
                            Ver todas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center">
            <input type="month" id="start" name="start" min="2018-03" value="{{ request('month', date('Y-m')) }}"
                   class="relative inline-flex items-center rounded-md border border-[#d8c495]/35 bg-white/10 px-2 text-lg font-bold text-white outline-none">
            <a href="#">
                <img src="/uploads/Logo-Png.png" alt="Logo" class="ml-4 h-8">
            </a>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        <div id="sidebar" class="sidebar" style="background-color: rgba(17,33,52,0.95) !important;">
            <div id="sidebar-content" class="flex flex-col h-full p-4">

                <div class="dropdown">
                    <button class="dropdown-toggle mb-4 flex w-full cursor-pointer items-center justify-between py-3 text-left text-base text-white hover:bg-white/10">
                        <span>Finanzas y Contabilidad</span>
                        <span class="arrow">&#9660;</span>
                    </button>
                    <div class="accordion-content hidden rounded-md bg-black/20">
                        <a href="/cuentas-por-pagar" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Cuentas por pagar</a>
                        <a href="/facturas" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Facturas</a>
                        <a href="/inpuestos" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Inpuestos</a>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="dropdown-toggle mb-4 flex w-full cursor-pointer items-center justify-between py-3 text-left text-base text-white hover:bg-white/10">
                        <span>Gestión Empresarial y Legal</span>
                        <span class="arrow">&#9660;</span>
                    </button>
                    <div class="accordion-content hidden rounded-md bg-black/20">
                        <a href="/lista-de-inversionistas" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Lista de inversionistas</a>
                        <a href="/subir-archivo" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Administración de contratos</a>
                        <a href="/incrementos" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Incrementos de Importe</a>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="dropdown-toggle mb-4 flex w-full cursor-pointer items-center justify-between py-3 text-left text-base text-white hover:bg-white/10">
                        <span>Operaciones y Atención al Cliente</span>
                        <span class="arrow">&#9660;</span>
                    </button>
                    <div class="accordion-content hidden rounded-md bg-black/20">
                        <a href="/registro_user" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Registro de Usuarios</a>
                        <a href="/admi_user" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Administrador de Usuarios</a>
                        <a href="/enviar-avisos" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Avisos</a>
                        <a href="{{ route('admin.users.chat-directory') }}" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white">Directorio de Usuarios (Chat)</a>
                        <a href="{{ route('admin.logos.index') }}" class="block py-2 pl-8 font-medium text-[#d8c495] no-underline transition-all duration-200 hover:border-l-4 hover:border-[#d8c495] hover:bg-white/10 hover:text-white"> Gestión de Logos (Carrusel)</a>
                    </div>
                </div>

                <a href="{{ route('logout') }}" class="mt-auto w-full rounded-lg bg-red-600 px-4 py-2 text-white transition duration-300 ease-in-out hover:-translate-y-1 hover:scale-105 hover:bg-red-700 text-center">
                    Cerrar sesión
                </a>
            </div>
        </div>

        <main id="main-content" class="flex-1 overflow-y-auto p-8" style="background-color: rgba(17,33,52,0.85) !important;">
            @yield('content')
        </main>
    </div>
</div>

<script>
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
@stack('scripts')
</body>
</html>
