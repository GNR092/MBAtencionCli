@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">

        <header class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#d8c495]/10 border border-[#d8c495]/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#d8c495]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-white/95 tracking-tight">Editor de Plantilla</h1>
                    <p class="text-xs text-white/40 mt-0.5">Diseña el fondo, zonas de texto e imágenes para la tarjeta</p>
                </div>
            </div>
            <a href="{{ route('usuarios.cumpleanios') }}" class="btn-dorado">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </header>

        <form action="{{ route('cumpleanios.template.save') }}" method="POST" enctype="multipart/form-data" class="space-y-6">

            @csrf

            <section class="bg-[#0d1f30]/60 backdrop-blur-sm border border-[#d8c495]/10 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-[#d8c495]"></div>
                    <h2 class="text-sm font-medium text-[#d8c495]">Información de plantilla</h2>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] text-white/50 uppercase tracking-wider font-medium">Nombre</label>
                        <input name="name" required value="{{ old('name', $template->name ?? 'Plantilla General') }}" class="w-full bg-[#0a1520] border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white/90 placeholder-white/20 focus:outline-none focus:border-[#d8c495]/40 focus:bg-[#0d1f30] transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] text-white/50 uppercase tracking-wider font-medium">Imagen de fondo</label>
                        <input type="file" accept="image/*" name="background" id="backgroundInput" class="w-full bg-[#0a1520] border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white/90 file:mr-3 file:text-[#d8c495] file:border-0 file:bg-[#d8c495]/10 file:rounded-md file:px-3 file:py-1 file:text-xs file:font-medium file:cursor-pointer transition-all focus:outline-none focus:border-[#d8c495]/40">
                        @if(!empty($template?->background_path))
                            <label class="inline-flex items-center gap-2 text-xs text-white/40 mt-1.5 cursor-pointer hover:text-white/60 transition-colors">
                                <input type="checkbox" name="remove_background" value="1" class="rounded border-white/20 bg-[#0a1520]">
                                Quitar imagen actual
                            </label>
                        @endif
                    </div>
                </div>
                <div class="mt-4 space-y-1.5">
                    <label class="text-[11px] text-white/50 uppercase tracking-wider font-medium">Mensaje genérico <span class="text-[#d8c495]/50 normal-case tracking-normal">(usa [NOMBRE] para insertar el nombre)</span></label>
                    <textarea name="default_message" rows="2" class="w-full bg-[#0a1520] border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white/90 placeholder-white/20 focus:outline-none focus:border-[#d8c495]/40 focus:bg-[#0d1f30] transition-all resize-none">{{ old('default_message', $template->default_message ?? 'Feliz cumpleaños [NOMBRE], te deseamos un excelente día.') }}</textarea>
                </div>
            </section>

            <section class="bg-[#0d1f30]/60 backdrop-blur-sm border border-[#d8c495]/10 rounded-2xl p-5">
                <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full bg-[#d8c495]"></div>
                        <h2 class="text-sm font-medium text-[#d8c495]">Lienzo de diseño</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-white/30 uppercase tracking-wider font-medium">Ancho</span>
                            <input type="number" id="canvasWidth" value="{{ $template->canvas_width ?? 960 }}" min="400" max="2000" class="w-16 bg-[#0a1520] border border-white/10 rounded-md px-2 py-1.5 text-xs text-white/80 text-center focus:outline-none focus:border-[#d8c495]/40">
                        </div>
                        <span class="text-white/20 text-xs">×</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-white/30 uppercase tracking-wider font-medium">Alto</span>
                            <input type="number" id="canvasHeight" value="{{ $template->canvas_height ?? 540 }}" min="300" max="1500" class="w-16 bg-[#0a1520] border border-white/10 rounded-md px-2 py-1.5 text-xs text-white/80 text-center focus:outline-none focus:border-[#d8c495]/40">
                        </div>
                        <button type="button" id="resizeCanvasBtn" class="px-3 py-1.5 rounded-md bg-[#d8c495]/10 hover:bg-[#d8c495]/20 border border-[#d8c495]/20 text-[#d8c495]/80 hover:text-[#d8c495] text-xs font-medium transition-all">Aplicar</button>
                    </div>
                </div>

                <div class="overflow-auto rounded-xl border border-white/5 bg-[#0a1520]/80" style="max-height:60vh;">
                    <canvas id="editorCanvas" width="{{ $template->canvas_width ?? 960 }}" height="{{ $template->canvas_height ?? 540 }}" class="block"></canvas>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-white/30 uppercase tracking-wider font-medium mr-1">Zonas:</span>
                    </div>
                    <button type="button" id="addNameZone" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#d8c495]/10 hover:bg-[#d8c495]/15 border border-[#d8c495]/25 text-[#d8c495]/90 hover:text-[#d8c495] text-xs font-medium transition-all">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#d8c495]"></span>Nombre
                    </button>
                    <button type="button" id="addMessageZone" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#d8c495]/10 hover:bg-[#d8c495]/15 border border-[#d8c495]/25 text-[#d8c495]/90 hover:text-[#d8c495] text-xs font-medium transition-all">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#d8c495]/60"></span>Mensaje
                    </button>
                    <button type="button" id="addTextZone" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#d8c495]/10 hover:bg-[#d8c495]/15 border border-[#d8c495]/25 text-[#d8c495]/90 hover:text-[#d8c495] text-xs font-medium transition-all">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#d8c495]/30"></span>Texto
                    </button>

                    <div class="h-4 w-px bg-white/10 mx-1"></div>

                    <button type="button" id="addOverlayBtn" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white/60 hover:text-white/80 text-xs font-medium transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Imagen
                    </button>
                    <input type="file" accept="image/*" id="overlayInput" class="hidden" multiple>

                    <button type="button" id="deleteZone" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-red-500/20 text-red-400/60 hover:text-red-400 hover:bg-red-500/10 text-xs font-medium transition-all disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent ml-auto" disabled>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                </div>
            </section>

            <div class="grid md:grid-cols-2 gap-4">
                <div id="overlayControls" class="hidden bg-[#0d1f30]/60 backdrop-blur-sm border border-[#d8c495]/10 rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-3 rounded-full bg-[#d8c495]/60"></div>
                        <h3 class="text-xs font-medium text-[#d8c495]/80">Imágenes superpuestas</h3>
                    </div>
                    <div id="overlayList" class="space-y-2"></div>
                </div>

                <div id="zoneProperties" class="hidden bg-[#0d1f30]/60 backdrop-blur-sm border border-[#d8c495]/10 rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-3 rounded-full bg-[#d8c495]/60"></div>
                        <h3 class="text-xs font-medium text-[#d8c495]/80">Propiedades de zona</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-white/40">Tipo</span>
                            <span id="propZoneType" class="text-xs text-white/80 font-medium px-2.5 py-1 rounded-md bg-white/5 border border-white/10">-</span>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-white/40 uppercase tracking-wider">Capa (z-index)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="propLayer" min="0" max="999" class="w-full bg-[#0a1520] border border-white/10 rounded-lg px-3 py-2 text-sm text-white/80 focus:outline-none focus:border-[#d8c495]/40">
                                <div class="flex items-center gap-1">
                                    <button type="button" id="btnLayerUp" title="Subir capa" class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white/60 hover:text-white transition-all flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" id="btnLayerDown" title="Bajar capa" class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white/60 hover:text-white transition-all flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] text-white/40 uppercase tracking-wider">Tamaño fuente</label>
                                <input type="number" id="propFontSize" min="10" max="120" class="w-full bg-[#0a1520] border border-white/10 rounded-lg px-3 py-2 text-sm text-white/80 focus:outline-none focus:border-[#d8c495]/40">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-white/40 uppercase tracking-wider">Color texto</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="propTextColor" value="#ffffff" class="w-8 h-8 rounded-lg border border-white/10 cursor-pointer bg-transparent">
                                    <span id="propTextColorHex" class="text-xs text-white/50 font-mono">#ffffff</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-white/40 uppercase tracking-wider">Texto preview</label>
                            <input type="text" id="propZoneText" class="w-full bg-[#0a1520] border border-white/10 rounded-lg px-3 py-2 text-sm text-white/80 focus:outline-none focus:border-[#d8c495]/40" placeholder="Escribe el texto aquí...">
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="zonesJson" name="zones_json" value="{{ old('zones_json', json_encode($template->zones_json ?? [])) }}">
            <input type="hidden" id="overlayImagesJson" name="overlay_images_json" value="{{ old('overlay_images_json', json_encode($template->overlay_images ?? [])) }}">
            <input type="hidden" name="canvas_width" id="canvasWidthInput" value="{{ old('canvas_width', $template->canvas_width ?? 960) }}">
            <input type="hidden" name="canvas_height" id="canvasHeightInput" value="{{ old('canvas_height', $template->canvas_height ?? 540) }}">

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-2 text-xs text-white/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Usa el canvas para posicionar las zonas arrastrando</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="btnPreview" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white/70 hover:text-white text-sm font-medium transition-all">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Previsualizar
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#d8c495] hover:bg-[#d8c495]/90 text-[#0f172a] text-sm font-bold transition-all shadow-lg shadow-[#d8c495]/10">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Guardar plantilla
                    </button>
                </div>
            </div>
        </form>

        <div id="previewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-[#0d1f30] border border-[#d8c495]/15 rounded-2xl shadow-2xl shadow-black/50 w-full max-w-3xl mx-4 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#d8c495]/10 border border-[#d8c495]/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#d8c495]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-[#d8c495]">Vista previa</h3>
                            <p class="text-[10px] text-white/30 mt-0.5">Previsualización de la tarjeta de cumpleaños</p>
                        </div>
                    </div>
                    <button type="button" id="btnClosePreview" class="w-8 h-8 flex items-center justify-center rounded-lg text-white/40 hover:text-white hover:bg-white/5 transition-all">&times;</button>
                </div>
                <div class="p-6 flex justify-center" id="previewContent">
                    <div id="previewCanvas" class="rounded-xl overflow-hidden shadow-xl" style="background:#1f2937;"></div>
                </div>
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-white/5">
                    <button type="button" id="btnAbrirNuevaPestana" class="px-4 py-2 rounded-lg bg-[#d8c495]/10 hover:bg-[#d8c495]/20 border border-[#d8c495]/20 text-[#d8c495]/80 hover:text-[#d8c495] text-xs font-medium transition-all">
                        <svg class="w-3.5 h-3.5 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Abrir en nueva pestaña
                    </button>
                    <button type="button" id="btnCerrarPreview" class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white/60 hover:text-white text-xs font-medium transition-all">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = new fabric.Canvas('editorCanvas');
    const bgInput = document.getElementById('backgroundInput');
    const zonesInput = document.getElementById('zonesJson');
    const deleteZoneBtn = document.getElementById('deleteZone');
    const overlayInput = document.getElementById('overlayInput');
    const addOverlayBtn = document.getElementById('addOverlayBtn');
    const overlayControls = document.getElementById('overlayControls');
    const overlayList = document.getElementById('overlayList');
    const overlayImagesJson = document.getElementById('overlayImagesJson');

    let overlayImages = JSON.parse(overlayImagesJson.value || '[]');
    let fabricOverlays = [];

    const BASE_W = parseInt(document.getElementById('canvasWidth').value) || 960;
    const BASE_H = parseInt(document.getElementById('canvasHeight').value) || 540;

    function getCanvasScale() {
        return {
            scaleX: canvas.getWidth() / BASE_W,
            scaleY: canvas.getHeight() / BASE_H,
        };
    }

    function getScaledFontSize(baseSize) {
        const { scaleX, scaleY } = getCanvasScale();
        return Math.round(baseSize * (scaleX + scaleY) / 2);
    }

    function zoneRect(label, color, left, top) {
        const rect = new fabric.Rect({
            left: left || 60,
            top: top || 60,
            width: 260,
            height: 70,
            fill: 'transparent',
            stroke: color,
            strokeWidth: 2,
            rx: 8,
            ry: 8,
        });
        rect.zoneType = label;
        rect.zoneColor = color;
        rect.fontSize = 28;
        rect.textColor = '#ffffff';
        rect.zoneLayer = 0;
        return rect;
    }

    function drawZoneLabel(rect) {
        const zoneLabel = rect.zoneType === 'name' ? 'NOMBRE' : rect.zoneType === 'message' ? 'MENSAJE' : 'TEXTO';
        const label = new fabric.Text(rect.zoneText || zoneLabel, {
            left: rect.left + 10,
            top: rect.top + 20,
            fontSize: getScaledFontSize(18),
            fill: rect.zoneColor,
            selectable: false,
            evented: false,
        });
        rect._label = label;
        canvas.add(label);
    }

    function syncLabels() {
        const baseLabelSize = getScaledFontSize(18);
        canvas.getObjects('rect').forEach(function (rect) {
            if (!rect._label) return;
            rect._label.set({
                left: rect.left + 10,
                top: rect.top + 20,
                fontSize: baseLabelSize,
            });
        });
        canvas.renderAll();
    }

    function addZone(type) {
        const color = type === 'name' ? '#d8c495' : (type === 'message' ? '#d8c495bb' : '#d8c49566');
        const rect = zoneRect(type, color);
        canvas.add(rect);
        drawZoneLabel(rect);
        canvas.setActiveObject(rect);
        updateDeleteButtonState();
        syncZonesToInput();
    }

    function updateDeleteButtonState() {
        const activeObject = canvas.getActiveObject();
        deleteZoneBtn.disabled = !activeObject;
        updateZonePropertiesPanel();
    }

    function updateZonePropertiesPanel() {
        const activeObject = canvas.getActiveObject();
        const panel = document.getElementById('zoneProperties');
        const typeSpan = document.getElementById('propZoneType');
        const fontSizeInput = document.getElementById('propFontSize');
        const textColorInput = document.getElementById('propTextColor');
        const zoneTextInput = document.getElementById('propZoneText');

        if (!activeObject || !activeObject.zoneType) {
            panel.classList.add('hidden');
            return;
        }

        panel.classList.remove('hidden');
        const typeLabels = { name: 'Nombre', message: 'Mensaje', text: 'Texto' };
        typeSpan.textContent = typeLabels[activeObject.zoneType] || 'Texto';
        document.getElementById('propLayer').value = activeObject.zoneLayer !== undefined ? activeObject.zoneLayer : 0;
        fontSizeInput.value = activeObject.fontSize || 28;
        textColorInput.value = activeObject.textColor || '#ffffff';
        zoneTextInput.value = activeObject.zoneText || '';
    }

    function applyZoneProperties() {
        const activeObject = canvas.getActiveObject();
        if (!activeObject || !activeObject.zoneType) return;

        const fontSize = parseInt(document.getElementById('propFontSize').value) || 28;
        const textColor = document.getElementById('propTextColor').value;
        const zoneText = document.getElementById('propZoneText').value;
        const zoneLayer = parseInt(document.getElementById('propLayer').value) || 0;

        activeObject.fontSize = fontSize;
        activeObject.textColor = textColor;
        activeObject.zoneText = zoneText;
        activeObject.zoneLayer = zoneLayer;
        activeObject.moveTo(zoneLayer);
        canvas.renderAll();

        if (activeObject._label) {
            activeObject._label.set({
                fontSize: getScaledFontSize(fontSize),
                fill: activeObject.zoneColor,
            });
        }

        if (activeObject.zoneType === 'name') {
            activeObject._label.set({ text: zoneText || 'NOMBRE' });
        } else if (activeObject.zoneType === 'message') {
            activeObject._label.set({ text: zoneText || 'MENSAJE' });
        } else {
            activeObject._label.set({ text: zoneText || 'TEXTO' });
        }

        syncLabels();

        syncLabels();
        syncZonesToInput();
    }

    function deleteSelectedZone() {
        const activeObject = canvas.getActiveObject();
        if (!activeObject) return;
        if (activeObject._label) canvas.remove(activeObject._label);
        canvas.remove(activeObject);
        canvas.discardActiveObject();
        canvas.renderAll();
        updateDeleteButtonState();
        syncZonesToInput();
    }

    document.getElementById('addNameZone').addEventListener('click', function () { addZone('name'); });
    document.getElementById('addMessageZone').addEventListener('click', function () { addZone('message'); });
    document.getElementById('addTextZone').addEventListener('click', function () { addZone('text'); });
    deleteZoneBtn.addEventListener('click', deleteSelectedZone);
    addOverlayBtn.addEventListener('click', function() { overlayInput.click(); });

    document.getElementById('resizeCanvasBtn').addEventListener('click', function () {
        const w = parseInt(document.getElementById('canvasWidth').value) || 960;
        const h = parseInt(document.getElementById('canvasHeight').value) || 540;
        canvas.setDimensions({ width: w, height: h });
        canvas.renderAll();
        document.getElementById('canvasWidthInput').value = w;
        document.getElementById('canvasHeightInput').value = h;
        syncLabels();
        syncZonesToInput();
    });
    canvas.on('object:moving', function () { syncLabels(); syncZonesToInput(); });
    canvas.on('object:scaling', function () { syncLabels(); syncZonesToInput(); });
    canvas.on('selection:created', updateDeleteButtonState);
    canvas.on('selection:updated', updateDeleteButtonState);
    canvas.on('selection:cleared', updateDeleteButtonState);

    document.getElementById('propFontSize').addEventListener('input', applyZoneProperties);
    document.getElementById('propTextColor').addEventListener('input', applyZoneProperties);
    document.getElementById('propZoneText').addEventListener('input', applyZoneProperties);
    document.getElementById('propLayer').addEventListener('input', applyZoneProperties);

    document.getElementById('btnLayerUp').addEventListener('click', function() {
        const activeObject = canvas.getActiveObject();
        if (!activeObject) return;
        const layerInput = document.getElementById('propLayer');
        const current = parseInt(layerInput.value) || 0;
        layerInput.value = current + 1;
        activeObject.zoneLayer = current + 1;
        activeObject.moveTo(current + 1);
        canvas.renderAll();
        syncZonesToInput();
    });

    document.getElementById('btnLayerDown').addEventListener('click', function() {
        const activeObject = canvas.getActiveObject();
        if (!activeObject) return;
        const layerInput = document.getElementById('propLayer');
        const current = parseInt(layerInput.value) || 0;
        if (current > 0) {
            layerInput.value = current - 1;
            activeObject.zoneLayer = current - 1;
            activeObject.moveTo(current - 1);
            canvas.renderAll();
            syncZonesToInput();
        }
    });

    document.addEventListener('keydown', function (event) {
        const targetTag = (event.target && event.target.tagName) ? event.target.tagName.toLowerCase() : '';
        if (targetTag === 'input' || targetTag === 'textarea') return;

        const activeObject = canvas.getActiveObject();
        const step = event.shiftKey ? 10 : 1;
        let moved = false;

        if (event.key === 'Delete' || event.key === 'Backspace') {
            event.preventDefault();
            if (activeObject) deleteSelectedZone();
            return;
        }

        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
            event.preventDefault();
            if (!activeObject) return;
            switch (event.key) {
                case 'ArrowUp': activeObject.set('top', activeObject.top - step); moved = true; break;
                case 'ArrowDown': activeObject.set('top', activeObject.top + step); moved = true; break;
                case 'ArrowLeft': activeObject.set('left', activeObject.left - step); moved = true; break;
                case 'ArrowRight': activeObject.set('left', activeObject.left + step); moved = true; break;
            }
            if (moved) {
                canvas.renderAll();
                syncLabels();
                syncZonesToInput();
            }
        }
    });

    if (@json(isset($template->background_path) ? asset('storage/'.$template->background_path) : null)) {
        fabric.Image.fromURL(@json(asset('storage/'.$template->background_path)), function (img) {
            img.selectable = false;
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                scaleX: canvas.width / img.width,
                scaleY: canvas.height / img.height,
            });
        }, { crossOrigin: 'anonymous' });
    }

    const existingZones = JSON.parse(zonesInput.value || '[]');
    existingZones.forEach(function (z) {
        const zoneColor = z.type === 'name' ? '#d8c495' : (z.type === 'message' ? '#d8c495bb' : '#d8c49566');
        const rect = zoneRect(z.type || 'name', zoneColor, z.x || 60, z.y || 60);
        rect.set({ width: z.width || 260, height: z.height || 70 });
        rect.fontSize = z.fontSize || 28;
        rect.textColor = z.textColor || '#ffffff';
        rect.zoneText = z.zoneText || '';
        rect.zoneLayer = z.layer !== undefined ? z.layer : 0;
        rect.moveTo(rect.zoneLayer);
        canvas.add(rect);
        drawZoneLabel(rect);
    });

    updateDeleteButtonState();

    bgInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            fabric.Image.fromURL(e.target.result, function (img) {
                img.selectable = false;
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                    scaleX: canvas.width / img.width,
                    scaleY: canvas.height / img.height,
                });
            });
        };
        reader.readAsDataURL(file);
    });

    function loadOverlayImages() {
        overlayImages.forEach(function (imgData, idx) {
            const url = imgData.path.startsWith('http') ? imgData.path : @json(asset('storage/')).replace(/\/$/, '') + '/' + imgData.path;
            fabric.Image.fromURL(url, function (fabricImg) {
                const originalSize = fabricImg.getOriginalSize();
                fabricImg._originalWidth = originalSize.x;
                fabricImg._originalHeight = originalSize.y;
                const desiredW = imgData.width || originalSize.x || 150;
                const desiredH = imgData.height || originalSize.y || 150;
                const imgLayer = imgData.layer !== undefined ? imgData.layer : 0;
                fabricImg.set({
                    left: imgData.x || 0,
                    top: imgData.y || 0,
                    scaleX: desiredW / originalSize.x,
                    scaleY: desiredH / originalSize.y,
                    angle: imgData.rotation || 0,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                });
                fabricImg._overlayIdx = idx;
                fabricImg._overlayLayer = imgLayer;
                fabricImg.moveTo(imgLayer);
                canvas.add(fabricImg);
                fabricOverlays.push(fabricImg);
            }, { crossOrigin: 'anonymous' });
        });
        renderOverlayList();
    }

    function renderOverlayList() {
        if (overlayImages.length === 0) {
            overlayControls.classList.add('hidden');
            overlayList.innerHTML = '';
            return;
        }
        overlayControls.classList.remove('hidden');
        overlayList.innerHTML = overlayImages.map(function (img, idx) {
            return '<div class="flex items-center gap-3 p-3 bg-white/[0.02] rounded-lg border border-white/5 flex-wrap">' +
                '<span class="text-[11px] text-white/40 w-full mb-1">' + (img.originalFilename || 'Imagen ' + (idx + 1)) + '</span>' +
                '<div class="flex items-center gap-2 flex-wrap">' +
                    '<label class="text-[10px] text-white/50">X <input type="number" data-idx="' + idx + '" data-field="x" value="' + (img.x || 0) + '" class="w-14 bg-[#0a1520] border border-white/10 rounded px-1.5 py-1 text-white/80 text-xs"></label>' +
                    '<label class="text-[10px] text-white/50">Y <input type="number" data-idx="' + idx + '" data-field="y" value="' + (img.y || 0) + '" class="w-14 bg-[#0a1520] border border-white/10 rounded px-1.5 py-1 text-white/80 text-xs"></label>' +
                    '<label class="text-[10px] text-white/50">W <input type="number" data-idx="' + idx + '" data-field="width" value="' + (img.width || 150) + '" class="w-14 bg-[#0a1520] border border-white/10 rounded px-1.5 py-1 text-white/80 text-xs"></label>' +
                    '<label class="text-[10px] text-white/50">H <input type="number" data-idx="' + idx + '" data-field="height" value="' + (img.height || 150) + '" class="w-14 bg-[#0a1520] border border-white/10 rounded px-1.5 py-1 text-white/80 text-xs"></label>' +
                    '<label class="text-[10px] text-white/50">Rot <input type="number" data-idx="' + idx + '" data-field="rotation" value="' + (img.rotation || 0) + '" class="w-14 bg-[#0a1520] border border-white/10 rounded px-1.5 py-1 text-white/80 text-xs"></label>' +
                    '<label class="text-[10px] text-white/50">Capa <input type="number" data-idx="' + idx + '" data-field="layer" value="' + (img.layer !== undefined ? img.layer : 0) + '" min="0" max="999" class="w-14 bg-[#0a1520] border border-white/10 rounded px-1.5 py-1 text-white/80 text-xs"></label>' +
                '</div>' +
                '<button type="button" data-remove="' + idx + '" class="text-red-400/60 hover:text-red-400 text-[11px] px-2 py-1 rounded hover:bg-red-500/10 transition-all">Quitar</button>' +
            '</div>';
        }).join('');

        overlayList.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', function () {
                const idx = parseInt(this.dataset.idx);
                const field = this.dataset.field;
                overlayImages[idx][field] = parseFloat(this.value) || 0;
                syncOverlayToCanvas(idx);
                overlayImagesJson.value = JSON.stringify(overlayImages);
            });
        });

        overlayList.querySelectorAll('[data-remove]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.dataset.remove);
                removeOverlayImage(idx);
            });
        });
    }

    function syncOverlayToCanvas(idx) {
        const fabricImg = fabricOverlays[idx];
        const imgData = overlayImages[idx];
        if (!fabricImg) return;

        const newWidth = imgData.width || 150;
        const newHeight = imgData.height || 150;
        const scaleX = newWidth / (fabricImg._originalWidth || fabricImg.width || 150);
        const scaleY = newHeight / (fabricImg._originalHeight || fabricImg.height || 150);
        const newLayer = imgData.layer !== undefined ? imgData.layer : 0;

        fabricImg.set({
            left: imgData.x || 0,
            top: imgData.y || 0,
            angle: imgData.rotation || 0,
            scaleX: scaleX,
            scaleY: scaleY,
        });
        fabricImg._overlayLayer = newLayer;
        fabricImg.moveTo(newLayer);
        fabricImg.setCoords();
        canvas.renderAll();
    }

    function removeOverlayImage(idx) {
        if (fabricOverlays[idx]) {
            canvas.remove(fabricOverlays[idx]);
            fabricOverlays.splice(idx, 1);
        }
        overlayImages.splice(idx, 1);
        overlayImagesJson.value = JSON.stringify(overlayImages);
        renderOverlayList();
        canvas.renderAll();
    }

    overlayInput.addEventListener('change', function () {
        const files = Array.from(this.files);
        if (files.length === 0) return;

        files.forEach(function (file, fileIdx) {
            const reader = new FileReader();
            reader.onload = function (e) {
                fabric.Image.fromURL(e.target.result, function (fabricImg) {
                    const originalSize = fabricImg.getOriginalSize();
                    fabricImg._originalWidth = originalSize.x;
                    fabricImg._originalHeight = originalSize.y;
                    const idx = overlayImages.length;
                    fabricImg.set({
                        left: 100 + (idx * 50),
                        top: 100 + (idx * 50),
                        selectable: true,
                        hasControls: true,
                    });
                    fabricImg._overlayIdx = idx;
                    canvas.add(fabricImg);
                    fabricOverlays.push(fabricImg);

                    overlayImages.push({
                        path: null,
                        x: fabricImg.left,
                        y: fabricImg.top,
                        width: originalSize.x,
                        height: originalSize.y,
                        rotation: 0,
                        layer: 0,
                        originalFilename: file.name,
                        _tempData: e.target.result,
                    });

                    fabricImg._overlayIdx = idx;
                    fabricImg._overlayLayer = 0;

                    overlayImagesJson.value = JSON.stringify(overlayImages);
                    renderOverlayList();
                });
            };
            reader.readAsDataURL(file);
        });

        this.value = '';
    });

    canvas.on('object:modified', function (e) {
        const obj = e.target;
        if (obj._overlayIdx !== undefined) {
            overlayImages[obj._overlayIdx].x = Math.round(obj.left);
            overlayImages[obj._overlayIdx].y = Math.round(obj.top);
            overlayImages[obj._overlayIdx].rotation = Math.round(obj.angle);
            const origW = obj._originalWidth || obj.width;
            const origH = obj._originalHeight || obj.height;
            overlayImages[obj._overlayIdx].width = Math.round(origW * obj.scaleX);
            overlayImages[obj._overlayIdx].height = Math.round(origH * obj.scaleY);
            overlayImagesJson.value = JSON.stringify(overlayImages);
            renderOverlayList();
        }
    });

    loadOverlayImages();

    document.getElementById('btnPreview').addEventListener('click', openPreviewModal);

    function openPreviewModal() {
        const previewModal = document.getElementById('previewModal');
        const previewDiv = document.getElementById('previewCanvas');
        previewModal.classList.remove('hidden');
        previewModal.style.display = 'flex';

        const zones = JSON.parse(zonesInput.value || '[]');
        const overlays = JSON.parse(overlayImagesJson.value || '[]');
        const mensaje = document.querySelector('textarea[name="default_message"]').value || '';
        const bgSrc = canvas.backgroundImage ? canvas.backgroundImage._element.src : null;
        const canvasW = canvas.getWidth();
        const canvasH = canvas.getHeight();
        const maxW = 800;
        const scale = canvasW > maxW ? maxW / canvasW : 1;
        const previewW = Math.round(canvasW * scale);
        const previewH = Math.round(canvasH * scale);

        let html = '<div style="position:relative;width:' + previewW + 'px;height:' + previewH + 'px;overflow:hidden;border-radius:8px;background:#1f2937;">';

        if (bgSrc) {
            html += '<img src="' + bgSrc + '" style="width:' + previewW + 'px;height:' + previewH + 'px;object-fit:cover;display:block;">';
        }

        overlays.forEach(function (o) {
            if (o._tempData) {
                html += '<img src="' + o._tempData + '" style="position:absolute;left:' + Math.round((o.x || 0) * scale) + 'px;top:' + Math.round((o.y || 0) * scale) + 'px;width:' + Math.round((o.width || 150) * scale) + 'px;transform:rotate(' + (o.rotation || 0) + 'deg);">';
            } else if (o.path) {
                const src = o.path.startsWith('http') ? o.path : @json(asset('storage/')).replace(/\/$/, '') + '/' + o.path;
                html += '<img src="' + src + '" style="position:absolute;left:' + Math.round((o.x || 0) * scale) + 'px;top:' + Math.round((o.y || 0) * scale) + 'px;width:' + Math.round((o.width || 150) * scale) + 'px;transform:rotate(' + (o.rotation || 0) + 'deg);">';
            }
        });

        const nameZone = zones.find(function (z) { return z.type === 'name'; });
        const msgZone = zones.find(function (z) { return z.type === 'message'; });
        const textZones = zones.filter(function (z) { return z.type === 'text'; });

        if (nameZone) {
            html += '<div style="position:absolute;left:' + Math.round((nameZone.x || 40) * scale) + 'px;top:' + Math.round((nameZone.y || 60) * scale) + 'px;font-size:' + Math.round((nameZone.fontSize || 30) * scale) + 'px;color:' + (nameZone.color || '#ffffff') + ';font-weight:bold;font-family:sans-serif;">' + (nameZone.zoneText || 'Ejemplo Nombre') + '</div>';
        }

        if (msgZone) {
            html += '<div style="position:absolute;left:' + Math.round((msgZone.x || 40) * scale) + 'px;top:' + Math.round((msgZone.y || 120) * scale) + 'px;font-size:' + Math.round((msgZone.fontSize || 18) * scale) + 'px;color:' + (msgZone.color || '#ffffff') + ';max-width:80%;line-height:1.45;font-family:sans-serif;">' + mensaje.replace('[NOMBRE]', nameZone?.zoneText || 'Ejemplo') + '</div>';
        }

        textZones.forEach(function (tz) {
            const textContent = (tz.zoneText || 'Texto aqui').replace('[NOMBRE]', nameZone?.zoneText || 'Ejemplo');
            html += '<div style="position:absolute;left:' + Math.round((tz.x || 40) * scale) + 'px;top:' + Math.round((tz.y || 180) * scale) + 'px;font-size:' + Math.round((tz.fontSize || 18) * scale) + 'px;color:' + (tz.color || '#ffffff') + ';max-width:80%;line-height:1.45;font-family:sans-serif;">' + textContent + '</div>';
        });

        html += '</div>';
        previewDiv.innerHTML = html;
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.add('hidden');
        document.getElementById('previewModal').style.display = 'none';
    }

    function openFullPreview() {
        const zones = JSON.parse(zonesInput.value || '[]');
        const overlays = JSON.parse(overlayImagesJson.value || '[]');
        const mensaje = document.querySelector('textarea[name="default_message"]').value || '';
        const canvasW = canvas.getWidth();
        const canvasH = canvas.getHeight();

        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = canvasW;
        exportCanvas.height = canvasH;
        const ctx = exportCanvas.getContext('2d');

        ctx.fillStyle = '#1f2937';
        ctx.fillRect(0, 0, canvasW, canvasH);

        const bgImg = canvas.backgroundImage && canvas.backgroundImage._element;
        if (bgImg) {
            ctx.drawImage(bgImg, 0, 0, canvasW, canvasH);
        }

        let loadedCount = 0;
        let totalOverlays = overlays.length;

        function checkDone() {
            loadedCount++;
            if (loadedCount >= totalOverlays) {
                renderTextZones();
            }
        }

        function renderTextZones() {
            const nameZone = zones.find(function (z) { return z.type === 'name'; });
            const msgZone = zones.find(function (z) { return z.type === 'message'; });
            const textZones = zones.filter(function (z) { return z.type === 'text'; });

            if (nameZone) {
                ctx.font = 'bold ' + (nameZone.fontSize || 28) + 'px sans-serif';
                ctx.fillStyle = nameZone.color || '#ffffff';
                ctx.fillText(nameZone.zoneText || 'Ejemplo Nombre', nameZone.x || 40, (nameZone.y || 60) + (nameZone.fontSize || 28));
            }

            if (msgZone) {
                ctx.font = (msgZone.fontSize || 18) + 'px sans-serif';
                ctx.fillStyle = msgZone.color || '#ffffff';
                const finalMsg = mensaje.replace('[NOMBRE]', nameZone?.zoneText || 'Ejemplo');
                const lines = finalMsg.split('\n');
                let y = (msgZone.y || 120) + (msgZone.fontSize || 18);
                lines.forEach(function (line) {
                    ctx.fillText(line.trim(), msgZone.x || 40, y);
                    y += (msgZone.fontSize || 18) * 1.45;
                });
            }

            textZones.forEach(function (tz) {
                ctx.font = (tz.fontSize || 18) + 'px sans-serif';
                ctx.fillStyle = tz.color || '#ffffff';
                const textContent = (tz.zoneText || 'Texto aqui').replace('[NOMBRE]', nameZone?.zoneText || 'Ejemplo');
                const lines = textContent.split('\n');
                let y = (tz.y || 180) + (tz.fontSize || 18);
                lines.forEach(function (line) {
                    ctx.fillText(line.trim(), tz.x || 40, y);
                    y += (tz.fontSize || 18) * 1.45;
                });
            });

            const dataURL = exportCanvas.toDataURL('image/png');
            const newTab = window.open();
            newTab.document.write('<html><head><title>Previsualizacion de Tarjeta</title></head><body style="margin:0;background:#0f172a;display:flex;justify-content:center;align-items:center;min-height:100vh;"><img src="' + dataURL + '" style="max-width:100%;height:auto;border-radius:8px;box-shadow:0 20px 60px rgba(0,0,0,0.5);"></body></html>');
            newTab.document.close();
        }

        if (totalOverlays === 0) {
            renderTextZones();
        } else {
            overlays.forEach(function (o) {
                const src = o._tempData
                    ? o._tempData
                    : (o.path.startsWith('http') ? o.path : @json(asset('storage/')).replace(/\/$/, '') + '/' + o.path);
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function () {
                    const w = (o.width || 150) * (o.scaleX || 1);
                    const h = (o.height || 150) * (o.scaleY || 1);
                    ctx.save();
                    ctx.translate(o.x || 0, o.y || 0);
                    ctx.rotate((o.rotation || 0) * Math.PI / 180);
                    ctx.drawImage(img, 0, 0, w, h);
                    ctx.restore();
                    checkDone();
                };
                img.onerror = function () { checkDone(); };
                img.src = src;
            });
        }
    }

    document.getElementById('previewModal').addEventListener('click', function (e) {
        if (e.target === this) closePreviewModal();
    });

    document.getElementById('btnClosePreview').addEventListener('click', closePreviewModal);
    document.getElementById('btnCerrarPreview').addEventListener('click', closePreviewModal);
    document.getElementById('btnAbrirNuevaPestana').addEventListener('click', openFullPreview);

    function syncZonesToInput() {
        const zones = canvas.getObjects('rect').map(function (rect) {
            return {
                type: rect.zoneType,
                x: Math.round(rect.left),
                y: Math.round(rect.top),
                width: Math.round(rect.width * rect.scaleX),
                height: Math.round(rect.height * rect.scaleY),
                fontSize: rect.fontSize || 28,
                color: rect.textColor || '#ffffff',
                zoneText: rect.zoneText || '',
                layer: rect.zoneLayer || 0,
            };
        });
        zonesInput.value = JSON.stringify(zones);
    }

    document.querySelector('form').addEventListener('submit', function () {
        syncZonesToInput();

        overlayImages.forEach(function (img, idx) {
            if (img._tempData) {
                img.path = 'pending_upload_' + idx;
            }
        });
        overlayImagesJson.value = JSON.stringify(overlayImages);
    });
});
</script>
@endpush