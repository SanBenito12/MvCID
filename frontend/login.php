<?php
session_start();

// Redirigir al dashboard si ya está logueado
if (isset($_SESSION['id_cliente']) && isset($_SESSION['llave_secreta'])) {
    header("Location: /dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MVC SISTEMA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/estiloslog.css" rel="stylesheet">
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1>
            <i class="fas fa-graduation-cap"></i>
            MvC SISTEMA
        </h1>
        <p>Accede a tus cursos</p>
    </div>

    <div class="register-link">
        <p><a href="/registro">¿No tienes cuenta? Regístrate aquí</a></p>
    </div>

    <form id="loginForm" class="login-form">
        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i>
                Correo Electrónico
            </label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input"
                required
                placeholder="Ingresa tu correo electrónico"
                autocomplete="email"
            >
        </div>

        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i>
                Contraseña
            </label>
            <div class="password-input-container">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input password-input"
                    required
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                >
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="login-btn">
            <i class="fas fa-sign-in-alt"></i>
            Iniciar Sesión
        </button>
    </form>

    <!-- Opción de login legacy -->
    <div class="legacy-login">
        <button type="button" class="legacy-btn" onclick="toggleLegacyLogin()">
            <i class="fas fa-key"></i>
            ¿Login sin contraseña? (Legacy)
        </button>
        
        <form id="legacyLoginForm" class="legacy-form" style="display: none;">
            <div class="form-group">
                <label for="legacyEmail">
                    <i class="fas fa-envelope"></i>
                    Solo Email (Método Legacy)
                </label>
                <input
                    type="email"
                    id="legacyEmail"
                    name="email"
                    class="form-input"
                    placeholder="Ingresa tu correo electrónico"
                    autocomplete="email"
                >
            </div>
            <button type="submit" class="login-btn legacy">
                <i class="fas fa-unlock"></i>
                Acceso Legacy
            </button>
        </form>
    </div>

    <!-- Mensaje de error/éxito -->
    <div id="mensaje" class="mensaje" style="display: none;">
        <i class="fas fa-info-circle"></i>
        <span id="mensajeTexto"></span>
    </div>
</div>

<style>
/* Estilos adicionales para el login con contraseña */
.password-input-container {
    position: relative;
}

.password-input {
    padding-right: 50px !important;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 5px;
    transition: all var(--transition-normal);
}

.password-toggle:hover {
    color: var(--accent-blue);
}

.legacy-login {
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--border-color);
    text-align: center;
}

.legacy-btn {
    background: var(--accent-bg);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-normal);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin: 0 auto;
}

.legacy-btn:hover {
    background: var(--secondary-bg);
    color: var(--text-primary);
    border-color: var(--border-hover);
}

.legacy-form {
    margin-top: var(--spacing-lg);
    padding: var(--spacing-lg);
    background: var(--primary-bg);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
}

.login-btn.legacy {
    background: var(--gradient-warning);
}

.form-group.error .form-input {
    border-color: var(--accent-red);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-group.success .form-input {
    border-color: var(--accent-green);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
</style>

<script>
// Variables globales
let legacyModeVisible = false;

// Manejar el formulario de login principal
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const container = document.querySelector('.login-container');
    const mensaje = document.getElementById('mensaje');
    const mensajeTexto = document.getElementById('mensajeTexto');
    const submitBtn = document.querySelector('.login-btn:not(.legacy)');

    // Mostrar estado de carga
    container.classList.add('loading');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        // Llamada a la API del backend con contraseña
        const response = await fetch('/api/clientes/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                email: email,
                password: password 
            })
        });

        const text = await response.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch (parseError) {
            throw new Error('Error del servidor. Intenta nuevamente.');
        }

        if (response.ok && data.success) {
            // Login exitoso
            mensajeTexto.textContent = '✅ Inicio de sesión exitoso. Redirigiendo...';
            mensaje.className = 'mensaje mensaje-exito';
            mensaje.style.display = 'block';

            // Redirigir al dashboard después de 1.5 segundos
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1500);

        } else {
            // Error en el login
            mensajeTexto.textContent = data.message || 'Error al iniciar sesión';
            mensaje.className = 'mensaje mensaje-error';
            mensaje.style.display = 'block';

            // Marcar campos con error
            markFieldError('email');
            markFieldError('password');

            // Restaurar botón después del error
            setTimeout(() => {
                container.classList.remove('loading');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
                clearFieldErrors();
            }, 3000);
        }

    } catch (error) {
        // Error de conexión
        mensajeTexto.textContent = 'Error de conexión. Intenta nuevamente.';
        mensaje.className = 'mensaje mensaje-error';
        mensaje.style.display = 'block';

        // Restaurar botón
        container.classList.remove('loading');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
    }
});

