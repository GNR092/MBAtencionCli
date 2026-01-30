<!DOCTYPE html>
<html >
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>MB SIGNATURE PROPERTIES</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="preconnect" href="https://fonts.googleapis.com">
       <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
       <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
               <link href="https://fonts.cdnfonts.com/css/lt-afficher-neue" rel="stylesheet">
                 <style>
            .dropdown .accordion-content {
                display: none;
            }
            .dropdown.active .accordion-content {
                display: block;
            }
            .dropdown.active .arrow {
                transform: rotate(180deg);
            }
            .arrow {
                transition: transform 0.3s ease;
                display: inline-block;
                margin-left: 5px;
            }
        </style>
    </head>
    <body >
    <div class="h-screen flex flex-col">
        <!-- Header -->
        <header class="relative bg-[#2f2f2f] text-white p-2 shadow-md flex items-center justify-between">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="text-white focus:outline-none mr-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-lg uppercase">BIENVENIDO {{ currentUser()->name }}</h1>
                <!-- Notification Bell Icon and Badge -->
                <div class="relative inline-block ml-4" id="notification-bell-container">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white cursor-pointer hover:text-[#d8c495] transition">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                    </svg>
                    <span id="notification-badge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center hidden">
                        0
                    </span>

                    <!-- Notification Dropdown -->
                    <div id="notification-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-10 hidden">
                        <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="notification-bell">
                            <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-200">
                                Notificaciones
                            </div>
                            <div id="notification-list" class="max-h-60 overflow-y-auto">
                                <!-- Notifications will be loaded here via AJAX -->
                                <p class="text-gray-500 text-sm p-4">Cargando notificaciones...</p>
                            </div>
                            <a href="{{ route('notificaciones.index') }}" class="block px-4 py-2 text-sm text-center text-blue-600 hover:bg-gray-100 border-t border-gray-200">
                                Ver todas
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center">
                <input type="month" id="start" name="start" min="2018-03" value="{{ request('month', date('Y-m')) }}" class="relative inline-flex items-center text-lg bg-gray-700 text-white rounded-md px-2">
                <a href="#">
                    <img src="/uploads/Logo-Png.png" alt="Logo" class="h-8 ml-4">
                </a>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar -->
            <div id="sidebar" class="sidebar">
                <div id="sidebar-content" class="flex flex-col h-full">
                    <div class="dropdown">
                        <button class="text-white py-3 text-base cursor-pointer w-full mb-4 text-left hover:bg-gray-600 dropdown-toggle flex items-center justify-between">
                            <span>Finanzas y Contabilidad</span>
                            <span class="arrow">&#9660;</span>
                        </button>
                        <div class="hidden bg-gray-700 rounded-md accordion-content">
                            <a href="/cuentas-por-pagar" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Cuentas por pagar</a>
                            <a href="/facturas" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Facturas</a>
                            <a href="/inpuestos" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Inpuestos</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="text-white py-3 text-base cursor-pointer w-full mb-4 text-left hover:bg-gray-600 dropdown-toggle flex items-center justify-between">
                            <span>Gestión Empresarial y Legal</span>
                            <span class="arrow">&#9660;</span>
                        </button>
                        <div class="hidden bg-gray-700 rounded-md accordion-content">
                            <a href="/lista-de-inversionistas" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Lista de inversionistas</a>
                            <a href="/subir-archivo" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Administración de contratos</a>
                            <a href="/incrementos" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Incrementos de Importe</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="text-white py-3 text-base cursor-pointer w-full mb-4 text-left hover:bg-gray-600 dropdown-toggle flex items-center justify-between">
                            <span>Operaciones y Atención al Cliente</span>
                            <span class="arrow">&#9660;</span>
                        </button>
                        <div class="hidden bg-gray-700 rounded-md accordion-content">
                            <a href="/registro_user" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Registro de Usuarios</a>
                            <a href="/admi_user" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Administrador de Usuarios</a>
                            <a href="/enviar-avisos" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Avisos</a>
                            <a href="{{ route('admin.users.chat-directory') }}" class="block py-2 pl-8 font-medium text-gray-200 no-underline transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:border-l-4 hover:border-yellow-400">Directorio de Usuarios (Chat)</a>
                        </div>
                    </div>

                    <a href="{{ route('logout') }}" class="mt-auto w-full bg-red-600 text-white px-4 py-2 rounded-lg transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700">
                        Cerrar sesión
                    </a>
                </div>
            </div>

            <!-- Main content -->
            <main id="main-content" class="flex-1 overflow-y-auto p-8">
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