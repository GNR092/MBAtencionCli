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

    <div class="max-w-6xl mx-auto p-6">
        <div class="flex justify-end mb-4">
            <button onclick="document.getElementById('modalCrear').classList.remove('hidden')" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-6 py-2 rounded font-bold shadow-md">+ NUEVO ANUNCIO</button>
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="w-full text-sm text-center border-collapse">
                <thead class="bg-[#2f2f2f] text-[#fff] uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3 border-b text-left">Título</th>
                    <th class="px-6 py-3 border-b">Prioridad</th>
                    <th class="px-6 py-3 border-b">Estado</th>
                    <th class="px-6 py-3 border-b">Acciones</th>
                </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-[#eee]">
                @foreach($anuncios as $anuncio)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-6 py-4 text-left font-semibold text-black">{{ $anuncio->titulo }}</td>
                        <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-[10px] font-bold {{ $anuncio->prioridad == 'alta' ? 'bg-red-200 text-red-800' : 'bg-blue-200 text-blue-800' }}">
                            {{ strtoupper($anuncio->prioridad) }}
                        </span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.anuncios.toggle', $anuncio->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-1 rounded-full text-xs font-bold transition {{ $anuncio->estado == 'activo' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                    {{ strtoupper($anuncio->estado) }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 flex justify-center gap-4">
                            <button onclick="editAnuncio({{ json_encode($anuncio) }})" class="text-blue-600 font-bold text-xs uppercase hover:underline">Editar</button>
                            <form action="{{ route('admin.anuncios.destroy', $anuncio->id) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 font-bold text-xs uppercase hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalCrear" class="bg-black/50 backdrop-blur-sm fixed inset-0 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-[500px] text-black shadow-2xl">
            <h2 class="text-lg font-bold mb-4 uppercase border-b pb-2">Nuevo Anuncio</h2>
            <form action="{{ route('admin.anuncios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <input type="text" name="titulo" placeholder="Título" required class="w-full p-2 border rounded bg-[#eee]">
                    <textarea name="descripcion" placeholder="Descripción" class="w-full p-2 border rounded bg-[#eee]"></textarea>
                    <select name="prioridad" class="w-full p-2 border rounded bg-[#eee]">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                    </select>
                    <input type="file" name="adjunto" class="w-full text-xs">
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('modalCrear').classList.add('hidden')" class="bg-gray-300 px-4 py-2 rounded font-bold">CANCELAR</button>
                    <button type="submit" class="bg-[#d8c495] text-black px-6 py-2 rounded font-bold">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="bg-black/50 backdrop-blur-sm fixed inset-0 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-[500px] text-black shadow-2xl">
            <h2 class="text-lg font-bold mb-4 uppercase border-b pb-2">Editar Anuncio</h2>
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <input type="text" name="titulo" id="edit_titulo" required class="w-full p-2 border rounded bg-[#eee]">
                    <textarea name="descripcion" id="edit_descripcion" class="w-full p-2 border rounded bg-[#eee]"></textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <select name="prioridad" id="edit_prioridad" class="p-2 border rounded bg-[#eee]">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                        <select name="estado" id="edit_estado" class="p-2 border rounded bg-[#eee]">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <input type="file" name="adjunto" class="w-full text-xs">
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('modalEditar').classList.add('hidden')" class="bg-gray-300 px-4 py-2 rounded font-bold">CANCELAR</button>
                    <button type="submit" class="bg-[#c9a143] text-white px-6 py-2 rounded font-bold">ACTUALIZAR</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editAnuncio(anuncio) {
            document.getElementById('formEditar').action = `/anuncios-admin/${anuncio.id}`;
            document.getElementById('edit_titulo').value = anuncio.titulo;
            document.getElementById('edit_descripcion').value = anuncio.descripcion || '';
            document.getElementById('edit_prioridad').value = anuncio.prioridad;
            document.getElementById('edit_estado').value = anuncio.estado;
            document.getElementById('modalEditar').classList.remove('hidden');
        }
    </script>
@endsection
