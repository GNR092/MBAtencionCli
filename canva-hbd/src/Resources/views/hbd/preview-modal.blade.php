<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vista previa del correo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-[600px] w-full">
        <div class="p-6 bg-gray-800 border-b border-gray-700 flex items-center justify-between">
            <h3 class="text-white font-semibold">Vista previa del correo</h3>
            <button onclick="window.close()" class="text-gray-400 hover:text-white text-sm">✕ Cerrar</button>
        </div>
        <div class="p-0">
            <iframe id="preview-frame" class="w-full h-[600px] border-0" src=""></iframe>
        </div>
    </div>

    <script>
        const params = new URLSearchParams(window.location.search);
        const content = params.get('content');
        const nombre = params.get('nombre') || 'Nombre del Cumpleañero';

        if (content) {
            try {
                const data = JSON.parse(decodeURIComponent(content));
                const sections = data.desktop || data.desktop || [];

                let html = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <style>
                            * { margin: 0; padding: 0; box-sizing: border-box; }
                            body { background: #f0f0f0; }
                            .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                        </style>
                    </head>
                    <body>
                        <div class="email-container">
                `;

                for (const section of sections) {
                    html += `<div style="position: relative; min-height: ${section.height || 400}px; width: 100%;`;
                    if ((section.bgType || 'color') === 'image') {
                        html += `background-image: url('${section.bgValue || ''}'); background-size: cover; background-position: center;`;
                    } else {
                        html += `background: ${section.bgValue || '#1a1a2e'};`;
                    }
                    html += `">`;

                    for (const comp of (section.components || [])) {
                        const top = comp.top || 0;
                        const left = comp.left || 0;
                        const width = comp.width || 100;

                        if (comp.type === 'text') {
                            const contentText = (comp.content || '').replace('{nombre}', `<strong>${nombre}</strong>`);
                            html += `<div style="position: absolute; top: ${top}%; left: ${left}%; width: ${width}%; color: ${comp.color || '#ffffff'}; font-size: ${comp.fontSize || 16}px; font-weight: ${comp.fontWeight || 'normal'}; text-align: ${comp.textAlign || 'left'}; font-family: ${comp.fontFamily || 'Arial, sans-serif'}; line-height: 1.2;">${contentText}</div>`;
                        } else if (comp.type === 'image') {
                            html += `<img src="${comp.url || ''}" style="position: absolute; top: ${top}%; left: ${left}%; width: ${width}%; height: ${comp.height || 'auto'}; object-fit: cover;" onerror="this.style.display='none'" />`;
                        } else if (comp.type === 'button') {
                            html += `<a href="${comp.href || '#'}" style="position: absolute; top: ${top}%; left: ${left}%; width: ${width}%; display: inline-block; background: ${comp.bgColor || '#d8c495'}; color: ${comp.textColor || '#1a1a2e'}; padding: 10px 20px; border-radius: 8px; font-weight: bold; text-align: center; text-decoration: none;">${comp.text || 'Click aquí'}</a>`;
                        } else if (comp.type === 'shape') {
                            let style = `position: absolute; top: ${top}%; left: ${left}%; width: ${width}%; height: ${comp.height || 20}%; background: ${comp.fill || '#d8c495'};`;
                            if (comp.shapeType === 'circle') style += 'border-radius: 50%;';
                            else if (comp.shapeType === 'rounded') style += 'border-radius: 16px;';
                            html += `<div style="${style}"></div>`;
                        }
                    }

                    html += `</div>`;
                }

                html += `
                        </div>
                    </body>
                    </html>
                `;

                document.getElementById('preview-frame').srcdoc = html;
            } catch (e) {
                document.getElementById('preview-frame').srcdoc = '<p style="color:red;">Error al cargar preview</p>';
            }
        }
    </script>

</body>
</html>
