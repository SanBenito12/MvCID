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
    <title>Registro - MVC SISTEMA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/estilosreg.css" rel="stylesheet">
</head>
<body>

<div class="register-container">
    <div class="register-header">
        <h1>
            <i class="fas fa-user-plus"></i>
            MvC SISTEMA
        </h1>
        <p>Crea tu cuenta y comienza a aprender</p>
    </div>

    <form id="registroForm" class="register-form">
        <div class="form-group">
            <label for="nombre">
                <i class="fas fa-user"></i>
                Nombre
            </label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                class="form-input"
                required
                placeholder="Ingresa tu nombre"
                autocomplete="given-name"
            >
        </div>

        <div class="form-group">
            <label for="apellido">
                <i class="fas fa-user"></i>
                Apellido
            </label>
            <input
                type="text"
                id="apellido"
                name="apellido"
                class="form-input"
                required
                placeholder="Ingresa tu apellido"
                autocomplete="family-name"
            >
        </div>

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
                    placeholder="Crea una contraseña segura"
                    autocomplete="new-password"
                >
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                </button>
            </div>
            <div class="password-strength" id="password-strength">
                <div class="strength-bar">
                    <div class="strength-fill" id="strength-fill"></div>
                </div>
                <div class="strength-text" id="strength-text">Escribe una contraseña</div>
            </div>
        </div>

        <div class="form-group">
            <label for="confirmPassword">
                <i class="fas fa-lock"></i>
                Confirmar Contraseña
            </label>
            <div class="password-input-container">
                <input
                    type="password"
                    id="confirmPassword"
                    name="confirmPassword"
                    class="form-input password-input"
                    required
                    placeholder="Repite tu contraseña"
                    autocomplete="new-password"
                >
                <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                    <i class="fas fa-eye" id="confirm-toggle-icon"></i>
                </button>
            </div>
            <div class="password-match" id="password-match" style="display: none;">
                <i class="fas fa-check-circle"></i>
                <span id="match-text">Las contraseñas coinciden</span>
            </div>
        </div>

        <!-- Requisitos de contraseña -->
        <div class="password-requirements">
            <h4><i class="fas fa-shield-alt"></i> Requisitos de contraseña:</h4>
            <ul id="password-requirements-list">
                <li id="req-length"><i class="fas fa-times"></i> Al menos 8 caracteres</li>
                <li id="req-uppercase"><i class="fas fa-times"></i> Una letra mayúscula</li>
                <li id="req-lowercase"><i class="fas fa-times"></i> Una letra minúscula</li>
                <li id="req-number"><i class="fas fa-times"></i> Un número</li>
            </ul>
        </div>

        <button type="submit" class="register-btn">
            <i class="fas fa-user-plus"></i>
            Crear mi cuenta
        </button>
    </form>

    <div class="login-link">
        <p><a href="/login">¿Ya tienes cuenta? Inicia sesión</a></p>
    </div>

    <!-- Mensaje de error/éxito -->
    <div id="mensaje" class="mensaje" style="display: none;">
        <i class="fas fa-info-circle"></i>
        <span id="mensajeTexto"></span>
    </div>
</div>

<style>
/* Estilos adicionales para registro con contraseña */
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
    z-index: 10;
}

.password-toggle:hover {
    color: var(--accent-blue);
}

.password-strength {
    margin-top: var(--spacing-sm);
}

.strength-bar {
    width: 100%;
    height: 4px;
    background: var(--border-color);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: var(--spacing-xs);
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all var(--transition-normal);
    border-radius: 2px;
}

.strength-text {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-weight: 500;
}

