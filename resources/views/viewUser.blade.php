<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - MB Signature</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>

        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #d8c495; border-radius: 10px; }


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

        .marquee-viewport:hover .marquee-wrapper { animation-play-state: paused; }

        .logo-img {
            height: 150px !important;
            width: auto !important;
            max-width: none !important;
            object-fit: contain !important;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
            transition: transform 0.3s ease;
        }

        .logo-img:hover { transform: scale(1.1); }

        #chatModal #chatInput {
            color: #000000 !important;
            background-color: #ffffff !important;
        }
        #chatModal #chatInput::placeholder { color: #888 !important; }
    </style>
</head>


<body class="bg-gray-100">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- VIDEO FLOTANTE DE MIGUEL BARBOSA (Esquina Inferior Izquierda) --}}
<div id="miniVideoContainer" class="fixed bottom-24 left-8 z-[50] w-[300px] h-[180px] bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 overflow-hidden shadow-2xl group animate-fadeInUp">

    {{-- Botón de Cerrar --}}
    <button onclick="closeMiniVideo(event)" class="absolute top-2 right-2 z-[60] bg-black/50 text-white w-6 h-6 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
        &times;
    </button>

    {{-- Enlace Directo a YouTube con ID del CEO --}}
    <a href="https://www.youtube.com/watch?v=Xsct6_37qW8" target="_blank" class="block w-full h-full relative cursor-pointer">
        <iframe
            id="miniYT"
            class="w-full h-full pointer-events-none"
            src="https://www.youtube.com/embed/Xsct6_37qW8?controls=0&rel=0&autoplay=1&mute=1&loop=1&playlist=Xsct6_37qW8"
            frameborder="0"
            allow="autoplay; encrypted-media">
        </iframe>

        {{-- Overlay de Interacción --}}
        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition-all flex flex-col items-center justify-center">
            <div class="bg-[#d8c495]/90 p-3 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-xl transform scale-75 group-hover:scale-100">
                <svg class="w-6 h-6 text-[#3c3c3c]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4.5 2.688l11.022 6.312a1 1 0 010 1.734L4.5 17.047a1 1 0 01-1.5-.867V3.555a1 1 0 011.5-.867z"/>
                </svg>
            </div>
        </div>
    </a>
</div>

<main class="min-h-screen">
    <div class="w-full min-h-screen p-8 space-y-10 font-[system-ui]" style="background:#7d7d7d !important;">

        {{-- Header --}}
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
            {{-- Botones Superiores --}}
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
                   class="bg-white/90 backdrop-blur-sm rounded-2xl p-6 text-center shadow-lg border-b-4 border-transparent hover:border-[#d8c495] transition-all duration-300 transform hover:-translate-y-2 flex flex-col items-center justify-center group h-32">
                    <span class="font-black text-lg uppercase" style="color: #3c3c3c !important;">{{$opt['label']}}</span>
                    <div class="w-0 group-hover:w-12 h-1 bg-[#d8c495] mt-2 transition-all duration-300"></div>
                </a>
            @endforeach

            {{-- CARRUSEL LOGOS - Restaurado a 3 columnas --}}
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

            {{-- RECUADRO ANUNCIOS - Restaurado a 2 columnas --}}
            <div class="md:col-span-2 h-[500px] bg-white/5 backdrop-blur-sm rounded-2xl border-2 border-dashed border-white/20 flex flex-col relative overflow-hidden group shadow-xl mb-16">
                @if($anuncios->count() > 0)
                    <div id="anuncio-container" class="relative flex-1 flex flex-col h-full">
                        @foreach($anuncios as $index => $anuncio)
                            <div class="anuncio-slide absolute inset-0 p-6 pb-2 flex flex-col items-center transition-all duration-500 opacity-0 {{ $index === 0 ? 'opacity-100 z-10' : 'z-0' }}"
                                 data-titulo="{{ $anuncio->titulo }}"
                                 data-desc="{{ $anuncio->descripcion }}"
                                 data-path="{{ asset('storage/' . $anuncio->adjuncio_ruta) }}"
                                 data-type="{{ str_ends_with($anuncio->adjunto_ruta, '.pdf') ? 'pdf' : 'img' }}">

                                <h2 class="text-lg font-bold text-[#d8c495] uppercase mb-3 text-center tracking-wider">{{ $anuncio->titulo }}</h2>

                                <div class="flex-1 w-full overflow-hidden rounded-lg bg-black/20 relative shadow-inner">
                                    @if(str_ends_with($anuncio->adjunto_ruta, '.pdf'))
                                        <div class="w-full h-full overflow-y-auto bg-white custom-scroll">
                                            <iframe src="{{ asset('storage/' . $anuncio->adjunto_ruta) }}#toolbar=0&navpanes=0&view=FitH"
                                                    class="w-full h-[1200px] border-none">
                                            </iframe>
                                        </div>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <img src="{{ asset('storage/' . $anuncio->adjunto_ruta) }}" class="max-h-full max-w-full object-contain">
                                        </div>
                                    @endif

                                    <div class="absolute top-2 right-2 z-30 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="expandirAnuncio()" class="bg-[#d8c495] text-black p-2 rounded-full shadow-lg hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-300 italic text-center px-4 mt-2 line-clamp-1">{{ $anuncio->descripcion }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if($anuncios->count() > 1)
                        <div class="w-full flex justify-center items-center py-3 bg-black/10">
                            <div class="flex gap-2">
                                @foreach($anuncios as $index => $anuncio)
                                    <div onclick="event.stopPropagation(); jumpToAnuncio({{ $index }});"
                                         class="anuncio-dot w-2 h-2 rounded-full bg-white/30 cursor-pointer transition-all {{ $index === 0 ? 'bg-[#d8c495] w-6' : '' }}"></div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Flechas de navegación lateral --}}
                        <button onclick="event.stopPropagation(); changeAnuncio(-1);" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/60 text-white p-2 rounded-full z-40 opacity-0 group-hover:opacity-100 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M15 19l-7-7 7-7"/></svg></button>
                        <button onclick="event.stopPropagation(); changeAnuncio(1);" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/60 text-white p-2 rounded-full z-40 opacity-0 group-hover:opacity-100 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M9 5l7 7-7 7"/></svg></button>
                    @endif
                @endif
            </div>
        </section>


    </div>
