@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <header class="mb-10 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-[#D4A017] text-sm font-serif italic">|</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                    Carrusel de logos<span class="font-light text-[#D4A017]"></span><span class="text-[#D4A017] animate-pulse">_</span>
                </h1>
            </div>
        </header>

        <div class="bg-white rounded-lg shadow-md p-8 mb-8 border-t-4 border-[#d8c495]">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Cargar Nuevo Logo</h2>

            <form action="{{ route('admin.logos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Empresa</label>
                        <input type="text" name="nombre" required
                               style="color: #000000 !important;"
                               class="w-full px-4 py-2 rounded-lg border border-gray-400 bg-[#eee] focus:ring-2 focus:ring-[#d8c495] outline-none font-medium">
                    </div>

                    {{-- URL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL de Redirección (Opcional)</label>
                        <input type="url" name="url_redireccion" placeholder="https://ejemplo.com"
                               style="color: #000000 !important;"
                               class="w-full px-4 py-2 rounded-lg border border-gray-400 bg-[#eee] focus:ring-2 focus:ring-[#d8c495] outline-none font-medium">
                    </div>

                    {{-- Archivo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Archivo del Logo (Solo PNG o SVG)</label>
                        <input type="file" name="logo" required
                               accept=".png, .svg"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2f2f2f] file:text-white hover:file:bg-black cursor-pointer">
                        <p class="text-xs text-gray-400 mt-1 italic">Formatos recomendados: .svg para máxima nitidez.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black font-bold px-8 py-3 rounded-lg transition shadow-md uppercase tracking-wider">
                        Guardar Logo en Sistema
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="w-full text-sm text-center border-collapse">
                <thead class="bg-[#2f2f2f] text-[#fff] uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-b">Vista Previa</th>
                    <th class="px-6 py-4 border-b">Nombre Empresa</th>
                    <th class="px-6 py-4 border-b">Enlace</th>
                    <th class="px-6 py-4 border-b">Estado</th>
                    <th class="px-6 py-4 border-b">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[#eee]">
                @forelse($logos as $logo)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <img src="{{ asset('storage/' . $logo->imagen_ruta) }}" alt="Logo" class="h-12 mx-auto object-contain bg-gray-100 p-1 rounded">
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-700">{{ $logo->nombre }}</td>
                        <td class="px-6 py-4 text-blue-600 truncate max-w-[150px]">
                            <a href="{{ $logo->url_redireccion }}" target="_blank">{{ $logo->url_redireccion ?? 'N/A' }}</a>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.logos.toggle', $logo->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded-full text-xs font-bold transition {{ $logo->activo ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
                                    {{ $logo->activo ? '● ACTIVO' : '○ INACTIVO' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.logos.destroy', $logo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este logo permanentemente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mx-auto">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-gray-500 italic text-lg">No hay logos cargados en el carrusel actualmente.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
