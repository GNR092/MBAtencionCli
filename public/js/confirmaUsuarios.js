function loginUsuario(event) {
    event.preventDefault();

    const password = document.getElementById('password').value.trim();
    const email = document.getElementById('email').value.trim();
    const mensajeDiv = document.getElementById('loginMensaje');

    if (!password || !email) {
        mensajeDiv.innerHTML = '<p class="text-red-500">Por favor, complete todos los campos.</p>';
        return;
    }

    fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin',
        body: JSON.stringify({ password, email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mensajeDiv.innerHTML = '<p class="text-green-500">' + data.message + '</p>';

            if (data.rol === 'administrador') {
                window.location.href = '/cuentas-por-pagar';
            } else if (data.rol === 'usuario') {
                window.location.href = '/vista-usuario';
            }
        } else {
            mensajeDiv.innerHTML = '<p class="text-red-500">' + data.message + '</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mensajeDiv.innerHTML = '<p class="text-red-500">Error en el servidor.</p>';
    });
}