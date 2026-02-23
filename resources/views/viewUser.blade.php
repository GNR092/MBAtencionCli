@extends('layouts.base')

@section('layout-content')
    {{-- Mantenemos tus estilos específicos pero forzamos transparencias --}}
    <style>
        /* PERFORACIÓN PARA VER EL FONDO ANIMADO */
        html, body, #app-layout {
            background: transparent !important;
            background-color: transparent !important;
        }

        /* Scrollbar personalizada */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #d8c495; border-radius: 10px; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeInUp { animation: fadeInUp 0.6s ease-out forwards; }

        /* Marquee Responsivo */
        .marquee-viewport { width: 100%; overflow: hidden; display: flex; align-items: center; }
        .marquee-wrapper { display: flex; gap: 50px; animation: scroll 40s linear infinite; width: max-content; will-change: transform; }
        @keyframes scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        .marquee-viewport:hover .marquee-wrapper { animation-play-state: paused; }

        /* Imagen de logo Adaptable */
        .logo-img {
            height: 100px !important;
            width: auto !important;
            object-fit: contain !important;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
            transition: transform 0.3s ease;
        }
        @media (min-width: 768px) { .logo-img { height: 150px !important; } }
        .logo-img:hover { transform: scale(1.1); }

        .btn-original-style {
            background-color: rgba(255, 255, 255, 0.9) !important; /* Blanco casi opaco */
            border-radius: 1rem;
            transition: all 0.3s ease;
        }

        .btn-original-style:hover {
            transform: translateY(-0.25rem); /* Equivalente a hover:-translate-y-1 */
            background-color: rgba(255, 255, 255, 1) !important;
        }
    </style>

    {{-- MINI VIDEO (Izquierda) --}}
    <div id="miniVideoContainer" class="fixed bottom-6 left-6 md:bottom-24 md:left-8 z-[50] w-[200px] h-[120px] md:w-[300px] md:h-[180px] bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 overflow-hidden shadow-2xl group animate-fadeInUp">
        <button onclick="closeMiniVideo(event)" class="absolute top-2 right-2 z-[60] bg-black/50 text-white w-6 h-6 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">&times;</button>
        <a href="https://www.youtube.com/watch?v=Xsct6_37qW8" target="_blank" class="block w-full h-full relative cursor-pointer">
            <iframe id="miniYT" class="w-full h-full pointer-events-none" src="https://www.youtube.com/embed/Xsct6_37qW8?controls=0&rel=0&autoplay=1&mute=1&loop=1&playlist=Xsct6_37qW8" frameborder="0" allow="autoplay; encrypted-media"></iframe>
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition-all flex flex-col items-center justify-center">
                <div class="bg-[#d8c495]/90 p-2 md:p-3 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-xl transform scale-75 group-hover:scale-100">
                    <svg class="w-6 h-6 text-[#3c3c3c]" fill="currentColor" viewBox="0 0 20 20"><path d="M4.5 2.688l11.022 6.312a1 1 0 010 1.734L4.5 17.047a1 1 0 01-1.5-.867V3.555a1 1 0 011.5-.867z"/></svg>
                </div>
            </div>
        </a>
    </div>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="min-h-screen relative z-10 bg-transparent">
        <div class="w-full min-h-screen p-4 md:p-8 space-y-6 md:space-y-10 font-[system-ui] bg-transparent">

            {{-- Header traslúcido --}}
            <header class="flex flex-col md:flex-row items-center justify-between w-full animate-fadeInUp py-4 px-6 gap-4 rounded-2xl shadow-2xl" style="background-color: rgba(60, 60, 60, 0.8) !important; backdrop-filter: blur(10px);">
                <div class="flex items-center gap-4">
                    <div class="relative group cursor-pointer" title="Cambiar foto">
                        @php $avatarSize = "w-12 h-12 md:w-20 md:h-20"; @endphp
                        @if($user->foto)
                            <img id="avatar-preview" src="{{ asset('storage/' . $user->foto) . '?v=' . $user->updated_at->timestamp }}" class="{{ $avatarSize }} rounded-full object-cover border-2 border-[#d8c495]">
                        @else
                            <div id="avatar-initials" class="{{ $avatarSize }} flex items-center justify-center rounded-full bg-[#112134] text-white font-bold text-xl md:text-2xl border-2 border-[#d8c495]">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <label for="foto-input" class="absolute inset-0 flex items-center justify-center rounded-full bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </label>
                        <form id="foto-form" action="{{ route('perfil.foto') }}" method="POST" enctype="multipart/form-data" class="hidden">
                            @csrf @method('PUT')
                            <input id="foto-input" type="file" name="foto" accept="image/jpg,image/jpeg,image/png" class="hidden">
                        </form>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-lg md:text-2xl font-bold text-white tracking-tight">Bienvenido, <span style="color: #d8c495 !important;">{{ $user->name ?? 'Usuario' }}</span></h1>
                        <p class="text-xs md:text-sm text-gray-300 italic">Panel de Cliente</p>
                    </div>
                </div>
                <form method="get" action="{{ route('logout') }}" class="m-0">
                    <button type="submit" class="w-full md:w-auto px-4 py-2 text-sm border border-[#d8c495] text-[#d8c495] rounded-xl hover:bg-[#d8c495] hover:text-[#3c3c3c] transition-all font-bold">Cerrar sesión</button>
                </form>
            </header>

            {{-- Grid de botones transparentes --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
                @php
                    $opciones = [
                        ['route' => 'user.facturacion', 'label' => 'Facturación'],
                        ['route' => 'notificaciones.index', 'label' => 'Notificaciones'],
                        ['route' => 'cuentas-cobrar.index', 'label' => 'Cuentas Cobrar'],
                        ['route' => 'estados-cuenta.index', 'label' => 'Estados Cuenta'],
                        ['route' => 'contratos.index', 'label' => 'Contratos'],
                    ];
                @endphp

                @foreach($opciones as $opt)
                    <a href="{{ route($opt['route']) }}"
                       class="bg-white/90 rounded-2xl p-4 md:p-6 text-center shadow-lg border-b-4 border-transparent hover:border-[#d8c495] transition-all transform hover:-translate-y-1 flex flex-col items-center justify-center h-24 md:h-32">
                        <span class="font-black text-sm md:text-lg uppercase text-[#3c3c3c]">{{$opt['label']}}</span>
                    </a>
                @endforeach

                {{-- CARRUSEL LOGOS --}}
                <div class="col-span-1 sm:col-span-2 lg:col-span-3 h-[300px] md:h-[480px] flex flex-col justify-center overflow-hidden">
                    <div class="marquee-viewport">
                        <div class="marquee-wrapper" id="marquee">
                            @foreach(\App\Models\Logo::where('activo', true)->get() as $logo)
                                <div class="flex-none px-6 md:px-12">
                                    <img src="{{ asset('storage/' . $logo->imagen_ruta) }}" class="logo-img">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- RECUADRO ANUNCIOS --}}
                <div class="col-span-1 sm:col-span-2 lg:col-span-2 h-[450px] md:h-[500px] bg-white/5 backdrop-blur-sm rounded-2xl border-2 border-dashed border-white/20 flex flex-col relative overflow-hidden group shadow-xl">
                    @if($anuncios->count() > 0)
                        <div id="anuncio-container" class="relative flex-1 flex flex-col h-full">
                            @foreach($anuncios as $index => $anuncio)
                                <div class="anuncio-slide absolute inset-0 p-4 md:p-6 flex flex-col items-center transition-all duration-500 opacity-0 {{ $index === 0 ? 'opacity-100 z-10' : 'z-0' }}"
                                     data-titulo="{{ $anuncio->titulo }}" data-desc="{{ $anuncio->descripcion }}" data-path="{{ asset('storage/' . $anuncio->adjunto_ruta) }}" data-type="{{ str_ends_with($anuncio->adjunto_ruta, '.pdf') ? 'pdf' : 'img' }}">
                                    <h2 class="text-base md:text-lg font-bold text-[#d8c495] uppercase mb-2 md:mb-3 text-center tracking-wider">{{ $anuncio->titulo }}</h2>
                                    <div class="flex-1 w-full overflow-hidden rounded-lg bg-black/20 relative shadow-inner">
                                        @if(str_ends_with($anuncio->adjunto_ruta, '.pdf'))
                                            <div class="w-full h-full overflow-y-auto bg-white custom-scroll">
                                                <iframe src="{{ asset('storage/' . $anuncio->adjunto_ruta) }}#toolbar=0&navpanes=0&view=FitH" class="w-full h-[800px] md:h-[1200px] border-none"></iframe>
                                            </div>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <img src="{{ asset('storage/' . $anuncio->adjunto_ruta) }}" class="max-h-full max-w-full object-contain">
                                            </div>
                                        @endif
                                        <div class="absolute top-2 right-2 z-30 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onclick="expandirAnuncio()" class="bg-[#d8c495] text-black p-2 rounded-full shadow-lg hover:scale-110 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/></svg></button>
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
                                        <div onclick="event.stopPropagation(); jumpToAnuncio({{ $index }});" class="anuncio-dot w-2 h-2 rounded-full bg-white/30 cursor-pointer transition-all {{ $index === 0 ? 'bg-[#d8c495] w-6' : '' }}"></div>
                                    @endforeach
                                </div>
                            </div>
                            <button onclick="event.stopPropagation(); changeAnuncio(-1);" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/60 text-white p-2 rounded-full z-40 opacity-0 group-hover:opacity-100 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M15 19l-7-7 7-7"/></svg></button>
                            <button onclick="event.stopPropagation(); changeAnuncio(1);" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/60 text-white p-2 rounded-full z-40 opacity-0 group-hover:opacity-100 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M9 5l7 7-7 7"/></svg></button>
                        @endif
                    @endif
                </div>
            </section>
        </div>
    </main>

    {{-- MODAL FULLSCREEN ANUNCIO --}}
    <div id="fullScreenAnuncio" class="fixed inset-0 bg-black/95 z-[999999] hidden flex-col p-4 md:p-6 items-center justify-center">
        <button onclick="cerrarFullScreen()" class="absolute top-4 right-4 md:top-6 md:right-6 text-white text-3xl md:text-4xl hover:text-[#d8c495] transition">&times;</button>
        <div id="fs-content" class="w-full h-full flex flex-col items-center">
            <h2 id="fs-titulo" class="text-xl md:text-2xl font-bold text-[#d8c495] mb-4 uppercase text-center"></h2>
            <div id="fs-media" class="flex-1 w-full flex items-center justify-center overflow-hidden"></div>
            <p id="fs-desc" class="text-gray-200 mt-4 max-w-4xl text-center text-sm"></p>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODULO CHAT REINTEGRADO Y CORREGIDO (FIXED Z-INDEX)        --}}
    {{-- ========================================================== --}}

    <button id="openChatBtn" class="fixed bottom-6 right-6 z-[9999] cursor-pointer bg-[#d8c495] text-[#3c3c3c] w-14 h-14 rounded-full shadow-2xl hover:scale-110 transition-transform hover:bg-white flex items-center justify-center animate-fadeInUp">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>

    <div id="chatModal" class="fixed inset-0 z-[10000] hidden items-end sm:items-center justify-center bg-black/50 backdrop-blur-[2px]">
        <div class="bg-white w-full sm:w-[400px] h-[80vh] sm:h-[600px] sm:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fadeInUp">
            <div class="bg-[#3c3c3c] p-4 flex justify-between items-center shadow-md">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <h3 class="text-[#d8c495] font-bold text-lg">Soporte en línea</h3>
                </div>
                <button id="closeChatBtn" class="text-gray-400 hover:text-white transition-colors text-2xl leading-none">&times;</button>
            </div>

            <div id="chatMessages" class="flex-1 p-4 overflow-y-auto bg-gray-50 custom-scroll flex flex-col gap-2">
                <div class="text-center text-gray-400 text-sm mt-4">Iniciando conversación...</div>
            </div>

            <div class="p-3 bg-white border-t border-gray-200">
                <div class="flex gap-2">
                    <input type="text" id="chatInput"
                           class="flex-1 bg-gray-100 border-0 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#d8c495] focus:outline-none text-gray-700"
                           placeholder="Escribe tu mensaje..." autocomplete="off">
                    <button id="sendChatBtn" class="bg-[#d8c495] text-[#3c3c3c] p-3 rounded-xl hover:bg-[#c5b386] transition-colors shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const fotoInput = document.getElementById('foto-input');
                if (fotoInput) {
                    fotoInput.addEventListener('change', function () {
                        if (!this.files.length) return;
                        const file = this.files[0];
                        if (!['image/jpeg','image/jpg','image/png'].includes(file.type)) {
                            alert('Solo se permiten imágenes JPG o PNG.');
                            return;
                        }
                        if (file.size > 2 * 1024 * 1024) {
                            alert('La imagen no debe superar 2 MB.');
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = e => {
                            const prev = document.getElementById('avatar-preview');
                            const init = document.getElementById('avatar-initials');
                            if (prev) prev.src = e.target.result;
                            if (init) {
                                const img = document.createElement('img');
                                img.id = 'avatar-preview';
                                img.src = e.target.result;
                                img.className = init.className.replace('flex items-center justify-center', '') + ' object-cover';
                                init.replaceWith(img);
                            }
                        };
                        reader.readAsDataURL(file);
                        document.getElementById('foto-form').submit();
                    });
                }

                const marquee = document.getElementById('marquee');
                if (marquee && marquee.children.length > 0) {
                    const content = marquee.innerHTML;
                    marquee.innerHTML = content + content + content;
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                
                const openBtn = document.getElementById('openChatBtn');
                const closeBtn = document.getElementById('closeChatBtn');
                const modal = document.getElementById('chatModal');
                const messagesDiv = document.getElementById('chatMessages');
                const input = document.getElementById('chatInput');
                const sendBtn = document.getElementById('sendChatBtn');
                
                let lastId = 0;
                let pollingInterval = null;
                let isTabActive = true;
                let isFetching = false;
                const renderedMessageIds = new Set();

                
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

                
                document.addEventListener('visibilitychange', () => {
                    isTabActive = !document.hidden;
                    if (!isTabActive) stopPolling();
                    else if (modal && !modal.classList.contains('hidden')) startPolling();
                });

                if (openBtn && modal) {
                    openBtn.onclick = (e) => {
                        e.preventDefault();
                        modal.classList.remove('hidden');
                        modal.style.display = 'flex';
                        
                        if (lastId === 0) {
                            messagesDiv.innerHTML = '<div class="text-center text-gray-400 text-sm mt-4">Cargando mensajes...</div>';
                            renderedMessageIds.clear();
                        }
                        
                        fetchMessages();
                        startPolling();
                        setTimeout(() => input.focus(), 100);
                    };

                    closeBtn.onclick = () => {
                        modal.classList.add('hidden');
                        modal.style.display = 'none';
                        stopPolling();
                    };

                    function startPolling() {
                        stopPolling();
                        if (isTabActive) {
                            pollingInterval = setInterval(fetchMessages, 3000);
                        }
                    }

                    function stopPolling() {
                        if (pollingInterval) {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                        }
                    }

                    async function fetchMessages() {
                        if (isFetching) return;
                        isFetching = true;
                        try {
                            const url = `{{ route('chat.getMessages') }}?last_id=${lastId}`;
                            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

                            if (res.status === 401 || res.status === 419) {
                                stopPolling();
                                window.location.href = '/inicio-de-sesion';
                                return;
                            }
                            
                            if (res.ok) {
                                const msgs = await res.json();
                                if (msgs.length > 0) {
                                    if (lastId === 0) messagesDiv.innerHTML = '';
                                    displayMessages(msgs);
                                    lastId = msgs[msgs.length - 1].id;
                                } else if (lastId === 0) {
                                    messagesDiv.innerHTML = '<div class="text-center text-gray-400 text-sm mt-4">No hay mensajes previos.</div>';
                                }
                            }
                        } catch (error) {
                            console.error('Error fetching messages:', error);
                        } finally {
                            isFetching = false;
                        }
                    }

                    function displayMessages(msgs) {
                        msgs.forEach(m => {
                            if (renderedMessageIds.has(m.id)) return;
                            renderedMessageIds.add(m.id);

                            const el = document.createElement('div');
                            const isSender = m.sender_id === {{ Js::from($user->id) }};
                            Object.assign(el.style, {
                                marginBottom: '8px', padding: '10px', borderRadius: '12px', maxWidth: '75%',
                                backgroundColor: isSender ? '#3c3c3c' : '#e5e7eb',
                                color: isSender ? '#d8c495' : '#1f2937',
                                marginLeft: isSender ? 'auto' : '0', fontSize: '14px',
                                borderTopRightRadius: isSender ? '2px' : '12px',
                                borderTopLeftRadius: isSender ? '12px' : '2px',
                                boxShadow: '0 1px 2px rgba(0,0,0,0.1)'
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

                            if (res.status === 401 || res.status === 419) {
                                stopPolling();
                                window.location.href = '/inicio-de-sesion';
                                return;
                            }

                            if (res.ok) {
                                input.value = '';
                                await fetchMessages();
                                startPolling();
                            }
                        } catch(e) {
                            console.error("Error sending", e);
                        } finally {
                            input.readOnly = false; sendBtn.disabled = false; input.focus();
                        }
                    }

                    sendBtn.onclick = (e) => { e.preventDefault(); sendMessage(); };
                    input.onkeydown = (e) => { if (e.key === 'Enter') { e.preventDefault(); sendMessage(); } };
                }
            });

            
            let currentAnuncio = 0;
            const slides = document.querySelectorAll('.anuncio-slide');
            const dots = document.querySelectorAll('.anuncio-dot');

            function jumpToAnuncio(index) {
                if (slides.length <= 1) return;

                
                slides[currentAnuncio].classList.replace('opacity-100', 'opacity-0');
                slides[currentAnuncio].classList.replace('z-10', 'z-0');

                const allDots = document.querySelectorAll('.anuncio-dot');
                if (allDots.length > 0) {
                    allDots[currentAnuncio].classList.remove('bg-[#d8c495]', 'w-6');
                    allDots[currentAnuncio].classList.add('bg-white/30', 'w-2');
                }

                
                currentAnuncio = (index + slides.length) % slides.length;

                
                slides[currentAnuncio].classList.replace('opacity-0', 'opacity-100');
                slides[currentAnuncio].classList.replace('z-0', 'z-10');

                if (allDots.length > 0) {
                    allDots[currentAnuncio].classList.remove('bg-white/30', 'w-2');
                    allDots[currentAnuncio].classList.add('bg-[#d8c495]', 'w-6');
                }

                
                const activeScroll = slides[currentAnuncio].querySelector('.overflow-y-auto');
                if (activeScroll) activeScroll.scrollTop = 0;
            }

            function changeAnuncio(direction) {
                jumpToAnuncio(currentAnuncio + direction);
            }

            
            let autoPlayInterval = setInterval(() => changeAnuncio(1), 8000);

            function expandirAnuncio() {
                const slide = slides[currentAnuncio];
                if (!slide || !slide.dataset.path || slide.dataset.path.endsWith('/storage/')) {
                    console.error("Error: La ruta del archivo está vacía o es inválida.");
                    return;
                }

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
                clearInterval(autoPlayInterval); 
            }

            function cerrarFullScreen() {
                document.getElementById('fullScreenAnuncio').classList.add('hidden');
                document.getElementById('fs-media').innerHTML = '';
                document.body.style.overflow = 'auto';
                autoPlayInterval = setInterval(() => changeAnuncio(1), 8000); 
            }

            function closeMiniVideo(event) {
                event.preventDefault();
                event.stopPropagation();
                const container = document.getElementById('miniVideoContainer');
                if (container) {
                    container.style.display = 'none';
                    document.getElementById('miniYT').src = "";
                }
            }
        </script>
    @endpush
@endsection
