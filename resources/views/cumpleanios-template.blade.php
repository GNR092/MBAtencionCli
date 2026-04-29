@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="page-title">Editor de Plantilla de Cumpleanos</h1>
            <a href="{{ route('usuarios.cumpleanios') }}" class="btn-dorado">Volver</a>
        </div>

        <form action="{{ route('cumpleanios.template.save') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-white/80 mb-1">Nombre de plantilla</label>
                    <input name="name" required value="{{ old('name', $template->name ?? 'Plantilla General') }}" class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-white/80 mb-1">Imagen de fondo</label>
                    <input type="file" accept="image/*" name="background" id="backgroundInput" class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white">
                    @if(!empty($template?->background_path))
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-white/70">
                            <input type="checkbox" name="remove_background" value="1" class="rounded border-white/30 bg-white/10">
                            Quitar imagen actual
                        </label>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm text-white/80 mb-1">Mensaje generico</label>
                <textarea name="default_message" rows="4" class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white">{{ old('default_message', $template->default_message ?? 'Feliz cumpleanos [NOMBRE], te deseamos un excelente dia.') }}</textarea>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                <div class="flex gap-2 mb-3">
                    <button type="button" id="addNameZone" class="btn-dorado">Agregar zona nombre</button>
                    <button type="button" id="addMessageZone" class="btn-dorado">Agregar zona mensaje</button>
                    <button type="button" id="deleteZone" class="px-4 py-2 rounded-lg border border-red-400/40 text-red-300 bg-red-500/10 hover:bg-red-500/20 disabled:opacity-40 disabled:cursor-not-allowed" disabled>Eliminar zona</button>
                </div>
                <div class="mb-3 flex items-center gap-3">
                    <label class="btn-dorado cursor-pointer">
                        <span>Agregar imagen adicional</span>
                        <input type="file" accept="image/*" id="overlayInput" class="hidden" multiple>
                    </label>
                </div>
                <canvas id="editorCanvas" width="960" height="540" class="w-full rounded-lg border border-white/20 bg-black/30"></canvas>
            </div>

            <div id="overlayControls" class="hidden bg-white/5 border border-white/10 rounded-xl p-4">
                <h3 class="text-sm text-[#d8c495] font-bold mb-3">Imagenes agregadas</h3>
                <div id="overlayList" class="space-y-3"></div>
            </div>

            <input type="hidden" id="zonesJson" name="zones_json" value="{{ old('zones_json', json_encode($template->zones_json ?? [])) }}">
            <input type="hidden" id="overlayImagesJson" name="overlay_images_json" value="{{ old('overlay_images_json', json_encode($template->overlay_images ?? [])) }}">

            <button class="btn-dorado" type="button" onclick="openPreviewModal()">Previsualizar</button>
            <button class="btn-dorado" type="submit">Guardar plantilla activa</button>
        </form>

        <!-- Modal Previsualizar -->
        <div id="previewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
            <div class="bg-[#1a1a2e] border border-[#d8c495]/30 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-white/10">
                    <h3 class="text-[#d8c495] font-bold text-lg">Previsualizacion de Plantilla</h3>
                    <button type="button" onclick="closePreviewModal()" class="text-white/50 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                <div class="p-5" id="previewContent">
                    <div id="previewCanvas" class="w-full rounded-lg overflow-hidden" style="aspect-ratio:960/540;background:#1f2937;"></div>
                </div>
                <div class="flex justify-end p-5 border-t border-white/10">
                    <button type="button" onclick="closePreviewModal()" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white/70 text-sm">Cerrar</button>
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
    const overlayControls = document.getElementById('overlayControls');
    const overlayList = document.getElementById('overlayList');
    const overlayImagesJson = document.getElementById('overlayImagesJson');

    let overlayImages = JSON.parse(overlayImagesJson.value || '[]');
    let fabricOverlays = [];

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
        return rect;
    }

    function drawZoneLabel(rect) {
        const label = new fabric.Text(rect.zoneType === 'name' ? 'NOMBRE' : 'MENSAJE', {
            left: rect.left + 10,
            top: rect.top + 20,
            fontSize: 18,
            fill: rect.zoneColor,
            selectable: false,
            evented: false,
        });
        rect._label = label;
        canvas.add(label);
    }

    function syncLabels() {
        canvas.getObjects('rect').forEach(function (rect) {
            if (!rect._label) return;
            rect._label.set({ left: rect.left + 10, top: rect.top + 20 });
        });
        canvas.renderAll();
    }

    function addZone(type) {
        const color = type === 'name' ? '#d8c495' : '#34d399';
        const rect = zoneRect(type, color);
        canvas.add(rect);
        drawZoneLabel(rect);
        canvas.setActiveObject(rect);
        updateDeleteButtonState();
    }

    function updateDeleteButtonState() {
        const activeObject = canvas.getActiveObject();
        deleteZoneBtn.disabled = !activeObject;
    }

    function deleteSelectedZone() {
        const activeObject = canvas.getActiveObject();
        if (!activeObject) return;
        if (activeObject._label) canvas.remove(activeObject._label);
        canvas.remove(activeObject);
        canvas.discardActiveObject();
        canvas.renderAll();
        updateDeleteButtonState();
    }

    document.getElementById('addNameZone').addEventListener('click', function () { addZone('name'); });
    document.getElementById('addMessageZone').addEventListener('click', function () { addZone('message'); });
    deleteZoneBtn.addEventListener('click', deleteSelectedZone);
    canvas.on('object:moving', syncLabels);
    canvas.on('object:scaling', syncLabels);
    canvas.on('selection:created', updateDeleteButtonState);
    canvas.on('selection:updated', updateDeleteButtonState);
    canvas.on('selection:cleared', updateDeleteButtonState);

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Delete' && event.key !== 'Backspace') return;
        const targetTag = (event.target && event.target.tagName) ? event.target.tagName.toLowerCase() : '';
        if (targetTag === 'input' || targetTag === 'textarea') return;
        if (canvas.getActiveObject()) {
            event.preventDefault();
            deleteSelectedZone();
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
        const rect = zoneRect(z.type || 'name', z.color || '#d8c495', z.x || 60, z.y || 60);
        rect.set({ width: z.width || 260, height: z.height || 70 });
        rect.fontSize = z.fontSize || 28;
        rect.textColor = z.color || '#ffffff';
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
                fabricImg.set({
                    left: imgData.x || 0,
                    top: imgData.y || 0,
                    scaleX: imgData.scaleX || 1,
                    scaleY: imgData.scaleY || 1,
                    angle: imgData.rotation || 0,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                });
                fabricImg._overlayIdx = idx;
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
            return '<div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">' +
                '<span class="text-xs text-white/50">' + (img.originalFilename || 'Imagen ' + (idx + 1)) + '</span>' +
                '<div class="flex items-center gap-1 ml-auto">' +
                    '<label class="text-xs text-white/60">X <input type="number" data-idx="' + idx + '" data-field="x" value="' + (img.x || 0) + '" class="w-16 bg-white/10 border border-white/20 rounded px-1 py-0.5 text-white text-xs"></label>' +
                    '<label class="text-xs text-white/60">Y <input type="number" data-idx="' + idx + '" data-field="y" value="' + (img.y || 0) + '" class="w-16 bg-white/10 border border-white/20 rounded px-1 py-0.5 text-white text-xs"></label>' +
                    '<label class="text-xs text-white/60">Rot <input type="number" data-idx="' + idx + '" data-field="rotation" value="' + (img.rotation || 0) + '" class="w-16 bg-white/10 border border-white/20 rounded px-1 py-0.5 text-white text-xs"></label>' +
                    '<label class="text-xs text-white/60">Rot <input type="number" data-idx="' + idx + '" data-field="scaleX" value="' + (img.scaleX || 1) + '" class="w-16 bg-white/10 border border-white/20 rounded px-1 py-0.5 text-white text-xs"></label>' +
                '</div>' +
                '<button type="button" data-remove="' + idx + '" class="text-red-400 hover:text-red-300 text-xs px-2">Quitar</button>' +
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
        fabricImg.set({
            left: imgData.x || 0,
            top: imgData.y || 0,
            angle: imgData.rotation || 0,
            scaleX: imgData.scaleX || 1,
            scaleY: imgData.scaleY || 1,
        });
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
                        width: fabricImg.width,
                        height: fabricImg.height,
                        rotation: 0,
                        scaleX: 1,
                        scaleY: 1,
                        originalFilename: file.name,
                        _tempData: e.target.result,
                    });

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
            overlayImages[obj._overlayIdx].scaleX = parseFloat(obj.scaleX.toFixed(3));
            overlayImages[obj._overlayIdx].scaleY = parseFloat(obj.scaleY.toFixed(3));
            overlayImagesJson.value = JSON.stringify(overlayImages);
            renderOverlayList();
        }
    });

    loadOverlayImages();

    function openPreviewModal() {
        const previewModal = document.getElementById('previewModal');
        const previewDiv = document.getElementById('previewCanvas');
        previewModal.classList.remove('hidden');
        previewModal.style.display = 'flex';

        const zones = JSON.parse(zonesInput.value || '[]');
        const overlays = JSON.parse(overlayImagesJson.value || '[]');
        const mensaje = document.querySelector('textarea[name="default_message"]').value || '';
        const bgSrc = canvas.backgroundImage ? canvas.backgroundImage._element.src : null;

        let html = '<div style="position:relative;width:100%;aspect-ratio:960/540;overflow:hidden;border-radius:8px;background:#1f2937;">';

        if (bgSrc) {
            html += '<img src="' + bgSrc + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
        }

        overlays.forEach(function (o) {
            if (o._tempData) {
                html += '<img src="' + o._tempData + '" style="position:absolute;left:' + (o.x || 0) + 'px;top:' + (o.y || 0) + 'px;width:' + (o.width || 150) + 'px;transform:rotate(' + (o.rotation || 0) + 'deg);">';
            } else if (o.path) {
                const src = o.path.startsWith('http') ? o.path : @json(asset('storage/')).replace(/\/$/, '') + '/' + o.path;
                html += '<img src="' + src + '" style="position:absolute;left:' + (o.x || 0) + 'px;top:' + (o.y || 0) + 'px;width:' + (o.width || 150) + 'px;transform:rotate(' + (o.rotation || 0) + 'deg);">';
            }
        });

        const nameZone = zones.find(function (z) { return z.type === 'name'; });
        const msgZone = zones.find(function (z) { return z.type === 'message'; });

        if (nameZone) {
            html += '<div style="position:absolute;left:' + (nameZone.x || 40) + 'px;top:' + (nameZone.y || 60) + 'px;font-size:' + (nameZone.fontSize || 30) + 'px;color:' + (nameZone.color || '#ffffff') + ';font-weight:bold;font-family:sans-serif;">Ejemplo Nombre</div>';
        }

        if (msgZone) {
            html += '<div style="position:absolute;left:' + (msgZone.x || 40) + 'px;top:' + (msgZone.y || 120) + 'px;font-size:' + (msgZone.fontSize || 18) + 'px;color:' + (msgZone.color || '#ffffff') + ';max-width:80%;line-height:1.45;font-family:sans-serif;">' + mensaje.replace('[NOMBRE]', 'Ejemplo') + '</div>';
        }

        html += '</div>';
        previewDiv.innerHTML = html;
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.add('hidden');
        document.getElementById('previewModal').style.display = 'none';
    }

    document.getElementById('previewModal').addEventListener('click', function (e) {
        if (e.target === this) closePreviewModal();
    });

    document.querySelector('form').addEventListener('submit', function () {
        const zones = canvas.getObjects('rect').map(function (rect) {
            return {
                type: rect.zoneType,
                x: Math.round(rect.left),
                y: Math.round(rect.top),
                width: Math.round(rect.width * rect.scaleX),
                height: Math.round(rect.height * rect.scaleY),
                fontSize: rect.fontSize || 28,
                color: rect.textColor || '#ffffff',
            };
        });
        zonesInput.value = JSON.stringify(zones);

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