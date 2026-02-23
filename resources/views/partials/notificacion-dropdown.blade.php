@forelse($notificaciones as $n)
<div class="notificacion-nueva px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors">
    <h4 class="text-xs font-bold text-gray-800 leading-tight">{{ $n->data['asunto'] ?? '' }}</h4>
    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $n->data['mensaje'] ?? '' }}</p>
    <div class="flex items-center justify-between mt-2">
        <span class="text-[10px] text-gray-400">{{ $n->created_at->diffForHumans() }}</span>
        <form action="{{ route('notificaciones.leer', $n->id) }}" method="POST">
            @csrf
            <button type="submit" class="text-[10px] font-bold uppercase tracking-wider text-amber-600 hover:text-amber-800 transition-colors">
                Leída
            </button>
        </form>
    </div>
</div>
@empty
<p class="text-gray-500 text-sm p-4 text-center">No tienes notificaciones nuevas.</p>
@endforelse
@if($notificaciones->count() > 0)
<div class="p-2 text-center border-t border-gray-100">
    <a href="/notificaciones" class="text-[11px] font-bold uppercase tracking-wider text-amber-600 hover:text-amber-800 transition-colors">
        Ver todas
    </a>
</div>
@endif
