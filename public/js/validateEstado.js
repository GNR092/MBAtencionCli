document.addEventListener('DOMContentLoaded', () => {
    const activo = document.getElementById('activo');
    const inactivo = document.getElementById('inactivo');
    const errorDiv = document.getElementById('estado-error');
    const form = document.querySelector('form'); // asumiendo que tienes un <form>

    form.addEventListener('submit', (e) => {
        errorDiv.classList.add('hidden');
        errorDiv.textContent = '';

        if (activo.checked && inactivo.checked) {
            e.preventDefault();
            errorDiv.textContent = 'No se puede seleccionar ambos estados.';
            errorDiv.classList.remove('hidden');
        } else if (!activo.checked && !inactivo.checked) {
            e.preventDefault();
            errorDiv.textContent = 'Debe seleccionar un estado.';
            errorDiv.classList.remove('hidden');
        }
    });
});