<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - MB Signature</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        /* Specific styles for chat modal text visibility */
        #chatModal #chatInput {
            color: black !important;
        }
        #chatModal #chatInput::placeholder {
            color: #555 !important; /* Darker grey for placeholder */
        }
        #chatModal .text-gray-500 { /* For the "Inicia una conversación..." text */
            color: #333 !important;
        }
    </style>
</head>
<body class="bg-gray-100">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Contenido principal sin sidebar --}}
<main class="min-h-screen">
    <div class="w-full min-h-screen p-8 space-y-10 font-[system-ui] bg-gradient-to-br from-[#112134] to-[#000000]">

        {{-- Bienvenida --}}
        <header class="flex flex-col md:flex-row items-center md:items-center md:justify-center gap-8 animate-fadeInUp py-10 bg-[#3c3c3c] border-b border-[rgba(216,196,149,0.35)] shadow-[0_4px_10px_rgba(0,0,0,0.2)]">
            <div class="flex items-center gap-4">
                <div class="relative group">
                    @php
                        $avatarSize = "w-16 h-16 md:w-20 md:h-20";
                    @endphp

                    @if($user->foto)
                        <img id="avatar-preview" src="{{ asset('storage/' . $user->foto) . '?v=' . $user->updated_at->timestamp }}"
                             alt="Avatar Preview"
                             class="{{ $avatarSize }} rounded-full object-cover border-2 border-[#d8c495] shadow-lg">
                    @else
                        <div id="avatar-initial" class="{{ $avatarSize }} flex items-center justify-center rounded-full bg-[#112134] text-white font-bold text-2xl shadow-lg border-2 border-[#d8c495]">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                        <label for="fotoPerfilUsuario" class="cursor-pointer text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col">
                    <h1 class="text-xl md:text-2xl font-bold text-white tracking-tight">
                        Bienvenido, <span class="text-[#d8c495]">{{ $user->name ?? 'Usuario' }}</span>
                    </h1>
                    <p class="text-sm text-gray-400 font-light italic">Panel de Cliente</p>
                </div>
            </div>

            <form method="get" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm border border-[#d8c495] text-[#d8c495] rounded-xl hover:bg-[#d8c495] hover:text-[#112134] transition-all duration-300 flex items-center gap-2 group">
                    <span>Cerrar sesión</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                </button>
            </form>
        </header>

        {{-- Contenedor de Acciones y Recuadro de Anuncios --}}
        <section class="mt-12 grid grid-cols-1 md:grid-cols-5 gap-6">

            {{-- Fila superior: Las 5 opciones originales se mantienen intactas --}}
            <a href="{{ route('facturacion') }}" class="bg-white rounded-2xl p-6 text-center shadow-lg border-t-4 border-blue-500 hover:scale-[1.05] transition transform flex flex-col items-center justify-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375H12a1.125 1.125 0 0 1-1.125-1.125V1.5A2.25 2.25 0 0 0 8.75 0H7.5A2.25 2.25 0 0 0 5.25 1.5V3.75a3.375 3.375 0 0 0 3.375 3.375H12c.621 0 1.125.504 1.125 1.125v2.625c0 .621-.504 1.125-1.125 1.125H8.75a2.25 2.25 0 0 0-2.25 2.25v2.25A2.25 2.25 0 0 0 8.75 21H12c.621 0 1.125-.504 1.125-1.125V18.75a3.375 3.375 0 0 0 3.375-3.375z" />
                </svg>
                <span class="font-semibold text-lg text-blue-800">Facturación</span>
                <span class="text-sm text-gray-500">Subir y validar XMLs/PDFs</span>
            </a>

            <a href="{{ route('notificaciones.index') }}" class="bg-white rounded-2xl p-6 text-center shadow-lg border-t-4 border-green-500 hover:scale-[1.05] transition transform flex flex-col items-center justify-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                </svg>
                <span class="font-semibold text-lg text-green-800">Notificaciones</span>
                <span class="text-sm text-gray-500">Ver avisos importantes</span>
            </a>

            <a href="{{ route('cuentasCobrar') }}" class="bg-white rounded-2xl p-6 text-center shadow-lg border-t-4 border-yellow-500 hover:scale-[1.05] transition transform flex flex-col items-center justify-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-yellow-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5V17.25m11.2-4.5H12.975m2.475 0a.75.75 0 0 1 .75.75v3.75m-2.475-4.5a.75.75 0 0 0-.75.75v3.75m2.475-4.5H12.975m-2.475-4.5a.75.75 0 0 1 .75.75v3.75m-2.475-4.5a.75.75 0 0 0-.75.75v3.75m-1.248-8.79a7.5 7.5 0 0 1 10.607 0C21.497 9.873 22 11.234 22 12.75c0 1.517-.503 2.878-1.47 3.966M7.5 7.5h.008v.008H7.5V7.5Zm6.002 0h.008v.008h-.008V7.5ZM21.75 12c0 3.993-3.266 7.25-7.258 7.25H3.75a60.07 60.07 0 0 1-15.797-2.101A.75.75 0 0 1 0 18.75V17.25C0 14.733 3.504 12.75 6.75 12.75a7.5 7.5 0 0 0 1.248-8.79Z" />
                </svg>
                <span class="font-semibold text-lg text-yellow-800">Cuentas por Cobrar</span>
                <span class="text-sm text-gray-500">Pagos pendientes</span>
            </a>

            <a href="{{ route('estadosDeCuenta') }}" class="bg-white rounded-2xl p-6 text-center shadow-lg border-t-4 border-purple-500 hover:scale-[1.05] transition transform flex flex-col items-center justify-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-purple-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.079 0-2.15.221-3.133.664a10.973 10.973 0 0 0 6.267 19.344c.414.165.918-.293.918-.75V18.75a2.25 2.25 0 0 0-2.25-2.25H9.75a2.25 2.25 0 0 0-2.25 2.25v.375c0 .621-.504 1.125-1.125 1.125H4.5a2.25 2.25 0 0 1-2.25-2.25V6.042a48.08 48.08 0 0 1 9-.664Z" />
                </svg>
                <span class="font-semibold text-lg text-purple-800">Estados de Cuenta</span>
                <span class="text-sm text-gray-500">Historial de estados</span>
            </a>

            <a href="{{ route('contratos.index') }}" class="bg-white rounded-2xl p-6 text-center shadow-lg border-t-4 border-red-500 hover:scale-[1.05] transition transform flex flex-col items-center justify-center space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-red-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375H12a1.125 1.125 0 0 1-1.125-1.125V1.5A2.25 2.25 0 0 0 8.75 0H7.5A2.25 2.25 0 0 0 5.25 1.5V3.75a3.375 3.375 0 0 0 3.375 3.375H12c.621 0 1.125.504 1.125 1.125v2.625c0 .621-.504 1.125-1.125 1.125H8.75a2.25 2.25 0 0 0-2.25 2.25v2.25A2.25 2.25 0 0 0 8.75 21H12c.621 0 1.125-.504 1.125-1.125V18.75a3.375 3.375 0 0 0 3.375-3.375z" />
                </svg>
                <span class="font-semibold text-lg text-red-800">Contratos</span>
                <span class="text-sm text-gray-500">Visualiza activos</span>
            </a>

            {{-- Espaciado y Recuadro de Anuncios --}}
            <div class="hidden md:block md:col-span-3"></div>

            {{-- Recuadro con altura ajustada a la línea roja --}}
            <div class="md:col-span-2 h-[480px] bg-white/5 backdrop-blur-sm rounded-2xl border-2 border-dashed border-[#d8c495]/20 flex flex-col items-center justify-center p-6 text-white text-center shadow-xl mb-16">
                <div class="opacity-40">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto mb-2 text-[#d8c495]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="text-[#d8c495] font-semibold uppercase tracking-widest text-xs">Espacio para Anuncios</p>
                </div>
            </div>
        </section>


    </div>
</main>
    <!-- Floating Chat Button -->
    <button id="openChatBtn" style="position: fixed; bottom: 24px; right: 24px; background-color: #3b82f6; color: white; padding: 16px; border-radius: 9999px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); z-index: 9999; cursor: pointer;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H15.75m6.375 0a9.75 9.75 0 1 1-19.5 0 9.75 9.75 0 0 1 19.5 0Z" />
        </svg>
    </button>

    <!-- Chat Modal -->
    <div id="chatModal" style="position: fixed; bottom: 80px; right: 24px; width: 320px; height: 384px; background-color: white; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; z-index: 9999; display: none;">
        <div style="background-color: #3C3C3C; color: white; padding: 12px; border-top-left-radius: 8px; border-top-right-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-weight: bold;">Atención al Cliente</h3>
            <button id="closeChatBtn" style="color: #d9d9d9; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="chatMessages" style="flex: 1; padding: 12px; overflow-y: auto; background-color: #f3f4f6;">
            <!-- Messages will be loaded here -->
            <div style="text-align: center; color: #6b7280; padding-top: 16px; padding-bottom: 16px;">Inicia una conversación...</div>
        </div>
        <div style="padding: 12px; border-top: 1px solid #e5e7eb; background-color: white;">
            <input type="text" id="chatInput" placeholder="Escribe tu mensaje..." style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0); color: black !important;">
            <button id="sendChatBtn" style="margin-top: 8px; width: 100%; background-color: #3b82f6; color: white; padding: 8px; border-radius: 4px; cursor: pointer;">Enviar</button>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Chat button and modal logic
        const openChatBtn = document.getElementById('openChatBtn');
        const closeChatBtn = document.getElementById('closeChatBtn');
        const chatModal = document.getElementById('chatModal');

        if (openChatBtn && closeChatBtn && chatModal) {
            openChatBtn.addEventListener('click', function() {
                chatModal.style.display = 'flex'; // Show modal
                fetchMessages(); // Fetch messages when chat modal opens
            });

            closeChatBtn.addEventListener('click', function() {
                chatModal.style.display = 'none'; // Hide modal
            });

            const chatMessagesDiv = document.getElementById('chatMessages');
            const chatInput = document.getElementById('chatInput');
            const sendChatBtn = document.getElementById('sendChatBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function scrollToBottom() {
                chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
            }

            function displayMessages(messages) {
                chatMessagesDiv.innerHTML = ''; // Clear previous messages
                if (messages.length === 0) {
                    chatMessagesDiv.innerHTML = '<div style="text-align: center; color: #6b7280; padding-top: 16px; padding-bottom: 16px;">Inicia una conversación...</div>';
                    return;
                }

                messages.forEach(message => {
                    const messageElement = document.createElement('div');
                    const isSender = message.sender_id === {{ Js::from($user->id) }};

                    messageElement.style.marginBottom = '8px';
                    messageElement.style.padding = '8px';
                    messageElement.style.borderRadius = '8px';
                    messageElement.style.maxWidth = '70%';

                    if (isSender) {
                        messageElement.style.backgroundColor = '#3b82f6';
                        messageElement.style.color = 'white';
                        messageElement.style.marginLeft = 'auto';
                    } else {
                        messageElement.style.backgroundColor = '#d1d5db';
                        messageElement.style.color = '#1f2937';
                        messageElement.style.marginRight = 'auto';
                    }
                    messageElement.textContent = message.message;
                    chatMessagesDiv.appendChild(messageElement);
                });
                scrollToBottom();
            }

            async function fetchMessages() {
                try {
                    const response = await fetch('{{ route('chat.getMessages') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (!response.ok) {
                        throw new Error('Failed to fetch messages');
                    }
                    const messages = await response.json();
                    displayMessages(messages);
                } catch (error) {
                    console.error('Error fetching messages:', error);
                    chatMessagesDiv.innerHTML = '<div style="text-align: center; color: #dc2626; padding-top: 16px; padding-bottom: 16px;">Error al cargar mensajes.</div>';
                }
            }

            async function sendMessage() {
                const messageText = chatInput.value.trim();
                if (messageText === '') {
                    return;
                }

                try {
                    const response = await fetch('{{ route('chat.sendMessage') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-with': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ message: messageText })
                    });

                    if (!response.ok) {
                        throw new Error('Failed to send message');
                    }

                    chatInput.value = ''; // Clear input
                } catch (error) {
                    console.error('Error sending message:', error);
                }
            }

            sendChatBtn.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });

            // Poll for new messages every 5 seconds when chat is open
            setInterval(() => {
                if (chatModal.style.display === 'flex') { // Check if modal is visible
                    fetchMessages();
                }
            }, 5000);
        }
    });
</script>
</body>
</html>
