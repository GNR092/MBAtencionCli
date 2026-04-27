<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Canvas de Cumpleaños</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">

<div class="flex h-screen overflow-hidden" x-data="canvasEditor()">

    {{-- SIDEBAR --}}
    <aside class="w-72 bg-gray-800 border-r border-gray-700 flex flex-col">

        <div class="p-4 border-b border-gray-700">
            <h2 class="text-dorado-400 text-xs font-bold uppercase tracking-widest">Componentes</h2>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">

            <button @click="addText()"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 transition text-left">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                </svg>
                <span class="text-sm">Texto</span>
            </button>

            <button @click="showImagePicker = true"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 transition text-left">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm">Imagen</span>
            </button>

            <button @click="addButton()"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 transition text-left">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2z"/>
                </svg>
                <span class="text-sm">Botón</span>
            </button>

            <button @click="addBackground()"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 transition text-left">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"/>
                </svg>
                <span class="text-sm">Fondo</span>
            </button>

            <button @click="addShape()"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 transition text-left">
                <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V8a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                </svg>
                <span class="text-sm">Forma</span>
            </button>

        </div>

        <div class="p-4 border-t border-gray-700 space-y-2">
            <button @click="saveTemplate()"
                class="w-full py-2.5 px-4 bg-[#d8c495] hover:bg-[#c4b07a] text-gray-900 font-semibold rounded-xl transition text-sm">
                💾 Guardar
            </button>
            <button @click="previewTemplate()"
                class="w-full py-2.5 px-4 bg-gray-700 hover:bg-gray-600 rounded-xl transition text-sm">
                👁 Vista previa
            </button>
        </div>
    </aside>

    {{-- CANVAS AREA --}}
    <div class="flex-1 flex flex-col">

        <header class="h-14 bg-gray-800 border-b border-gray-700 flex items-center px-4 gap-4">
            <div class="flex gap-1 bg-gray-700 rounded-lg p-1">
                <button @click="activeDevice = 'desktop'"
                    :class="activeDevice === 'desktop' ? 'bg-[#d8c495] text-gray-900' : 'text-gray-400 hover:text-white'"
                    class="px-3 py-1 rounded-md text-xs font-semibold transition">Desktop</button>
                <button @click="activeDevice = 'tablet'"
                    :class="activeDevice === 'tablet' ? 'bg-[#d8c495] text-gray-900' : 'text-gray-400 hover:text-white'"
                    class="px-3 py-1 rounded-md text-xs font-semibold transition">Tablet</button>
                <button @click="activeDevice = 'mobile'"
                    :class="activeDevice === 'mobile' ? 'bg-[#d8c495] text-gray-900' : 'text-gray-400 hover:text-white'"
                    class="px-3 py-1 rounded-md text-xs font-semibold transition">Mobile</button>
            </div>
            <div class="flex-1"></div>
            <input type="text" x-model="templateName"
                class="bg-gray-700 text-white text-sm px-3 py-1.5 rounded-lg border border-gray-600 focus:border-[#d8c495] focus:outline-none w-64"
                placeholder="Nombre de la plantilla">
        </header>

        <div class="flex-1 overflow-auto bg-gray-900 p-8 flex justify-center">
            <div class="relative bg-white shadow-2xl"
                :class="{
                    'w-[600px]': activeDevice === 'desktop',
                    'w-[768px]': activeDevice === 'tablet',
                    'w-[375px]': activeDevice === 'mobile'
                }"
                :style="'min-height: 500px;'">

                <template x-for="(section, sIdx) in sections" :key="sIdx">
                    <div class="relative bg-gray-800 border border-dashed border-gray-600 m-2"
                        :style="'min-height: ' + (section.height || 400) + 'px;'"
                        :class="{'border-blue-500': activeSection === sIdx}">

                        <div class="absolute top-0 left-0 bg-gray-700 text-xs text-gray-300 px-2 py-0.5 rounded-br opacity-0 hover:opacity-100 transition cursor-move"
                            style="z-index: 10;"
                            @mousedown="startDragSection($event, sIdx)">☰ <span x-text="section.name || 'Sección'"></span></div>

                        <button @click="removeSection(sIdx)"
                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded text-xs opacity-0 group-hover:opacity-100 transition"
                            style="z-index: 10;">✕</button>

                        <template x-for="(comp, cIdx) in section.components" :key="cIdx">
                            <div class="absolute cursor-move"
                                :style="'top: ' + comp.top + '%; left: ' + comp.left + '%; width: ' + comp.width + '%;' + (comp.height ? ' height: ' + comp.height + '%;' : '')"
                                :class="{'ring-2 ring-blue-500': activeComponent === cIdx && activeSection === sIdx}"
                                @mousedown="startDrag($event, sIdx, cIdx)"
                                @click.stop="activeComponent = cIdx; activeSection = sIdx;">

                                <template x-if="comp.type === 'text'">
                                    <div :style="'color: ' + (comp.color || '#ffffff') + '; font-size: ' + (comp.fontSize || 16) + 'px; font-weight: ' + (comp.fontWeight || 'normal') + '; text-align: ' + (comp.textAlign || 'left') + '; font-family: ' + (comp.fontFamily || 'Arial') + ';'"
                                        contenteditable="true"
                                        @blur="comp.content = $event.target.innerText"
                                        x-text="comp.content">
                                    </div>
                                </template>

                                <template x-if="comp.type === 'image'">
                                    <img :src="comp.url" :style="'width: 100%; height: ' + (comp.height || 'auto') + '; object-fit: cover;'"
                                        @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><rect fill=%22%23333%22 width=%22100%22 height=%22100%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-size=%2212%22> imagen</text></svg>'" />
                                </template>

                                <template x-if="comp.type === 'button'">
                                    <div :style="'background: ' + (comp.bgColor || '#d8c495') + '; color: ' + (comp.textColor || '#1a1a2e') + '; padding: 10px 20px; border-radius: 8px; display: inline-block; font-weight: bold; text-align: center; width: 100%;'"
                                        contenteditable="true"
                                        @blur="comp.text = $event.target.innerText"
                                        x-text="comp.text">
                                    </div>
                                </template>

                                <template x-if="comp.type === 'shape'">
                                    <div :style="'background: ' + (comp.fill || '#d8c495') + '; border-radius: ' + (comp.shapeType === 'circle' ? '50%' : comp.shapeType === 'rounded' ? '16px' : '0') + ';'"></div>
                                </template>

                                <template x-if="comp.type === 'bg'">
                                    <div class="absolute inset-0 -z-10" :style="'background: ' + (comp.bgType === 'color' ? comp.bgValue : (comp.bgType === 'image' ? 'url(' + comp.bgValue + ')' : 'linear-gradient(' + comp.bgValue + ')')) + ';'"></div>
                                </template>

                                <button @click.stop="removeComponent(sIdx, cIdx)"
                                    class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full text-[10px] opacity-0 transition"
                                    :class="{'opacity-100': activeComponent === cIdx}">✕</button>

                                <div class="absolute -bottom-5 left-0 w-full text-center opacity-0 transition"
                                    :class="{'opacity-100': activeComponent === cIdx}"
                                    style="font-size: 8px; color: #666;">
                                    <span x-text="comp.type"></span>
                                </div>
                            </div>
                        </template>

                        <div class="absolute bottom-1 right-1 flex gap-1 opacity-0 hover:opacity-100 transition">
                            <button @click="addComponentToSection(sIdx)"
                                class="w-6 h-6 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs">+</button>
                        </div>
                    </div>
                </template>

                <button @click="addSection()"
                    class="w-full py-4 border-2 border-dashed border-gray-600 hover:border-gray-400 text-gray-500 hover:text-gray-300 transition text-sm">
                    + Agregar sección
                </button>

            </div>
        </div>
    </div>

    {{-- PROPERTIES PANEL --}}
    <aside class="w-72 bg-gray-800 border-l border-gray-700 flex flex-col"
        x-show="activeComponent !== null || activeSection !== null"
        x-cloak>
        <div class="p-4 border-b border-gray-700">
            <h2 class="text-dorado-400 text-xs font-bold uppercase tracking-widest">Propiedades</h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-4">

            <template x-if="activeComponent !== null">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Color</label>
                    <input type="color" x-model="sections[activeSection].components[activeComponent].color"
                        class="w-full h-8 rounded cursor-pointer bg-transparent">
                </div>
            </template>

            <template x-if="activeComponent !== null">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tamaño de fuente</label>
                    <input type="range" min="10" max="72" x-model="sections[activeSection].components[activeComponent].fontSize"
                        class="w-full">
                    <span class="text-xs text-gray-400" x-text="sections[activeSection]?.components[activeComponent]?.fontSize + 'px'"></span>
                </div>
            </template>

            <template x-if="activeComponent !== null">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Ancho (%)</label>
                    <input type="range" min="5" max="100" x-model="sections[activeSection].components[activeComponent].width"
                        class="w-full">
                    <span class="text-xs text-gray-400" x-text="sections[activeSection]?.components[activeComponent]?.width + '%'"></span>
                </div>
            </template>

            <template x-if="activeComponent !== null">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Posición arriba (%)</label>
                    <input type="range" min="0" max="95" x-model="sections[activeSection].components[activeComponent].top"
                        class="w-full">
                </div>
            </template>

            <template x-if="activeComponent !== null">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Posición izquierda (%)</label>
                    <input type="range" min="0" max="95" x-model="sections[activeSection].components[activeComponent].left"
                        class="w-full">
                </div>
            </template>

            <template x-if="sections[activeSection]?.components[activeComponent]?.type === 'text'">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tipo de texto</label>
                    <select x-model="sections[activeSection].components[activeComponent].subtype"
                        class="w-full bg-gray-700 text-white text-sm px-3 py-2 rounded-lg border border-gray-600">
                        <option value="body">Párrafo</option>
                        <option value="heading">Encabezado</option>
                    </select>
                </div>
            </template>

            <template x-if="sections[activeSection]?.components[activeComponent]?.type === 'button'">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Color del botón</label>
                    <input type="color" x-model="sections[activeSection].components[activeComponent].bgColor"
                        class="w-full h-8 rounded cursor-pointer bg-transparent">
                </div>
            </template>

            <template x-if="sections[activeSection]">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Altura de sección</label>
                    <input type="number" min="100" max="1000" x-model="sections[activeSection].height"
                        class="w-full bg-gray-700 text-white text-sm px-3 py-2 rounded-lg border border-gray-600">
                </div>
            </template>

        </div>
    </aside>

    {{-- IMAGE PICKER MODAL --}}
    <div x-show="showImagePicker" x-cloak
        class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
        @keydown.escape.window="showImagePicker = false"
        @click.self="showImagePicker = false">

        <div class="bg-gray-800 rounded-2xl p-6 w-[500px] max-w-full">
            <h3 class="text-white font-semibold mb-4">Insertar imagen</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">URL de imagen</label>
                    <input type="url" x-model="imageUrl"
                        class="w-full bg-gray-700 text-white text-sm px-3 py-2 rounded-lg border border-gray-600"
                        placeholder="https://...">
                </div>
                <div class="flex gap-2 pt-2">
                    <button @click="addImageFromUrl()"
                        class="flex-1 py-2 bg-[#d8c495] text-gray-900 font-semibold rounded-lg">Usar URL</button>
                    <label class="flex-1 py-2 bg-gray-700 text-white text-center rounded-lg cursor-pointer hover:bg-gray-600 transition">
                        Subir
                        <input type="file" accept="image/*" class="hidden" @change="uploadImage($event)">
                    </label>
                    <button @click="showImagePicker = false"
                        class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <div x-show="toast.show" x-cloak
        class="fixed bottom-6 right-6 px-4 py-3 rounded-xl shadow-lg z-50"
        :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
        x-text="toast.message">
    </div>