.password-match {
    margin-top: var(--spacing-sm);
    color: var(--accent-green);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.password-match.error {
    color: var(--accent-red);
}

.password-requirements {
    background: var(--primary-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    margin: var(--spacing-lg) 0;
}

.password-requirements h4 {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: var(--spacing-md);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.password-requirements ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.password-requirements li {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: var(--spacing-xs);
    transition: all var(--transition-normal);
}

.password-requirements li.valid {
    color: var(--accent-green);
}

.password-requirements li.valid i {
    color: var(--accent-green);
}

.password-requirements li i {
    color: var(--accent-red);
    width: 14px;
}

/* Fortalezas de contraseña */
.strength-weak .strength-fill {
    width: 25%;
    background: var(--accent-red);
}

.strength-fair .strength-fill {
    width: 50%;
    background: var(--accent-orange);
}

.strength-good .strength-fill {
    width: 75%;
    background: var(--accent-blue);
}

.strength-strong .strength-fill {
    width: 100%;
    background: var(--accent-green);
}

.form-group.success .form-input {
    border-color: var(--accent-green);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group.error .form-input {
    border-color: var(--accent-red);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
</style>

<script>
// Variables globales
let passwordStrength = 0;
let passwordsMatch = false;

// Manejar el formulario de registro
document.getElementById('registroForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const container = document.querySelector('.register-container');
    const mensaje = document.getElementById('mensaje');
    const mensajeTexto = document.getElementById('mensajeTexto');
    const submitBtn = document.querySelector('.register-btn');

    // Validaciones del lado cliente
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password !== confirmPassword) {
        mostrarError('Las contraseñas no coinciden');
        return;
    }

    if (passwordStrength < 3) {
        mostrarError('La contraseña no es lo suficientemente segura');
        return;
    }

    // Mostrar estado de carga
    container.classList.add('loading');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando cuenta...';

    // Obtener datos del formulario
    const formData = {
        nombre: document.getElementById('nombre').value,
        apellido: document.getElementById('apellido').value,
        email: document.getElementById('email').value,
        password: password
    };

    try {
        // Llamada a la API del backend
        const response = await fetch('/api/clientes/registro', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const text = await response.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch (parseError) {
            throw new Error('Error del servidor. Intenta nuevamente.');
        }

        if (response.ok && data.success) {
            // Registro exitoso
            mensajeTexto.textContent = '✅ Registro exitoso. Redirigiendo...';
            mensaje.className = 'mensaje mensaje-exito';
            mensaje.style.display = 'block';

            // Redirigir al dashboard después de 2 segundos
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 2000);

        } else {
            // Error en el registro
            mostrarError(data.message || 'Error al registrar usuario');
        }

    } catch (error) {
        // Error de conexión
        mostrarError('Error de conexión. Intenta nuevamente.');
    }

    // Restaurar botón
    container.classList.remove('loading');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Crear mi cuenta';
});

// Función para mostrar errores
function mostrarError(mensaje) {
    const mensajeEl = document.getElementById('mensaje');
    const mensajeTexto = document.getElementById('mensajeTexto');
    
    mensajeTexto.textContent = mensaje;
    mensajeEl.className = 'mensaje mensaje-error';
    mensajeEl.style.display = 'block';
}

// Función para alternar visibilidad de contraseña
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = fieldId === 'password' ? 
        document.getElementById('password-toggle-icon') : 
        document.getElementById('confirm-toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Validación de fortaleza de contraseña en tiempo real
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    updatePasswordStrength(password);
    updatePasswordRequirements(password);
    checkPasswordMatch();
});

// Validación de coincidencia de contraseñas
document.getElementById('confirmPassword').addEventListener('input', function() {
    checkPasswordMatch();
});

// Función para actualizar la fortaleza de contraseña
function updatePasswordStrength(password) {
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    // Limpiar clases anteriores
    strengthBar.className = 'strength-bar';
    
    let strength = 0;
    let strengthText_content = '';
    
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    
    passwordStrength = strength;
    
    switch (strength) {
        case 0:
        case 1:
            strengthBar.classList.add('strength-weak');
            strengthText_content = 'Muy débil';
            break;
        case 2:
            strengthBar.classList.add('strength-fair');
            strengthText_content = 'Débil';
            break;
        case 3:
            strengthBar.classList.add('strength-good');
            strengthText_content = 'Buena';
            break;
        case 4:
            strengthBar.classList.add('strength-strong');
            strengthText_content = 'Muy fuerte';
            break;
    }
    
    strengthText.textContent = strengthText_content;
}

// Función para actualizar los requisitos de contraseña
function updatePasswordRequirements(password) {
    const requirements = {
        'req-length': password.length >= 8,
        'req-uppercase': /[A-Z]/.test(password),
        'req-lowercase': /[a-z]/.test(password),
        'req-number': /[0-9]/.test(password)
    };
    
    Object.entries(requirements).forEach(([id, met]) => {
        const element = document.getElementById(id);
        const icon = element.querySelector('i');
        
        if (met) {
            element.classList.add('valid');
            icon.className = 'fas fa-check';
        } else {
            element.classList.remove('valid');
            icon.className = 'fas fa-times';
        }
    });
}

// Función para verificar coincidencia de contraseñas
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchElement = document.getElementById('password-match');
    const matchText = document.getElementById('match-text');
    const confirmGroup = document.getElementById('confirmPassword').parentElement.parentElement;
    
    if (confirmPassword.length > 0) {
        matchElement.style.display = 'flex';
        
        if (password === confirmPassword) {
            passwordsMatch = true;
            matchElement.classList.remove('error');
            matchText.textContent = 'Las contraseñas coinciden';
            matchElement.querySelector('i').className = 'fas fa-check-circle';
            confirmGroup.classList.add('success');
            confirmGroup.classList.remove('error');
        } else {
            passwordsMatch = false;
            matchElement.classList.add('error');
            matchText.textContent = 'Las contraseñas no coinciden';
            matchElement.querySelector('i').className = 'fas fa-times-circle';
            confirmGroup.classList.add('error');
            confirmGroup.classList.remove('success');
        }
    } else {
        matchElement.style.display = 'none';
        passwordsMatch = false;
        confirmGroup.classList.remove('success', 'error');
    }
}

// Efecto de focus mejorado
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.parentElement.style.transform = 'scale(1.02)';
        this.parentElement.parentElement.classList.remove('error');
    });

    input.addEventListener('blur', function() {
        this.parentElement.parentElement.style.transform = 'scale(1)';
        
        // Validación en tiempo real
        if (this.value && this.checkValidity() && this.id !== 'password' && this.id !== 'confirmPassword') {
            this.parentElement.parentElement.classList.add('success');
        }
    });

    // Limpiar errores al escribir
    input.addEventListener('input', function() {
        this.parentElement.parentElement.classList.remove('error');
    });
});

