@extends('layouts.base')

@section('layout-content')
    {{-- Assets y Fuentes --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <style>
        /* 1. PERFORACIÓN GLOBAL */
        /* Forzamos que los contenedores raíz no tengan color */
        html, body, #app-layout, .h-screen {
            background: transparent !important;
            background-color: transparent !important;
            background-image: none !important;
        }

        #main-content {
            background-color: transparent !important;
        }

        /* 2. ESTILOS DE COMPONENTES (Glassmorphism) */
        /* Mantenemos un fondo traslúcido para que el texto sea legible */
        header {
            background-color: rgba(17, 33, 52, 0.75) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(216, 196, 149, 0.2);
        }

        #sidebar {
            background-color: rgba(11, 22, 36, 0.9) !important;
            backdrop-filter: blur(10px);
        }

        /* Estilo para las tarjetas blancas de tu imagen (Facturación, etc) */
        /* Bajamos un poco la opacidad para que el fondo se note */
        .bg-white, .bg-gray-100 {
            background-color: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(4px);
        }

        /* 3. LÓGICA DE SIDEBAR Y DROPDOWNS */
        .dropdown .accordion-content { display: none; }
        .dropdown.active .accordion-content { display: block; }
        .arrow { transition: transform 0.3s ease; }
        .dropdown.active .arrow { transform: rotate(180deg); }

        /* Inputs: Texto negro */
        input:not([type="button"]):not([type="submit"]), select, textarea {
            color: #000 !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-radius: 6px;
        }
    </style>

    <div class="h-screen flex flex-col relative z-10 bg-transparent">
        <header class="relative z-30 flex items-center justify-between p-2 text-white shadow-md">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="text-white focus:outline-none mr-2 p-1 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-lg uppercase font-bold text-[#d8c495]">BIENVENIDO {{ currentUser()->name }}</h1>

                <div class="relative inline-block ml-4" id="notification-bell-container">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-400 cursor-pointer hover:text-[#d8c495] transition">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                    </svg>
                    <span id="notification-badge" class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center hidden">0</span>

                    <div id="notification-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-50 hidden text-gray-800">
                        <div class="py-1" role="menu">
                            <div class="px-4 py-2 text-sm font-bold border-b border-gray-200">Notificaciones</div>
                            <div id="notification-list" class="max-h-60 overflow-y-auto">
                                <p class="text-gray-500 text-sm p-4">Cargando...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <input type="month" id="start" name="start" value="{{ request('month', date('Y-m')) }}" class="relative inline-flex items-center text-lg bg-white/10 text-white rounded-md px-2 border border-[#d8c495]/40 outline-none">
                <a href="#">
                    <img src="/uploads/Logo-Png.png" alt="Logo" class="h-8">
                </a>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <aside id="sidebar" class="sidebar">
                <div id="sidebar-content" class="flex flex-col h-full p-4">
                    <nav class="space-y-2">
                        <div class="dropdown">
                            <button class="text-white py-3 px-3 text-base cursor-pointer w-full rounded-lg dropdown-toggle flex items-center justify-between hover:bg-white/10 transition-all">
                                <span>Operaciones</span>
                                <span class="arrow text-[10px]">&#9660;</span>
                            </button>
                            <div class="hidden bg-black/20 rounded-md accordion-content space-y-1 mt-1">
                                <a href="{{ route('user.facturacion') }}" class="block py-2.5 pl-8 text-sm text-gray-200 hover:text-[#d8c495] transition-colors">Facturación</a>
                                <a href="/cuentas-por-cobrar" class="block py-2.5 pl-8 text-sm text-gray-200 hover:text-[#d8c495] transition-colors">Cuentas por cobrar</a>
                                <a href="/estados-de-cuenta" class="block py-2.5 pl-8 text-sm text-gray-200 hover:text-[#d8c495] transition-colors">Estados de cuenta</a>
                                <a href="/contratos" class="block py-2.5 pl-8 text-sm text-gray-200 hover:text-[#d8c495] transition-colors">Contratos</a>
                                <a href="/notificaciones" class="block py-2.5 pl-8 text-sm text-gray-200 hover:text-[#d8c495] transition-colors">Notificaciones</a>
                            </div>
                        </div>
                    </nav>

                    <a href="{{ route('logout') }}" class="mt-auto w-full bg-red-600 text-white px-4 py-3 rounded-xl text-center font-bold shadow-lg hover:bg-red-700 transition-all active:scale-95">
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
            document.addEventListener('DOMContentLoaded', function () {
                
                document.getElementById('start').addEventListener('change', function() {
                    const month = this.value;
                    if (month) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('month', month);
                        window.location.href = url.toString();
                    }
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

                
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('main-content');
                const sidebarToggle = document.getElementById('sidebar-toggle');

                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-collapsed');
                    mainContent.classList.toggle('main-content-collapsed');
                });

                
                const notificationBellContainer = document.getElementById('notification-bell-container');
                const notificationBadge = document.getElementById('notification-badge');
                const notificationDropdown = document.getElementById('notification-dropdown');
                const notificationList = document.getElementById('notification-list');

                if (notificationBellContainer && notificationBadge && notificationDropdown && notificationList) {
                    function fetchUnreadNotificationCount() {
                        fetch('{{ route('notificaciones.no-leidas') }}', {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.count > 0) {
                                    notificationBadge.textContent = data.count;
                                    notificationBadge.classList.remove('hidden');
                                } else {
                                    notificationBadge.classList.add('hidden');
                                }
                            })
                            .catch(error => console.error('Error fetching count:', error));
                    }

                    function loadNotifications() {
                        notificationList.innerHTML = '<p class="text-gray-500 text-sm p-4">Cargando...</p>';
                        fetch('{{ route('notificaciones.index') }}', {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                notificationList.innerHTML = data.html || '<p class="text-gray-500 text-sm p-4">No hay notificaciones.</p>';
                                attachNotificationClickHandlers();
                            })
                            .catch(error => {
                                notificationList.innerHTML = '<p class="text-red-500 text-sm p-4">Error al cargar.</p>';
                            });
                    }

                    notificationBellContainer.addEventListener('click', function (event) {
                        event.stopPropagation();
                        notificationDropdown.classList.toggle('hidden');
                        if (!notificationDropdown.classList.contains('hidden')) {
                            loadNotifications();
                            fetchUnreadNotificationCount();
                        }
                    });

                    document.body.addEventListener('click', function (event) {
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

                                fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: new FormData(form)
                                })
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
    @endpush

@endsection
