<div class="mes-selector-container relative" id="mesSelector">
    <button type="button" 
        class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-[#d8c495]/40 bg-white/10 text-white text-sm font-semibold hover:bg-white/15 transition"
        onclick="toggleMesSelector()">
        <svg class="w-4 h-4 text-[#d8c495]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span id="mesActual">{{ $mesActual ?? date('Y-m') }}</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div id="mesSelectorDropdown" class="hidden absolute right-0 top-full mt-2 z-50 bg-[#112134] border border-[#d8c495]/20 rounded-xl shadow-2xl p-4 w-72">
        <div class="flex items-center justify-between mb-4">
            <button type="button" onclick="cambiarAno('prev')" class="p-1 hover:bg-white/10 rounded transition">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <span id="anoDisplay" class="text-white font-bold">{{ $anoInicial ?? date('Y') }}</span>
            <button type="button" onclick="cambiarAno('next')" class="p-1 hover:bg-white/10 rounded transition">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
        
        <div id="mesesGrid" class="grid grid-cols-3 gap-2">
        </div>
    </div>
</div>

<script>
const MESES_NOMBRE = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
let anoActual = {{ $anoInicial }};
let mesesConFacturas = [];
let mesSeleccionado = '{{ $mesActual }}';

function toggleMesSelector() {
    const dropdown = document.getElementById('mesSelectorDropdown');
    dropdown.classList.toggle('hidden');
    if (!dropdown.classList.contains('hidden')) {
        cargarMesesFacturas();
    }
}

document.addEventListener('click', function(e) {
    const container = document.getElementById('mesSelector');
    if (container && !container.contains(e.target)) {
        document.getElementById('mesSelectorDropdown').classList.add('hidden');
    }
});

async function cargarMesesFacturas() {
    try {
        const response = await fetch('/api/cuentas/meses-facturados/' + anoActual);
        mesesConFacturas = await response.json();
        renderizarMeses();
    } catch(e) {
        console.error('Error cargando meses:', e);
        renderizarMeses();
    }
}

function renderizarMeses() {
    const grid = document.getElementById('mesesGrid');
    
    grid.innerHTML = MESES_NOMBRE.map((nombre, index) => {
        const mesNum = index + 1;
        const mesKey = anoActual + '-' + String(mesNum).padStart(2, '0');
        const tieneFactura = mesesConFacturas.includes(mesKey);
        const esSeleccionado = mesSeleccionado === mesKey;
        
        return `
            <button type="button" 
                onclick="seleccionarMes('${mesKey}')"
                class="relative p-2 rounded-lg text-xs font-medium transition-all text-center
                ${esSeleccionado ? 'bg-[#d8c495] text-[#0d1f30] font-bold ring-2 ring-[#d8c495]' : 'text-white/70 hover:bg-white/10'}
                ${tieneFactura && !esSeleccionado ? 'text-[#d8c495]' : ''}">
                <span class="block">${nombre}</span>
                ${tieneFactura ? '<span class="absolute top-0.5 right-0.5 w-1.5 h-1.5 bg-[#d8c495] rounded-full"></span>' : ''}
            </button>
        `;
    }).join('');
}

function cambiarAno(direccion) {
    if (direccion === 'prev') {
        anoActual--;
    } else {
        anoActual++;
    }
    document.getElementById('anoDisplay').textContent = anoActual;
    cargarMesesFacturas();
}

function seleccionarMes(mesKey) {
    mesSeleccionado = mesKey;
    document.getElementById('mesActual').textContent = mesKey;
    document.getElementById('mesSelectorDropdown').classList.add('hidden');
    const url = new URL(window.location.href);
    url.searchParams.set('month', mesKey);
    window.location.href = url.toString();
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    cargarMesesFacturas();
});
</script>
