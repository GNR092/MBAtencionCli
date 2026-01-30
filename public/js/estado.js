document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.estado-select');

    selects.forEach(select => {
        select.addEventListener('change', async function() {
            const id = this.dataset.id;
            const estado = this.value;

            try {
                const response = await fetch(`/cuentasporpagar/${id}/estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ estado })
                });

                const data = await response.json();

                if (data.success) {
                    this.style.backgroundColor = estado === 'pagado' ? '#bbf7d0' : '#fde68a'; // verde o amarillo
                    this.disabled = true; // evita cambios adicionales
                    alert('✅ Estado actualizado correctamente a: ' + estado);
                } else {
                    alert('⚠️ No se pudo actualizar el estado: ' + (data.message ?? 'Error desconocido'));
                }
            } catch (error) {
                console.error(error);
                alert('❌ Error al conectar con el servidor');
            }
        });
    });
});



