@extends('layouts.admin')

@section('content')
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-[#D4A017] text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                Emitir anuncios<span class="font-light text-[#D4A017]"></span><span class="text-[#D4A017] animate-pulse">_</span>
            </h1>
        </div>
    </header>

        @if(session('success'))
        <div class="bg-green-800 text-white p-4 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif
        <!-- Formulario -->

    <div class="w-full relative bg-white rounded-2xl shadow-xl border border-carbon overflow-hidden pb-2">

        <div class="bg-gris-carbon px-6 py-4 border-b-2 border-dorado">
            <h2 class="text-dorado text-lg font-bold uppercase tracking-widest flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Redactar Nuevo Aviso
            </h2>
        </div>

        <form action="{{ route('avisos.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-xs font-bold text-gris-carbon uppercase mb-1">Buscar usuario</label>
                    <div class="relative">
                        <input type="text" name="usuario"
                               class="block w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                               placeholder="Nombre, correo o ID">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gris-carbon uppercase mb-1">Filtrar por Proyecto</label>
                    <select name="proyect[]" id="proyect"
                            class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon bg-white focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors custom-scroll">
                        <option value="" disabled selected>Selecciona un proyecto</option>
                        <option value="RESIDENT 1">RESIDENT 1</option>
                        <option value="RESIDENT 2">RESIDENT 2</option>
                        <option value="CAMPUS RECIDENCIA">CAMPUS RECIDENCIA</option>
                        <option value="TMZN 122">TMZN 122</option>
                        <option value="GRAND TEMOZON">GRAND TEMOZÓN</option>
                        <option value="MB RESORT MERIDA">MB RESORT MÉRIDA</option>
                        <option value="Princess Village">Princess Village</option>
                        <option value="Royal Square Plaza">Royal Square Plaza</option>
                        <option value="RUM">RUM</option>
                        <option value="Avenue Temozon">Avenue Temozón</option>
                        <option value="MB Resort Orlando">MB Resort Orlando</option>
                        <option value="MB Wellness Resort">MB Wellness Resort</option>
                        <option value="Aldea Borboleta I">Aldea Borboleta I</option>
                        <option value="Aldea Borboleta II">Aldea Borboleta II </option>
                        <option value="Aldea Borboleta III">Aldea Borboleta III</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-yellow-50 p-3 rounded-lg border border-dorado/20">
                <input type="checkbox" name="todos" value="1" id="checkTodos"
                       class="w-4 h-4 text-dorado bg-white border-gray-300 rounded focus:ring-dorado">
                <label for="checkTodos" class="text-sm font-bold text-yellow-800 cursor-pointer select-none">
                    Enviar este aviso a todos los usuarios (Omite filtro de proyecto)
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-gris-carbon uppercase mb-1">Asunto</label>
                <input type="text" name="asunto"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                       placeholder="Ej: Mantenimiento de elevadores">
            </div>

            <div>
                <label class="block text-xs font-bold text-gris-carbon uppercase mb-1">Mensaje</label>
                <textarea name="mensaje" rows="4"
                          class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors custom-scroll"
                          placeholder="Escribe el contenido del aviso aquí..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-xs font-bold text-gris-carbon uppercase mb-1">Prioridad</label>
                    <select name="prioridad"
                            class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon bg-white focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors">
                        <option value="alta">Alta</option>
                        <option value="media" selected>Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gris-carbon uppercase mb-2">Canales de envío</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="canales[]" value="interno" class="w-4 h-4 text-dorado border-gray-300 rounded focus:ring-dorado">
                            <span class="ml-2 text-sm text-gris-carbon">Interno</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="canales[]" value="correo" class="w-4 h-4 text-dorado border-gray-300 rounded focus:ring-dorado">
                            <span class="ml-2 text-sm text-gris-carbon">Email</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="canales[]" value="whatsapp" class="w-4 h-4 text-dorado border-gray-300 rounded focus:ring-dorado">
                            <span class="ml-2 text-sm text-gris-carbon">WhatsApp</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <button type="submit"
                        class="w-full md:w-auto md:px-12 float-right bg-dorado text-white font-bold uppercase tracking-widest py-3 rounded-lg shadow-md hover:bg-dorado/90 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                    Enviar Aviso
                </button>
            </div>
        </form>
    </div>
@endsection