</div>

<script>
function canvasEditor() {
    return {
        sections: [],
        activeDevice: 'desktop',
        activeSection: null,
        activeComponent: null,
        templateName: 'Feliz Cumpleaños',
        templateId: null,
        showImagePicker: false,
        imageUrl: '',
        toast: { show: false, message: '', type: 'success' },

        init() {
            const content = document.getElementById('template-content');
            if (content && content.value) {
                try {
                    const data = JSON.parse(content.value);
                    this.sections = data.desktop || data.desktop || [];
                    this.templateName = data.name || 'Feliz Cumpleaños';
                    this.templateId = data.id || null;
                } catch (e) {
                    this.sections = [];
                }
            }
            if (this.sections.length === 0) {
                this.sections = [{
                    name: 'Hero',
                    height: 500,
                    bgType: 'color',
                    bgValue: '#1a1a2e',
                    components: [
                        {
                            type: 'text',
                            subtype: 'heading',
                            top: 30,
                            left: 10,
                            width: 80,
                            content: '¡Feliz cumpleaños, {nombre}!',
                            color: '#ffffff',
                            fontSize: 48,
                            fontWeight: 'bold',
                            textAlign: 'center',
                        },
                        {
                            type: 'text',
                            subtype: 'body',
                            top: 55,
                            left: 15,
                            width: 70,
                            content: 'Te deseamos un día lleno de alegría y bendiciones.',
                            color: '#d8c495',
                            fontSize: 20,
                            textAlign: 'center',
                        }
                    ]
                }];
            }
        },

        addSection() {
            this.sections.push({
                name: 'Nueva sección',
                height: 300,
                bgType: 'color',
                bgValue: '#2d2d44',
                components: []
            });
        },

        removeSection(sIdx) {
            this.sections.splice(sIdx, 1);
        },

        addText() {
            const sIdx = this.activeSection !== null ? this.activeSection : this.sections.length - 1;
            if (sIdx < 0) { this.addSection(); return; }
            this.sections[sIdx].components.push({
                type: 'text',
                subtype: 'body',
                top: 20,
                left: 10,
                width: 80,
                content: 'Nuevo texto',
                color: '#ffffff',
                fontSize: 16,
                textAlign: 'left',
            });
        },

        addButton() {
            const sIdx = this.activeSection !== null ? this.activeSection : this.sections.length - 1;
            if (sIdx < 0) { this.addSection(); return; }
            this.sections[sIdx].components.push({
                type: 'button',
                top: 70,
                left: 25,
                width: 50,
                text: 'Click aquí',
                bgColor: '#d8c495',
                textColor: '#1a1a2e',
            });
        },

        addImageFromUrl() {
            if (!this.imageUrl) return;
            const sIdx = this.activeSection !== null ? this.activeSection : this.sections.length - 1;
            if (sIdx < 0) { this.addSection(); return; }
            this.sections[sIdx].components.push({
                type: 'image',
                top: 10,
                left: 35,
                width: 30,
                height: 20,
                url: this.imageUrl,
            });
            this.imageUrl = '';
            this.showImagePicker = false;
        },

        async uploadImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            try {
                const res = await fetch('{{ route("hbd.canvas.media") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData,
                });
                const data = await res.json();
                if (data.success) {
                    this.imageUrl = data.url;
                    this.addImageFromUrl();
                }
            } catch (e) {
                this.showToast('Error al subir imagen', 'error');
            }
        },

        addShape() {
            const sIdx = this.activeSection !== null ? this.activeSection : this.sections.length - 1;
            if (sIdx < 0) { this.addSection(); return; }
            this.sections[sIdx].components.push({
                type: 'shape',
                shapeType: 'rounded',
                top: 80,
                left: 40,
                width: 20,
                height: 10,
                fill: '#d8c495',
            });
        },

        addBackground() {
            const sIdx = this.activeSection !== null ? this.activeSection : this.sections.length - 1;
            if (sIdx < 0) { this.addSection(); return; }
            this.sections[sIdx].components.push({
                type: 'bg',
                bgType: 'color',
                bgValue: '#1a1a2e',
            });
        },

        addComponentToSection(sIdx) {
            this.addText();
        },

        removeComponent(sIdx, cIdx) {
            this.sections[sIdx].components.splice(cIdx, 1);
            this.activeComponent = null;
        },

        startDrag(e, sIdx, cIdx) {
            e.preventDefault();
            const comp = this.sections[sIdx].components[cIdx];
            const startX = e.clientX;
            const startY = e.clientY;
            const startTop = comp.top;
            const startLeft = comp.left;

            const onMove = (moveEvent) => {
                const rect = e.target.closest('.bg-gray-800').getBoundingClientRect();
                const dx = moveEvent.clientX - startX;
                const dy = moveEvent.clientY - startY;
                comp.left = Math.max(0, Math.min(95, startLeft + (dx / rect.width) * 100));
                comp.top = Math.max(0, Math.min(95, startTop + (dy / rect.height) * 100));
            };

            const onUp = () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

        startDragSection(e, sIdx) {
            e.preventDefault();
        },

        async saveTemplate() {
            try {
                const res = await fetch('{{ route("hbd.canvas.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        id: this.templateId,
                        name: this.templateName,
                        content: { desktop: this.sections, tablet: [], mobile: [] },
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.templateId = data.template.id;
                    this.templateName = data.template.name;
                    this.showToast('Plantilla guardada correctamente');
                }
            } catch (e) {
                this.showToast('Error al guardar', 'error');
            }
        },

        previewTemplate() {
            const content = encodeURIComponent(JSON.stringify({ desktop: this.sections }));
            window.open('{{ route("hbd.canvas.preview") }}?content=' + content + '&nombre=Nombre+Cumpleañero', '_blank');
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        }
    }
}
</script>

</body>
</html>
