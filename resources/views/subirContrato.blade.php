@extends('layouts.admin')

@section('content')
<header class="mb-10 px-2">
    <div class="flex items-baseline gap-4">
        <span class="text-dorado-400 text-sm font-serif italic">|</span>
        <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
            Contratos<span class="font-light text-dorado-400"></span><span class="text-dorado-400 animate-pulse">_</span>
        </h1>
    </div>
</header>
@if(session('success'))
<div id="alert-success" class="bg-green-800/80 border border-green-400/30 text-green-300 px-4 py-3 rounded-xl mb-4 text-sm">
    {{ session('success') }}
</div>
<script>
setTimeout(() => {
    document.getElementById('alert-success').style.display = 'none';
}, 4000);
</script>
@endif
<div class="max-w-6xl mx-auto p-6 bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20">

    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="post" action="{{ route('admin.contratos.buscar') }}" class="relative mx-12 flex items-center gap-2">
            @csrf
            <label for="searchInput" class="text-white/70 text-sm">Buscar por:</label>

            <input type="text" id="searchInput" name="search" placeholder="Buscar..." value="{{ $search }}"
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-[#d8c495]/30 bg-white/10 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495]">

            <select name="categoria" id="categoria"
                class="bg-[#0d1f30] p-3 rounded-lg mx-2 border border-[#d8c495]/30 text-white">
                <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>Contrato</option>
                <option value="name" {{ $categoria == 'name' ? 'selected' : '' }}>Usuario</option>
            </select>

            <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded mx-2 font-bold text-sm uppercase tracking-wider">
                BUSCAR
            </button>

            <a href="{{ route('admin.contratos.limpiar') }}"
                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded mx-2 text-sm">
                LIMPIAR
            </a>

            <!-- BOTÓN NUEVO CONTRATO -->
            <button type="button" id="openModalBtn" class="bg-[#d8c495]/20 hover:bg-[#d8c495]/30 text-[#d8c495] border border-[#d8c495]/40 px-4 py-2 rounded mx-2 text-sm font-bold">
                Agregar
            </button>
        </form>
    </div>

    <!-- Tabla de contratos -->
    <div class="tabla-dorada-container">
        <div class="overflow-x-auto custom-scroll">
            <table class="tabla-dorada">
                <thead>
                    <tr>
                        <th>Contrato</th>
                        <th>Usuario</th>
                        <th>Proyecto</th>
                        <th>Importe</th>
                        <th>Estado</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($contratos as $contrato)
                    <tr>
                        <td class="font-bold">{{ $contrato->id }}</td>
                        <td class="font-medium">{{ $contrato->user_name }}</td>
                        <td>{{ $contrato->proyecto }}</td>

                        <td class="font-mono text-xs">
                            ${{ number_format($contrato->importe_bruto_renta, 2) }}
                        </td>

                        <td>
                            <span
                                class="px-2 py-1 text-[10px] font-bold uppercase rounded-full tracking-wider
                            {{ $contrato->estado === 'activo' ? 'bg-green-900/40 text-green-400 border border-green-400/30' : 'bg-red-900/40 text-red-400 border border-red-400/30' }}">
                                {{ ucfirst($contrato->estado) }}
                            </span>
                        </td>

                        <td>
                            <button onclick="openModalEditar({{ $contrato->id }})"
                                class="inline-flex items-center justify-center bg-dorado-400 hover:bg-dorado/80 text-white w-8 h-8 rounded-lg transition shadow-sm border border-dorado/50">
                                <img src="/images/update.png" class="w-4 h-4 invert brightness-0" alt="editar">
                            </button>
                        </td>

                        <td>
                            <button onclick="openModalDelete({{ $contrato->id }})" type="submit"
                                class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white w-8 h-8 rounded-lg transition shadow-sm border border-red-600/50">
                                <img src="/images/delete.png" alt="eliminar" class="w-4 h-4 invert brightness-0">
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-white/30 font-medium italic">
                            No tienes contratos asignados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#d8c495]/10 p-4 flex justify-center">
            {{ $contratos->links('pagination::tailwind') }}
        </div>
    </div>


</div>
<!-- Modal editar contrato -->
<div id="confirmModalEditar"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 flex items-center justify-center hidden">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-xl shadow-lg p-6 w-96">
        <h2 class="text-[#d8c495] font-bold uppercase tracking-widest text-sm mb-4">Confirmar contraseña</h2>
        <form method="POST" action="{{ route('admin.contratos.confirmar-editar') }}">
            @csrf
            <input type="hidden" name="user_id" id="userIdInput" />

            <label for="password" class="block text-xs font-bold uppercase tracking-[0.2em] text-white/50 mb-2">Contraseña de administrador:</label>
            <input type="password" name="password" required
                class="w-full px-3 py-2 border border-[#d8c495]/30 rounded-lg bg-white/5 text-white mb-4 focus:outline-none focus:border-[#d8c495]">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModalEditar()"
                    class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded">Cancelar</button>
                <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] font-bold px-4 py-2 rounded">Confirmar</button>
            </div>
        </form>
    </div>
</div>
<!-- MODAL DE CONFIRMACIÓN (Nuevo contrato) -->
<div id="confirmModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 flex items-center justify-center hidden">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-xl shadow-lg p-6 w-96">
        <h2 class="text-[#d8c495] font-bold uppercase tracking-widest text-sm mb-4 text-center">Confirmar contraseña</h2>
        <form method="POST" action="{{ route('admin.contratos.confirmar') }}">
            @csrf
            <div class="mb-4">
                <label for="password" class="block text-xs font-bold uppercase tracking-[0.2em] text-white/50 mb-2">Contraseña del administrador:</label>
                <input type="password" name="password" id="password"
                    class="w-full px-3 py-2 border border-[#d8c495]/30 rounded-lg bg-white/5 text-white focus:outline-none focus:border-[#d8c495]"
                    required>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" id="closeModalBtn"
                    class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded">
                    Cancelar
                </button>
                <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] font-bold px-4 py-2 rounded">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>
<!--modal para eliminar contrato-->
<div id="confrimDeleteModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 flex items-center justify-center hidden">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-xl shadow-lg p-6 w-96">
        <h2 class="text-[#d8c495] font-bold uppercase tracking-widest text-sm mb-4">Confirmar contraseña</h2>
        <form method="POST" action="{{ route('admin.contratos.eliminar') }}">
            @csrf
            <input type="hidden" name="id" id="userIdInputDelete" />

            <label for="password" class="block text-xs font-bold uppercase tracking-[0.2em] text-white/50 mb-2">Contraseña de administrador:</label>
            <input type="password" name="password" required
                class="w-full px-3 py-2 border border-[#d8c495]/30 rounded-lg bg-white/5 text-white mb-4 focus:outline-none focus:border-[#d8c495]">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModalDelete()"
                    class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded">Cancelar</button>
                <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] font-bold px-4 py-2 rounded">Confirmar</button>
            </div>
        </form>
    </div>
</div>
<!-- SCRIPT PARA MODAL -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('confirmModal');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');

    openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
});

function openModalEditar(userId) {
    document.getElementById("confirmModalEditar").classList.remove("hidden");
    document.getElementById("userIdInput").value = userId;
}

function closeModalEditar() {
    document.getElementById("confirmModalEditar").classList.add("hidden");
}

function openModalDelete(userId) {
    document.getElementById("confrimDeleteModal").classList.remove("hidden");
    document.getElementById("userIdInputDelete").value = userId;
}

function closeModalDelete() {
    document.getElementById("confrimDeleteModal").classList.add("hidden");
}
</script>
@endsection
