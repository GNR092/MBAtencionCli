@extends('layouts.admin')

@section('content')
    <div class="w-full p-4 md:p-6 animate-fadeInUp">
        <div class="max-w-full mx-auto">
            <header class="mb-10 px-2">
                <div class="flex items-baseline gap-4">
                    <span class="text-dorado-400 text-sm font-serif italic">|</span>
                    <h1 class="page-title">
                        Emitir anuncios
                    </h1>
                </div>
            </header>

    <div class="max-w-full mx-auto p-6">
        <div class="flex justify-end mb-4">
            <button onclick="document.getElementById('modalCrear').classList.remove('hidden')"
                class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-6 py-2 rounded font-bold shadow-md">+ NUEVO
                ANUNCIO</button>
        </div>

    <div class="tabla-dorada-container">
        <div class="overflow-x-auto custom-scroll">
            <table class="tabla-dorada">
                <thead>
                    <tr>
                        <th class="text-left pl-6">Título</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach($anuncios as $anuncio)
                    <tr>
                        <td class="text-left pl-6 font-bold">{{ $anuncio->titulo }}</td>

                        <td>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-sm
                            {{ $anuncio->prioridad == 'alta'
                                ? 'bg-red-100 text-red-700 border-red-200'
                                : 'bg-blue-100 text-blue-700 border-blue-200' }}">
                                {{ $anuncio->prioridad }}
                            </span>
                        </td>

                        <td>
                            <form action="{{ route('admin.anuncios.toggle', $anuncio->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition border shadow-sm
                                {{ $anuncio->estado == 'activo'
                                    ? 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200'
                                    : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                    {{ $anuncio->estado }}
                                </button>
                            </form>
                        </td>

                        <td>
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editAnuncio('{{ json_encode($anuncio) }}')"
                                    class="inline-flex items-center justify-center bg-dorado-400 hover:bg-dorado/80 text-white w-8 h-8 rounded-lg transition shadow-sm border border-dorado/50"
                                    title="Editar Anuncio">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>

                                <form action="{{ route('admin.anuncios.destroy', $anuncio->id) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar este anuncio permanentemente?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white w-8 h-8 rounded-lg transition shadow-sm border border-red-600/50"
                                        title="Eliminar Anuncio">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalCrear"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 flex items-center justify-center hidden z-50">

    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden relative">

        <div class="px-6 py-4 border-b-2 border-[#d8c495]/40 flex justify-between items-center">
            <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">
                Nuevo Anuncio
            </h2>
            <button type="button" onclick="document.getElementById('modalCrear').classList.add('hidden')"
                class="text-gray-400 hover:text-white transition transform hover:rotate-90 duration-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.anuncios.store') }}" method="POST" enctype="multipart/form-data"
            class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Título</label>
                <input type="text" name="titulo" placeholder="Ej: Mantenimiento programado" required
                    class="block w-full border border-[#d8c495]/30 rounded-lg px-3 py-2.5 text-white bg-[#0d1f30] focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495] transition-colors placeholder-gray-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Descripción</label>
                <textarea name="descripcion" rows="3" placeholder="Detalles del anuncio..."
                    class="block w-full border border-[#d8c495]/30 rounded-lg px-3 py-2.5 text-white bg-[#0d1f30] focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495] transition-colors resize-none placeholder-gray-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Prioridad</label>
                <select name="prioridad"
                    class="block w-full border border-[#d8c495]/30 rounded-lg px-3 py-2.5 text-white bg-[#0d1f30] focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495] transition-colors cursor-pointer">
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Adjunto (Opcional)</label>
                <input type="file" name="adjunto" class="block w-full text-xs text-gray-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-lg file:border-0
                    file:text-xs file:font-bold file:uppercase
                    file:bg-[#d8c495] file:text-[#0d1f30]
                    hover:file:bg-[#c4b385] cursor-pointer file:transition-colors">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-[#d8c495]/20">
                <button type="button" onclick="document.getElementById('modalCrear').classList.add('hidden')"
                    class="px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider text-white bg-white/10 hover:bg-white/20 transition border border-white/20">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider text-[#0d1f30] bg-[#d8c495] hover:bg-[#c4b385] transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditar"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">

    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden relative">

        <div class="px-6 py-4 border-b-2 border-[#d8c495]/40 flex justify-between items-center">
            <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">
                Editar Anuncio
            </h2>
            <button type="button" onclick="document.getElementById('modalEditar').classList.add('hidden')"
                class="text-gray-400 hover:text-white transition focus:outline-none transform hover:rotate-90 duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="formEditar" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="edit_titulo" class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Título</label>
                <input type="text" name="titulo" id="edit_titulo" required
                    class="block w-full border border-[#d8c495]/30 rounded-lg px-3 py-2.5 text-white bg-[#0d1f30] focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495] transition-colors">
            </div>

            <div>
                <label for="edit_descripcion"
                    class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Descripción</label>
                <textarea name="descripcion" id="edit_descripcion" rows="3"
                    class="block w-full border border-[#d8c495]/30 rounded-lg px-3 py-2.5 text-white bg-[#0d1f30] focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495] transition-colors resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_prioridad"
                        class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Prioridad</label>
                    <select name="prioridad" id="edit_prioridad"
                        class="block w-full border border-[#d8c495]/30 rounded-lg px-3 py-2.5 text-white bg-[#0d1f30] focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495] transition-colors cursor-pointer">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
                <div>
                    <label for="edit_estado"
                        class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-1">Estado</label>
                    <select name="estado" id="edit_estado"
                        class="block w-full border border-[#d8c495]/30 rounded-lg px-3 py-2.5 text-white bg-[#0d1f30] focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495] transition-colors cursor-pointer">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#d8c495]/70 uppercase mb-2">Adjunto</label>
                <input type="file" name="adjunto" class="block w-full text-xs text-gray-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-lg file:border-0
                    file:text-xs file:font-bold file:uppercase
                    file:bg-[#d8c495] file:text-[#0d1f30]
                    hover:file:bg-[#c4b385] file:cursor-pointer cursor-pointer
                    file:transition-colors">
            </div>

            <div class="pt-4 mt-2 border-t border-[#d8c495]/20 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalEditar').classList.add('hidden')"
                    class="px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider text-white bg-white/10 hover:bg-white/20 transition border border-white/20">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider text-[#0d1f30] bg-[#d8c495] hover:bg-[#c4b385] transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {

    const modalEditar = document.getElementById('modalEditar');
    if (modalEditar) {
        document.body.appendChild(modalEditar);
    }
});

function editAnuncio(anuncio) {
    document.getElementById('formEditar').action = `/anuncios-admin/${anuncio.id}`;
    document.getElementById('edit_titulo').value = anuncio.titulo;
    document.getElementById('edit_descripcion').value = anuncio.descripcion || '';
    document.getElementById('edit_prioridad').value = anuncio.prioridad;
    document.getElementById('edit_estado').value = anuncio.estado;
    document.getElementById('modalEditar').classList.remove('hidden');
}
</script>
</div>
@endsection