// Validación de email en tiempo real
document.getElementById('email').addEventListener('blur', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const parentGroup = this.parentElement;
    
    if (this.value && emailRegex.test(this.value)) {
        parentGroup.classList.add('success');
        parentGroup.classList.remove('error');
    } else if (this.value) {
        parentGroup.classList.add('error');
        parentGroup.classList.remove('success');
    }
});

// Validación de campos de texto
['nombre', 'apellido'].forEach(fieldId => {
    document.getElementById(fieldId).addEventListener('blur', function() {
        const parentGroup = this.parentElement;
        
        if (this.value && this.value.length >= 2) {
            parentGroup.classList.add('success');
            parentGroup.classList.remove('error');
        } else if (this.value) {
            parentGroup.classList.add('error');
            parentGroup.classList.remove('success');
        }
    });
});

// Auto-focus en el primer campo
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('nombre').focus();
});

// Validación en tiempo real para habilitar/deshabilitar botón
function updateSubmitButton() {
    const submitBtn = document.querySelector('.register-btn');
    const nombre = document.getElementById('nombre').value;
    const apellido = document.getElementById('apellido').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    const allFieldsFilled = nombre && apellido && email && password && confirmPassword;
    const passwordValid = passwordStrength >= 3;
    const passwordsMatchValid = passwordsMatch;
    
    if (allFieldsFilled && passwordValid && passwordsMatchValid) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    } else {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';
    }
}

// Añadir event listeners para actualizar el botón
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('input', updateSubmitButton);
});


</script>

</body>
</html>