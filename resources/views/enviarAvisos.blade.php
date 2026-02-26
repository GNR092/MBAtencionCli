@extends('layouts.admin')

@section('content')
    <div class="w-full p-4 md:p-6 animate-fadeInUp">
        <div class="max-w-6xl mx-auto">
            <header class="mb-10 px-2">
                <div class="flex items-baseline gap-4">
                    <span class="text-dorado-400 text-sm font-serif italic">|</span>
                    <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                        Avisos
                    </h1>
                </div>
            </header>

    @if(session('success'))
    <div class="bg-green-800 text-white p-4 mb-6 rounded">
        {{ session('success') }}
    </div>
    @endif
    <!-- Formulario -->
    <div class="max-w-6xl mx-auto">
        <div class="w-full relative bg-white rounded-2xl shadow-xl border border-carbon-200 overflow-hidden pb-2">

        <div class="bg-carbon-900 px-6 py-4 border-b-2 border-dorado">
            <h2 class="text-dorado-400 text-lg font-bold uppercase tracking-widest flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                    </path>
                </svg>
                Redactar Nuevo Aviso
            </h2>
        </div>

    <form action="{{ route('avisos.store') }}" method="POST" class="p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Buscar usuario con autocompletado --}}
            <div x-data="usuarioSearch()" class="relative">
                <label class="block text-xs font-bold text-carbon-900 uppercase mb-1">Buscar usuario</label>
                <input type="hidden" name="usuario" :value="selectedId ?? selectedText">
                <div class="relative">
                    <input type="text"
                        x-model="query"
                        @input.debounce.300ms="buscar"
                        @keydown.escape="cerrar"
                        @keydown.arrow-down.prevent="moverAbajo"
                        @keydown.arrow-up.prevent="moverArriba"
                        @keydown.enter.prevent="seleccionar(activo)"
                        @blur="cerrarConDelay"
                        autocomplete="off"
                        class="block w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors"
                        placeholder="Nombre o correo...">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Dropdown de resultados --}}
                <ul x-show="abierto && resultados.length > 0"
                    x-transition
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden max-h-52 overflow-y-auto custom-scroll"
                    style="display:none">
                    <template x-for="(u, i) in resultados" :key="u.id">
                        <li @mousedown.prevent="seleccionar(i)"
                            :class="i === activo ? 'bg-dorado/10 text-carbon-900' : 'text-carbon-900 hover:bg-gray-50'"
                            class="px-4 py-2 cursor-pointer text-sm flex flex-col">
                            <span class="font-medium" x-text="u.name"></span>
                            <span class="text-xs text-gray-400" x-text="u.email"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <div>
                <label class="block text-xs font-bold text-carbon-900 uppercase mb-1">Filtrar por Proyecto</label>
                <select name="proyect[]" id="proyect"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 bg-white focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors custom-scroll">
                    <option value="" disabled selected>Selecciona un proyecto</option>
                    @foreach($proyectos as $proyecto)
                    <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre_proyecto }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-2 bg-yellow-50 p-3 rounded-lg border border-dorado/20">
            <input type="checkbox" name="todos" value="1" id="checkTodos"
                class="w-4 h-4 text-dorado-400 bg-white border-gray-300 rounded focus:ring-dorado">
            <label for="checkTodos" class="text-sm font-bold text-yellow-800 cursor-pointer select-none">
                Enviar este aviso a todos los usuarios (Omite filtro de proyecto)
            </label>
        </div>

        <div>
            <label class="block text-xs font-bold text-carbon-900 uppercase mb-1">Asunto</label>
            <input type="text" name="asunto"
                class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors"
                placeholder="Ej: Mantenimiento de elevadores">
        </div>

        <div>
            <label class="block text-xs font-bold text-carbon-900 uppercase mb-1">Mensaje</label>
            <textarea name="mensaje" rows="4"
                class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors custom-scroll"
                placeholder="Escribe el contenido del aviso aquí..."></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-xs font-bold text-carbon-900 uppercase mb-1">Prioridad</label>
                <select name="prioridad"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 bg-white focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors">
                    <option value="alta">Alta</option>
                    <option value="media" selected>Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-carbon-900 uppercase mb-2">Canales de envío</label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="canales[]" value="interno"
                            class="w-4 h-4 text-dorado-400 border-gray-300 rounded focus:ring-dorado">
                        <span class="ml-2 text-sm text-carbon-900">Interno</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="canales[]" value="correo"
                            class="w-4 h-4 text-dorado-400 border-gray-300 rounded focus:ring-dorado">
                        <span class="ml-2 text-sm text-carbon-900">Email</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="canales[]" value="whatsapp"
                            class="w-4 h-4 text-dorado-400 border-gray-300 rounded focus:ring-dorado">
                        <span class="ml-2 text-sm text-carbon-900">WhatsApp</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <button type="submit"
                class="w-full md:w-auto md:px-12 float-right bg-dorado-400 text-white font-bold uppercase tracking-widest py-3 rounded-lg shadow-md hover:bg-dorado/90 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                Enviar Aviso
            </button>
        </div>
    </form>
    </div>
</div>
<script>
function usuarioSearch() {
    return {
        query: '',
        selectedId: null,
        selectedText: '',
        resultados: [],
        abierto: false,
        activo: -1,
        async buscar() {
            this.selectedId = null;
            if (this.query.length < 2) {
                this.resultados = [];
                this.abierto = false;
                return;
            }
            const res = await fetch(`/api/usuarios/buscar?q=${encodeURIComponent(this.query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            this.resultados = await res.json();
            this.abierto = this.resultados.length > 0;
            this.activo = -1;
        },

        seleccionar(i) {
            if (i < 0 || i >= this.resultados.length) return;
            const u = this.resultados[i];
            this.selectedId = u.id;
            this.selectedText = u.name;
            this.query = u.name;
            this.abierto = false;
        },

        moverAbajo() {
            if (this.activo < this.resultados.length - 1) this.activo++;
        },

        moverArriba() {
            if (this.activo > 0) this.activo--;
        },

        cerrar() {
            this.abierto = false;
        },

        cerrarConDelay() {
            setTimeout(() => { this.abierto = false; }, 150);
        }
    };
}
</script>
@endsection
