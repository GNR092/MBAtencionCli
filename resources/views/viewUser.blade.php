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
        .animate-fadeInUp { animation: fadeInUp 0.6s ease-out forwards; }

        /* --- CARRUSEL INFINITO SIN RECUADRO --- */
        .marquee-viewport {
            width: 100%;
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .marquee-wrapper {
            display: flex;
            gap: 100px;
            animation: scroll 40s linear infinite;
            width: max-content;
            will-change: transform;
        }

        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .marquee-wrapper:hover { animation-play-state: paused; }

        .logo-img {
            height: 150px !important;
            width: auto !important;
            max-width: none !important;
            object-fit: contain !important;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
            transition: transform 0.3s ease;
        }

        .logo-img:hover { transform: scale(1.1); }

        /* Estilos del chat - CORREGIDOS PARA ESCRITURA */
        #chatModal #chatInput {
            color: #000000 !important;
            background-color: #ffffff !important;
        }
        #chatModal #chatInput::placeholder { color: #888 !important; }
    </style>
</head>
<body class="bg-gray-100">
<meta name="csrf-token" content="{{ csrf_token() }}">

<main class="min-h-screen">
    <div class="w-full min-h-screen p-8 space-y-10 font-[system-ui]" style="background:#7d7d7d !important;">

        {{-- Header Carbón (#3c3c3c) --}}
        <header class="flex flex-row items-center justify-between w-full animate-fadeInUp py-4 px-6 mb-6 rounded-2xl shadow-2xl" style="background-color: #3c3c3c !important;">
            <div class="flex items-center gap-4">
                <div class="relative group">
                    @php $avatarSize = "w-16 h-16 md:w-20 md:h-20"; @endphp
                    @if($user->foto)
                        <img src="{{ asset('storage/' . $user->foto) . '?v=' . $user->updated_at->timestamp }}" class="{{ $avatarSize }} rounded-full object-cover border-2 border-[#d8c495] shadow-lg">
                    @else
                        <div class="{{ $avatarSize }} flex items-center justify-center rounded-full bg-[#112134] text-white font-bold text-2xl shadow-lg border-2 border-[#d8c495]">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl md:text-2xl font-bold text-white tracking-tight">
                        Bienvenido, <span style="color: #d8c495 !important;">{{ $user->name ?? 'Usuario' }}</span>
                    </h1>
                    <p class="text-sm text-gray-300 font-light italic">Panel de Cliente</p>
                </div>
            </div>

            <form method="get" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm border border-[#d8c495] text-[#d8c495] rounded-xl hover:bg-[#d8c495] hover:text-[#3c3c3c] transition-all duration-300 font-bold">
                    Cerrar sesión
                </button>
            </form>
        </header>

        <section class="mt-12 grid grid-cols-1 md:grid-cols-5 gap-6">
            @php
                $opciones = [
                    ['route' => 'facturacion', 'label' => 'Facturación'],
                    ['route' => 'notificaciones.index', 'label' => 'Notificaciones'],
                    ['route' => 'cuentasCobrar', 'label' => 'Cuentas Cobrar'],
                    ['route' => 'estadosDeCuenta', 'label' => 'Estados Cuenta'],
                    ['route' => 'contratos.index', 'label' => 'Contratos'],
                ];
            @endphp

            @foreach($opciones as $opt)
                <a href="{{ route($opt['route']) }}"
                   class="bg-white/90 backdrop-blur-sm rounded-2xl p-6 text-center shadow-lg border-b-4 border-transparent hover:border-[#d8c495] transition-all duration-300 transform hover:-translate-y-2 flex flex-col items-center justify-center group">
                    <span class="font-black text-lg uppercase" style="color: #3c3c3c !important;">{{$opt['label']}}</span>
                    <div class="w-0 group-hover:w-12 h-1 bg-[#d8c495] mt-2 transition-all duration-300"></div>
                </a>
            @endforeach

            {{-- CARRUSEL --}}
            <div class="md:col-span-3 h-[480px] flex flex-col justify-center overflow-hidden">
                <div class="marquee-viewport">
                    <div class="marquee-wrapper" id="marquee">
                        @foreach(\App\Models\Logo::where('activo', true)->get() as $logo)
                            <div class="flex-none px-12">
                                @if($logo->url_redireccion)
                                    <a href="{{ $logo->url_redireccion }}" target="_blank">
                                        <img src="{{ asset('storage/' . $logo->imagen_ruta) }}" class="logo-img" alt="logo">
                                    </a>
                                @else
                                    <img src="{{ asset('storage/' . $logo->imagen_ruta) }}" class="logo-img" alt="logo">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- RECUADRO ANUNCIOS --}}
            <div class="md:col-span-2 h-[480px] bg-white/5 backdrop-blur-sm rounded-2xl border-2 border-dashed border-white/20 flex flex-col items-center justify-center p-6 text-center shadow-xl mb-16">
                <p class="font-bold uppercase tracking-widest text-xs" style="color: #3c3c3c !important;">Espacio para Anuncios</p>
            </div>
        </section>
    </div>
</main>

{{-- Botón Chat Forzado --}}
<button id="openChatBtn" style="position: fixed; bottom: 24px; right: 24px; background-color: #3c3c3c !important; color: white !important; padding: 16px; border-radius: 9999px; z-index: 99999; cursor: pointer; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.4); pointer-events: auto;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 28px; height: 28px;">
        <path d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H15.75m6.375 0a9.75 9.75 0 1 1-19.5 0 9.75 9.75 0 0 1 19.5 0Z" />
    </svg>
</button>

{{-- Modal Chat Forzado --}}
<div id="chatModal" style="position: fixed; bottom: 80px; right: 24px; width: 320px; height: 384px; background-color: white !important; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); flex-direction: column; z-index: 99999; display: none; overflow: hidden; pointer-events: auto;">
    <div style="background-color: #3c3c3c !important; color: white !important; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-weight: bold; margin: 0; color: white !important;">Atención al Cliente</h3>
        <button id="closeChatBtn" style="color: white !important; background: none; border: none; cursor: pointer; font-size: 24px;">×</button>
    </div>
    <div id="chatMessages" style="flex: 1; padding: 12px; overflow-y: auto; background-color: #f3f4f6 !important;"></div>
    <div style="padding: 12px; border-top: 1px solid #e5e7eb; background: white !important;">
        <input type="text" id="chatInput" placeholder="Escribe tu mensaje..." style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; background: white !important; color: black !important; display: block !important; position: relative; z-index: 100000;">
        <button id="sendChatBtn" style="margin-top: 8px; width: 100%; background-color: #3c3c3c !important; color: white !important; padding: 10px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold;">Enviar</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const marquee = document.getElementById('marquee');
        if (marquee && marquee.children.length > 0) {
            const content = marquee.innerHTML;
            marquee.innerHTML = content + content + content;
        }

        const openBtn = document.getElementById('openChatBtn');
        const closeBtn = document.getElementById('closeChatBtn');
        const modal = document.getElementById('chatModal');
        const messagesDiv = document.getElementById('chatMessages');
        const input = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendChatBtn');
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (openBtn && modal) {
            openBtn.onclick = (e) => {
                e.preventDefault();
                modal.style.display = 'flex';
                fetchMessages();
                setTimeout(() => input.focus(), 100);
            };
            closeBtn.onclick = () => { modal.style.display = 'none'; };

            function displayMessages(msgs) {
                messagesDiv.innerHTML = '';
                msgs.forEach(m => {
                    const el = document.createElement('div');
                    const isSender = m.sender_id === {{ Js::from($user->id) }};
                    Object.assign(el.style, {
                        marginBottom: '8px', padding: '8px', borderRadius: '8px', maxWidth: '75%',
                        backgroundColor: isSender ? '#3c3c3c' : '#d1d5db',
                        color: isSender ? 'white' : '#1f2937', marginLeft: isSender ? 'auto' : '0',
                        fontSize: '14px'
                    });
                    el.textContent = m.message;
                    messagesDiv.appendChild(el);
                });
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }

            async function fetchMessages() {
                const res = await fetch('{{ route('chat.getMessages') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) displayMessages(await res.json());
            }

            async function sendMessage() {
                const text = input.value.trim();
                // Bloqueo de seguridad: evita enviar si está vacío o si ya se está enviando uno
                if (!text || input.readOnly) return;

                input.readOnly = true; // Bloqueamos el cuadro de texto
                sendBtn.disabled = true; // Deshabilitamos el botón

                try {
                    const res = await fetch('{{ route('chat.sendMessage') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ message: text })
                    });

                    if (res.ok) {
                        input.value = '';
                        await fetchMessages(); // Recargamos para ver el mensaje nuevo
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    input.readOnly = false; // Liberamos
                    sendBtn.disabled = false;
                    input.focus();
                }
            }

// Corregimos los disparadores para que no se crucen
            sendBtn.onclick = (e) => {
                e.preventDefault();
                sendMessage();
            };

            input.onkeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Evita que el navegador procese el Enter dos veces
                    sendMessage();
                }
            };


        }
    });
</script>
</body>
</html>
