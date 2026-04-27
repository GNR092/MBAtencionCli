/*
 * Canva HBD - Canvas Editor JavaScript
 * Este archivo proporciona utilities JS adicionales para el editor canvas
 */

window.HbdCanvasEditor = {
    /**
     * Genera thumbnail del canvas usando html2canvas
     */
    async generateThumbnail(canvasElement) {
        if (typeof html2canvas === 'undefined') {
            console.warn('html2canvas no está cargado');
            return null;
        }

        try {
            const canvas = await html2canvas(canvasElement, {
                scale: 0.5,
                useCORS: true,
                allowTaint: true,
            });
            return canvas.toDataURL('image/png');
        } catch (e) {
            console.error('Error generando thumbnail:', e);
            return null;
        }
    },

    /**
     * Valida estructura del content JSON
     */
    validateContent(content) {
        if (!content || typeof content !== 'object') {
            return { valid: false, error: 'Content debe ser un objeto' };
        }

        const devices = ['desktop', 'tablet', 'mobile'];
        for (const device of devices) {
            if (content[device]) {
                if (!Array.isArray(content[device])) {
                    return { valid: false, error: `${device} debe ser un array` };
                }

                for (const section of content[device]) {
                    if (!section.height || section.height < 50) {
                        return { valid: false, error: 'Cada sección debe tener height > 50' };
                    }
                }
            }
        }

        return { valid: true };
    },

    /**
     * Normaliza posición para evitar elementos fuera del canvas
     */
    clampPosition(top, left, width) {
        return {
            top: Math.max(0, Math.min(95, top)),
            left: Math.max(0, Math.min(95, left)),
            width: Math.max(5, Math.min(100, width)),
        };
    },

    /**
     * Exporta el content como JSON string
     */
    exportContent(sections) {
        return JSON.stringify({ desktop: sections });
    },

    /**
     * Importa content desde JSON string
     */
    importContent(jsonString) {
        try {
            const content = JSON.parse(jsonString);
            return this.validateContent(content).valid ? content : null;
        } catch (e) {
            return null;
        }
    },
};
