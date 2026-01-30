@extends('layouts.admin')

@section('content')
<h1 class="text-center text-black text-3xl p-4">CONTRATOS</h1>
@if(session('success'))
    <div id="alert-success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('alert-success').style.display = 'none';
        }, 4000);
    </script>
@endif
<div class="max-w-6xl mx-auto p-6 bg-[#0b0b0b] rounded-2xl shadow-lg">

    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form  method="post" action="{{ route('contratos.search') }}"class="relative mx-12 flex items-center gap-2">
            @csrf
            <label for="searchInput" class="text-white">Buscar por:</label>

            <input 
                type="text" 
                id="searchInput"
                name="search" 
                placeholder="Buscar..." 
                 value="{{ $search }}"
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-gray-400 bg-[#eee]"
            >

            <select name="categoria" id="categoria" class="bg-[#eee] p-3 rounded-lg mx-2 border border-gray-400 text-black">
                <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>Contrato</option>
                <option value="name" {{ $categoria == 'name' ? 'selected' : '' }}>Usuario</option>
            </select>

            <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-2">
                BUSCAR
            </button>

            <a href="{{ route('contratos.clean') }}" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">
                LIMPIAR
            </a>
        </form>

        <!-- BOTÓN NUEVO CONTRATO -->
        <button id="openModalBtn"
            class="bg-gradient-to-r from-[#fec127] to-[#ff9900] rounded px-4 py-2 ml-10 shadow-lg text-white font-bold hover:scale-110 transition">
            Nuevo contrato
        </button>
    </div>

    <!-- Tabla de contratos -->
    <div class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="w-full text-sm text-center border-collapse">
            <thead class="bg-[#2f2f2f] text-[#fff] uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3 border-b">Contrato</th>
                    <th class="px-6 py-3 border-b">Usuario</th>
                    <th class="px-6 py-3 border-b">Proyecto</th>
                    <th class="px-6 py-3 border-b">Importe</th>
                    <th class="px-6 py-3 border-b">Estado</th>
                    <th class="px-6 py-3 border-b">Editar</th>
                    <th class="px-6 py-3 border-b">Eliminar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#eee]">
                @forelse($contratos as $contrato)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-6 py-4">{{ $contrato->id }}</td>
                        <td class="px-6 py-4">{{ $contrato->user_name }}</td>
                        <td class="px-6 py-4">{{ $contrato->proyecto }}</td>
                        <td class="px-6 py-4">{{ $contrato->importe_bruto_renta }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                {{ $contrato->estado === 'activo' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                {{ ucfirst($contrato->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openModalEditar({{ $contrato->id }})"
                            class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded-lg transition inline-block">
                            <img src="/images/update.png" class="w-5 h-5" alt="editar">
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openModalDelete({{ $contrato->id }})" type="submit" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                                <img src="/images/delete.png" alt="eliminar" class="w-5 h-5">
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-gray-500">
                            No tienes contratos asignados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
                        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow p-2 ">
                {{ $contratos->links('pagination::tailwind') }}
            </div>
        </div>

    </div>
</div>
<!-- Modal editar  contrato -->
<div id="confirmModalEditar" class=" bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-lg font-bold mb-4">Confirmar contraseña</h2>
        <form method="POST" action="{{ route('contratos.confirmPasswordEdit') }}">
            @csrf
            <input type="hidden" name="user_id" id="userIdInput"/>

            <label for="password" class="block text-sm mb-2">Contraseña de administrador:</label>
            <input type="password" name="password" required
                   class="w-full px-3 py-2 border border-gray-400 rounded mb-4">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModalEditar()"
                        class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
                <button type="submit"
                        class="bg-[#d8c495] hover:bg-[#c9a143] px-4 py-2 rounded">Confirmar</button>
            </div>

        </form>
    </div>
</div>
<!-- MODAL DE CONFIRMACIÓN -->
<div id="confirmModal" class="bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-lg font-bold mb-4 text-center">Confirmar contraseña</h2>
        <form method="POST" action="{{ route('contratos.confirmPassword') }}">
            @csrf
            <div class="mb-4">
                <label for="password" class="block text-gray-700 mb-2">Contraseña del administrador:</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" 
                    required>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" id="closeModalBtn" 
                        class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded">
                    Cancelar
                </button>
                <button type="submit" 
                        class="bg-[#033a7c] hover:bg-[#022b5a] text-white px-4 py-2 rounded">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>
<!--modal para eliminar contrato-->
<div id="confrimDeleteModal" class=" bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-lg font-bold mb-4">Confirmar contraseña</h2>
        <form method="POST" action="{{ route('contratos.delete') }}">
            @csrf
            <input type="hidden" name="id" id="userIdInputDelete"/>

            <label for="password" class="block text-sm mb-2">Contraseña de administrador:</label>
            <input type="password" name="password" required
                   class="w-full px-3 py-2 border border-gray-400 rounded mb-4">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModalDelete()"
                        class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
                <button type="submit"
                        class="bg-[#d8c495] hover:bg-[#c9a143] px-4 py-2 rounded">Confirmar</button>
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
