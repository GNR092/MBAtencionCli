const loginForm = document.getElementById('login-form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const rememberInput = document.getElementById('remember');
const loginMessage = document.getElementById('loginMensaje');
const submitButton = document.getElementById('submitLoginButton');
const togglePasswordButton = document.getElementById('togglePassword');
const emailError = document.getElementById('email-error');
const passwordError = document.getElementById('password-error');
const interactiveControls = [emailInput, passwordInput, rememberInput, togglePasswordButton].filter(Boolean);

let cooldownTimerId = null;
let cooldownRemainingSeconds = 0;

function setMessage(type, message) {
    const colorClass = type === 'success' ? 'text-green-400' : 'text-red-300';
    loginMessage.innerHTML = '<p class="' + colorClass + '">' + message + '</p>';
}

function clearFieldError(input, errorElement) {
    input.classList.remove('input-error');
    input.setAttribute('aria-invalid', 'false');
    errorElement.dataset.visible = 'false';
    errorElement.textContent = '';
}

function setFieldError(input, errorElement, message) {
    input.classList.add('input-error');
    input.setAttribute('aria-invalid', 'true');
    errorElement.dataset.visible = 'true';
    errorElement.textContent = message;
}

function clearAllFieldErrors() {
    clearFieldError(emailInput, emailError);
    clearFieldError(passwordInput, passwordError);
}

function setLoadingState(isLoading) {
    submitButton.disabled = isLoading;
    submitButton.classList.toggle('btn-loading', isLoading);
    submitButton.setAttribute('aria-busy', isLoading ? 'true' : 'false');

    if (isLoading) {
        submitButton.innerHTML =
            '<span class="inline-flex items-center justify-center gap-2">' +
            '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.35" stroke-width="3"></circle>' +
            '<path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>' +
            '</svg>' +
            'Ingresando...' +
            '</span>';
        return;
    }

    submitButton.innerHTML =
        '<span class="block group-hover:hidden">INGRESAR</span>' +
        '<span class="hidden group-hover:block">QUIERO MIS RENTAS!</span>';
}

function setControlsDisabled(disabled) {
    interactiveControls.forEach((control) => {
        control.disabled = disabled;
    });
}

function startCooldown(seconds) {
    if (cooldownTimerId) {
        clearInterval(cooldownTimerId);
    }

    cooldownRemainingSeconds = Math.max(1, Number.parseInt(String(seconds), 10) || 60);
    setControlsDisabled(true);

    const updateCooldownUi = () => {
        submitButton.disabled = true;
        submitButton.classList.add('btn-loading');
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.innerHTML = '<span class="block">REINTENTAR EN ' + cooldownRemainingSeconds + 's</span>';
        setMessage('error', 'Demasiados intentos. Espera ' + cooldownRemainingSeconds + ' segundos para reintentar.');
    };

    updateCooldownUi();

    cooldownTimerId = setInterval(() => {
        cooldownRemainingSeconds -= 1;

        if (cooldownRemainingSeconds <= 0) {
            clearInterval(cooldownTimerId);
            cooldownTimerId = null;
            cooldownRemainingSeconds = 0;
            setControlsDisabled(false);
            setLoadingState(false);
            setMessage('success', 'Ya puedes intentar iniciar sesion nuevamente.');
            emailInput.focus();
            return;
        }

        updateCooldownUi();
    }, 1000);
}

if (togglePasswordButton && passwordInput) {
    togglePasswordButton.addEventListener('click', () => {
        const isPasswordHidden = passwordInput.type === 'password';
        passwordInput.type = isPasswordHidden ? 'text' : 'password';
        togglePasswordButton.textContent = isPasswordHidden ? 'Ocultar' : 'Mostrar';
        togglePasswordButton.setAttribute('aria-pressed', isPasswordHidden ? 'true' : 'false');
        togglePasswordButton.setAttribute('aria-label', isPasswordHidden ? 'Ocultar contrasena' : 'Mostrar contrasena');
    });
}

async function loginUsuario(event) {
    event.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    const remember = rememberInput ? rememberInput.checked : false;

    loginMessage.innerHTML = '';
    clearAllFieldErrors();

    if (!email || !password) {
        if (!email) {
            setFieldError(emailInput, emailError, 'Ingresa tu correo electronico.');
        }

        if (!password) {
            setFieldError(passwordInput, passwordError, 'Ingresa tu contrasena.');
        }

        setMessage('error', 'Por favor, completa todos los campos obligatorios.');
        (email ? passwordInput : emailInput).focus();
        return;
    }

    setLoadingState(true);

    try {
        const response = await fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ email, password, remember }),
        });

        const data = await response.json();

        if (response.status === 422 && data.errors) {
            if (data.errors.email) {
                setFieldError(emailInput, emailError, data.errors.email[0]);
            }

            if (data.errors.password) {
                setFieldError(passwordInput, passwordError, data.errors.password[0]);
            }

            setMessage('error', 'Revisa los datos capturados e intentalo de nuevo.');
            (data.errors.email ? emailInput : passwordInput).focus();
            return;
        }

        if (response.status === 429) {
            const retryAfterHeader = response.headers.get('Retry-After');
            const retryAfterSeconds = Number.parseInt(String(retryAfterHeader), 10);

            startCooldown(Number.isFinite(retryAfterSeconds) && retryAfterSeconds > 0 ? retryAfterSeconds : 60);
            return;
        }

        if (!response.ok || !data.success) {
            setFieldError(passwordInput, passwordError, 'Verifica tu contrasena e intentalo nuevamente.');
            setMessage('error', data.message || 'No fue posible iniciar sesion.');
            passwordInput.focus();
            return;
        }

        setMessage('success', data.message || 'Acceso correcto. Redirigiendo...');

        if (data.role === 'administrador') {
            window.location.href = '/tablas-control';
        } else if (data.role === 'usuario') {
            window.location.href = '/user/dashboard';
        }
    } catch (error) {
        console.error('Error:', error);
        setMessage('error', 'Error en el servidor. Intenta nuevamente en unos segundos.');
    } finally {
        if (cooldownRemainingSeconds <= 0) {
            setLoadingState(false);
        }
    }
}

if (loginForm) {
    loginForm.addEventListener('input', () => {
        clearAllFieldErrors();
        loginMessage.innerHTML = '';
    });
}