</main>


{{-- MODAL FULLSCREEN --}}
<div id="fullScreenAnuncio" class="fixed inset-0 bg-black/95 z-[999999] hidden flex-col p-6 items-center justify-center">
    <button onclick="cerrarFullScreen()" class="absolute top-6 right-6 text-white text-4xl hover:text-[#d8c495] transition">&times;</button>
    <div id="fs-content" class="w-full h-full flex flex-col items-center">
        <h2 id="fs-titulo" class="text-2xl font-bold text-[#d8c495] mb-4 uppercase"></h2>
        <div id="fs-media" class="flex-1 w-full flex items-center justify-center overflow-hidden"></div>
        <p id="fs-desc" class="text-gray-200 mt-4 max-w-4xl text-center"></p>
    </div>
</div>

{{-- Botón y Modal Chat --}}
<button id="openChatBtn" style="position: fixed; bottom: 24px; right: 24px; background-color: #3c3c3c !important; color: white !important; padding: 16px; border-radius: 9999px; z-index: 99999; cursor: pointer; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.4);">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 28px; height: 28px;">
        <path d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H15.75m6.375 0a9.75 9.75 0 1 1-19.5 0 9.75 9.75 0 0 1 19.5 0Z" />
    </svg>
</button>

<div id="chatModal" style="position: fixed; bottom: 80px; right: 24px; width: 320px; height: 384px; background-color: white !important; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); flex-direction: column; z-index: 99999; display: none; overflow: hidden;">
    <div style="background-color: #3c3c3c !important; color: white !important; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-weight: bold; margin: 0; color: white !important;">Atención al Cliente</h3>
        <button id="closeChatBtn" style="color: white !important; background: none; border: none; cursor: pointer; font-size: 24px;">×</button>
    </div>
    <div id="chatMessages" style="flex: 1; padding: 12px; overflow-y: auto; background-color: #f3f4f6 !important;"></div>
    <div style="padding: 12px; border-top: 1px solid #e5e7eb; background: white !important;">
        <input type="text" id="chatInput" placeholder="Escribe tu mensaje..." style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; background: white !important; color: black !important;">
        <button id="sendChatBtn" style="margin-top: 8px; width: 100%; background-color: #3c3c3c !important; color: white !important; padding: 10px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold;">Enviar</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- LÓGICA MARQUEE ---
        const marquee = document.getElementById('marquee');
        if (marquee && marquee.children.length > 0) {
            const content = marquee.innerHTML;
            marquee.innerHTML = content + content + content;
        }

        // --- LÓGICA CHAT ---
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

            async function fetchMessages() {
                const res = await fetch('{{ route('chat.getMessages') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) displayMessages(await res.json());
            }

            function displayMessages(msgs) {
                messagesDiv.innerHTML = '';
                msgs.forEach(m => {
                    const el = document.createElement('div');
                    const isSender = m.sender_id === {{ Js::from($user->id) }};
                    Object.assign(el.style, {
                        marginBottom: '8px', padding: '8px', borderRadius: '8px', maxWidth: '75%',
                        backgroundColor: isSender ? '#3c3c3c' : '#d1d5db',
                        color: isSender ? 'white' : '#1f2937', marginLeft: isSender ? 'auto' : '0', fontSize: '14px'
                    });
                    el.textContent = m.message;
                    messagesDiv.appendChild(el);
                });
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }

            async function sendMessage() {
                const text = input.value.trim();
                if (!text || input.readOnly) return;
                input.readOnly = true;
                sendBtn.disabled = true;
                try {
                    const res = await fetch('{{ route('chat.sendMessage') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ message: text })
                    });
                    if (res.ok) { input.value = ''; await fetchMessages(); }
                } finally { input.readOnly = false; sendBtn.disabled = false; input.focus(); }
            }

            sendBtn.onclick = (e) => { e.preventDefault(); sendMessage(); };
            input.onkeydown = (e) => { if (e.key === 'Enter') { e.preventDefault(); sendMessage(); } };
        }
    });

    // --- LÓGICA DE ANUNCIOS (CARRUSEL INFINITO CORREGIDO) ---
    let currentAnuncio = 0;
    const slides = document.querySelectorAll('.anuncio-slide');
    const dots = document.querySelectorAll('.anuncio-dot');


    function jumpToAnuncio(index) {
        if (slides.length <= 1) return;

        // 1. Limpiar slide y dot actual
        slides[currentAnuncio].classList.replace('opacity-100', 'opacity-0');
        slides[currentAnuncio].classList.replace('z-10', 'z-0');

        const allDots = document.querySelectorAll('.anuncio-dot');
        if (allDots.length > 0) {
            allDots[currentAnuncio].classList.remove('bg-[#d8c495]', 'w-6');
            allDots[currentAnuncio].classList.add('bg-white/30', 'w-2');
        }

        // 2. Calcular nuevo índice (Ciclo infinito)
        currentAnuncio = (index + slides.length) % slides.length;

        // 3. Activar nuevo slide y dot
        slides[currentAnuncio].classList.replace('opacity-0', 'opacity-100');
        slides[currentAnuncio].classList.replace('z-0', 'z-10');

        if (allDots.length > 0) {
            allDots[currentAnuncio].classList.remove('bg-white/30', 'w-2');
            allDots[currentAnuncio].classList.add('bg-[#d8c495]', 'w-6');
        }

        // 4. Reiniciar el scroll del PDF al cambiar de anuncio
        const activeScroll = slides[currentAnuncio].querySelector('.overflow-y-auto');
        if (activeScroll) activeScroll.scrollTop = 0;
    }

    function changeAnuncio(direction) {
        jumpToAnuncio(currentAnuncio + direction);
    }

    // Auto-play cada 8 segundos
    let autoPlayInterval = setInterval(() => changeAnuncio(1), 8000);

    function expandirAnuncio() {
        const slide = slides[currentAnuncio];
        if (!slide) return;

        const titulo = slide.dataset.titulo;
        const desc = slide.dataset.desc;
        const path = slide.dataset.path;
        const type = slide.dataset.type;

        document.getElementById('fs-titulo').textContent = titulo;
        document.getElementById('fs-desc').textContent = desc;
        const mediaDiv = document.getElementById('fs-media');

        if (type === 'pdf') {
            mediaDiv.innerHTML = `<iframe src="${path}#view=Fit" class="w-full h-full border-none" style="min-height: 80vh;"></iframe>`;
        } else {
            mediaDiv.innerHTML = `<img src="${path}" class="max-h-[80vh] w-auto object-contain shadow-2xl">`;
        }

        document.getElementById('fullScreenAnuncio').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        clearInterval(autoPlayInterval); // Pausar carrusel al ampliar
    }

    function cerrarFullScreen() {
        document.getElementById('fullScreenAnuncio').classList.add('hidden');
        document.getElementById('fs-media').innerHTML = '';
        document.body.style.overflow = 'auto';
        autoPlayInterval = setInterval(() => changeAnuncio(1), 8000); // Reanudar
    }

    function openVideoModal() {
        const videoId = "TU_ID_AQUI"; // Reemplaza con el ID real del video
        const modal = document.getElementById('videoModal');
        const iframe = document.getElementById('main-yt-video');

        // Cargamos el video solo cuando se abre el modal para optimizar recursos
        iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Bloquear scroll de fondo
    }

    function closeVideoModal() {
        const modal = document.getElementById('videoModal');
        const iframe = document.getElementById('main-yt-video');

        modal.style.display = 'none';
        iframe.src = ""; // Detener el video al cerrar
        document.body.style.overflow = 'auto';
    }

    function closeMiniVideo(event) {
        // Evitamos que el clic en cerrar active la redirección del enlace padre
        event.preventDefault();
        event.stopPropagation();

        const container = document.getElementById('miniVideoContainer');
        if (container) {
            container.style.display = 'none';
            // Detenemos el video al cerrar para liberar memoria
            document.getElementById('miniYT').src = "";
        }
    }

    function closeFloatingVideo(event) {
        event.preventDefault();
        event.stopPropagation();
        const videoElem = document.getElementById('miniVideoFloating');
        if (videoElem) {
            videoElem.style.display = 'none';
            // Detenemos el iframe para que no siga consumiendo recursos
            document.getElementById('floatingYT').src = "";
        }
    }

    window.onclick = function(event) {
        const modal = document.getElementById('videoModal');
        if (event.target == modal) {
            closeVideoModal();
        }
    }


</script>
</body>
</html>
