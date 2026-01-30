@extends('layouts.user-simple')

@section('content')
    <a href="/vista-usuario" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">Regresar</a>
    <h1 class="text-center text-black text-3xl p-2">NOTIFICACIONES</h1>

    <div class="flex justify-center">
        <button class="tablink rounded-tl-lg" onclick="openPage('New', this,'#c2b280')" id="defaultOpen">NUEVAS</button>
        <button class="tablink rounded-tr-lg" onclick="openPage('Before', this,'#c2b280')">ANTERIORES</button>
    </div>

    <div id="New" class="tabcontent shadow-lg">
        <h3 class="text-2xl">Nuevas</h3>
        <ul>
            @forelse($nuevas as $n)
                <li class="notificacion notificacion-nueva bg-white rounded-xl pl-4 pb-4 hover:bg-[#8e8d8d] transition duration-300">
                    <h3 class="text-lg font-semibold">{{ $n->data['asunto'] }}</h3>
                    <p class="text-black text-sm">{{ $n->data['mensaje'] }}</p>
                    <span class="text-xs text-black">📅 {{ $n->created_at->format('d/m/Y H:i') }}</span>
                    <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                        @csrf
                        <button type="submit" class="bg-[#2f2f2f] hover:bg-[#575656] text-white px-3 py-1 rounded">
                            Marcar como leída
                        </button>
                    </form>
                </li>
            @empty
                <li class="text-[#585858] text-center py-4">No tienes notificaciones nuevas.</li>
            @endforelse
        </ul>
    </div>

    <div id="Before" class="tabcontent overflow-auto">
        <h3 class="text-2xl">Anteriores</h3>
        <ul class="space-y-4">
            @forelse($antiguas as $n)
                <li class="notificacion bg-white rounded-xl pl-4 pb-4 hover:bg-[#8e8d8d] transition duration-300">
                    <h3 class="text-lg font-semibold">{{ $n->data['asunto'] }}</h3>
                    <p class="text-black text-sm">{{ $n->data['mensaje'] }}</p>
                    <span class="text-xs text-black">📅 {{ $n->created_at->format('d/m/Y H:i') }}</span>
                <form method="POST" action="{{ route('notifications.delete', $n->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-[#c9a143] hover:bg-[#cf950d] text-white px-3 py-1 rounded">
                       Eliminar
                    </button>
                </form>

                </li>
            @empty
                <li class="text-[#585858] text-center py-4">No tienes notificaciones antiguas.</li>
            @endforelse
        </ul>
    </div>
<script src="/js/notviw.js"></script>
@endsection