// Manejar el formulario de login legacy
document.getElementById('legacyLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const container = document.querySelector('.login-container');
    const mensaje = document.getElementById('mensaje');
    const mensajeTexto = document.getElementById('mensajeTexto');
    const submitBtn = document.querySelector('.login-btn.legacy');

    // Mostrar estado de carga
    container.classList.add('loading');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Accediendo...';

    const email = document.getElementById('legacyEmail').value;

    try {
        // Llamada a la API del backend modo legacy
        const response = await fetch('/api/clientes/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                accion: 'login-legacy',
                email: email 
            })
        });

        const text = await response.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch (parseError) {
            throw new Error('Error del servidor. Intenta nuevamente.');
        }

        if (response.ok && data.success) {
            // Login exitoso
            mensajeTexto.textContent = '✅ Acceso legacy exitoso. Redirigiendo...';
            mensaje.className = 'mensaje mensaje-exito';
            mensaje.style.display = 'block';

            // Redirigir al dashboard después de 1.5 segundos
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1500);

        } else {
            // Error en el login
            mensajeTexto.textContent = data.message || 'Error en acceso legacy';
            mensaje.className = 'mensaje mensaje-error';
            mensaje.style.display = 'block';

            // Restaurar botón después del error
            setTimeout(() => {
                container.classList.remove('loading');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-unlock"></i> Acceso Legacy';
            }, 3000);
        }

    } catch (error) {
        // Error de conexión
        mensajeTexto.textContent = 'Error de conexión. Intenta nuevamente.';
        mensaje.className = 'mensaje mensaje-error';
        mensaje.style.display = 'block';

        // Restaurar botón
        container.classList.remove('loading');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-unlock"></i> Acceso Legacy';
    }
});

// Función para alternar visibilidad de contraseña
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Función para alternar modo legacy
function toggleLegacyLogin() {
    const legacyForm = document.getElementById('legacyLoginForm');
    const legacyBtn = document.querySelector('.legacy-btn');
    
    legacyModeVisible = !legacyModeVisible;
    
    if (legacyModeVisible) {
        legacyForm.style.display = 'block';
        legacyBtn.innerHTML = '<i class="fas fa-times"></i> Ocultar Login Legacy';
        legacyForm.style.animation = 'slideInDown 0.3s ease';
    } else {
        legacyForm.style.display = 'none';
        legacyBtn.innerHTML = '<i class="fas fa-key"></i> ¿Login sin contraseña? (Legacy)';
    }
}

// Función para marcar campo con error
function markFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.parentElement.classList.add('error');
    }
}

// Función para limpiar errores de campos
function clearFieldErrors() {
    document.querySelectorAll('.form-group').forEach(group => {
        group.classList.remove('error', 'success');
    });
}

// Efecto de focus mejorado
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
        this.parentElement.classList.remove('error');
    });

    input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
        
        // Validación en tiempo real
        if (this.value && this.checkValidity()) {
            this.parentElement.classList.add('success');
        }
    });

    // Limpiar errores al escribir
    input.addEventListener('input', function() {
        this.parentElement.classList.remove('error');
    });
});

// Auto-focus en el primer campo
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('email').focus();
});
</script>

</body>
</html>