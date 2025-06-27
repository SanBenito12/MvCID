<?php
session_start();
if (!isset($_SESSION['id_cliente']) || !isset($_SESSION['llave_secreta'])) {
    header("Location: /login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - MVC SISTEMA</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        const id_cliente = "<?= $_SESSION['id_cliente'] ?>";
        const llave_secreta = "<?= $_SESSION['llave_secreta'] ?>";
    </script>
    <style>
        .password-container {
            max-width: 600px;
            margin: 0 auto;
            padding: var(--spacing-lg);
        }

        .password-form {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            margin-bottom: var(--spacing-xl);
        }

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
        }

        .strength-weak .strength-fill { width: 25%; background: var(--accent-red); }
        .strength-fair .strength-fill { width: 50%; background: var(--accent-orange); }
        .strength-good .strength-fill { width: 75%; background: var(--accent-blue); }
        .strength-strong .strength-fill { width: 100%; background: var(--accent-green); }

        .strength-text {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .password-requirements {
            background: var(--primary-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin: var(--spacing-lg) 0;
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
        }

        .password-requirements li.valid {
            color: var(--accent-green);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--accent-blue);
            text-decoration: none;
            margin-bottom: var(--spacing-lg);
            transition: all var(--transition-normal);
        }

        .back-link:hover {
            color: var(--text-primary);
            transform: translateX(-4px);
        }
    </style>
</head>
<body>
<div class="password-container">
    <a href="/dashboard" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Volver al Dashboard
    </a>

    <div class="password-form">
        <div class="form-header" style="text-align: center; margin-bottom: var(--spacing-2xl);">
            <h1>
                <i class="fas fa-key"></i>
                Cambiar Contraseña
            </h1>
            <p style="color: var(--text-secondary);">
                Actualiza tu contraseña para mantener tu cuenta segura
            </p>
        </div>

        <form id="passwordForm">
            <div class="form-group">
                <label for="currentPassword">
                    <i class="fas fa-lock"></i>
                    Contraseña Actual
                </label>
                <div class="password-input-container">
                    <input
                        type="password"
                        id="currentPassword"
                        name="currentPassword"
                        class="form-input password-input"
                        required
                        placeholder="Ingresa tu contraseña actual"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('currentPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="newPassword">
                    <i class="fas fa-key"></i>
                    Nueva Contraseña
                </label>
                <div class="password-input-container">
                    <input
                        type="password"
                        id="newPassword"
                        name="newPassword"
                        class="form-input password-input"
                        required
                        placeholder="Ingresa tu nueva contraseña"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                    <div class="strength-text" id="strength-text">Escribe una nueva contraseña</div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">
                    <i class="fas fa-shield-alt"></i>
                    Confirmar Nueva Contraseña
                </label>
                <div class="password-input-container">
                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        class="form-input password-input"
                        required
                        placeholder="Repite tu nueva contraseña"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-match" id="password-match" style="display: none; margin-top: var(--spacing-sm); color: var(--accent-green); font-size: 0.9rem;">
                    <i class="fas fa-check-circle"></i>
                    <span id="match-text">Las contraseñas coinciden</span>
                </div>
            </div>

            <div class="password-requirements">
                <h4 style="color: var(--text-secondary); margin-bottom: var(--spacing-md);">
                    <i class="fas fa-shield-alt"></i> 
                    Requisitos de contraseña:
                </h4>
                <ul id="password-requirements-list">
                    <li id="req-length"><i class="fas fa-times"></i> Al menos 8 caracteres</li>
                    <li id="req-uppercase"><i class="fas fa-times"></i> Una letra mayúscula</li>
                    <li id="req-lowercase"><i class="fas fa-times"></i> Una letra minúscula</li>
                    <li id="req-number"><i class="fas fa-times"></i> Un número</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="fas fa-save"></i>
                    Cambiar Contraseña
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='/dashboard'">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    <!-- Mensaje de estado -->
    <div id="mensaje" class="mensaje" style="display: none;">
        <i class="fas fa-info-circle"></i>
        <span id="mensajeTexto"></span>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Actualizando contraseña...</p>
    </div>
</div>

<script>
// Variables globales
let passwordStrength = 0;
let passwordsMatch = false;

// Manejar el formulario
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const mensaje = document.getElementById('mensaje');
    const mensajeTexto = document.getElementById('mensajeTexto');
    const submitBtn = document.getElementById('submitBtn');

    // Validaciones del lado cliente
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        mostrarMensaje('Las contraseñas no coinciden', 'error');
        return;
    }

    if (passwordStrength < 3) {
        mostrarMensaje('La nueva contraseña no es lo suficientemente segura', 'error');
        return;
    }

    if (currentPassword === newPassword) {
        mostrarMensaje('La nueva contraseña debe ser diferente a la actual', 'error');
        return;
    }

    // Mostrar loading
    mostrarLoading(true);

    try {
        const response = await fetch('/api/clientes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                accion: 'cambiar-password',
                password_actual: currentPassword,
                password_nueva: newPassword,
                id_cliente: id_cliente,
                llave_secreta: llave_secreta
            })
        });

        const data = await response.json();

        if (data.success) {
            mostrarMensaje('✅ Contraseña actualizada exitosamente', 'success');
            
            // Limpiar formulario
            document.getElementById('passwordForm').reset();
            updatePasswordStrength('');
            updatePasswordRequirements('');
            checkPasswordMatch();
            
            // Redirigir después de 3 segundos
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 3000);

        } else {
            mostrarMensaje('Error: ' + data.error, 'error');
        }

    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión. Intenta nuevamente.', 'error');
    } finally {
        mostrarLoading(false);
    }
});

// Función para alternar visibilidad de contraseña
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = passwordInput.nextElementSibling.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Validación de fortaleza de contraseña
document.getElementById('newPassword').addEventListener('input', function() {
    const password = this.value;
    updatePasswordStrength(password);
    updatePasswordRequirements(password);
    checkPasswordMatch();
    updateSubmitButton();
});

document.getElementById('confirmPassword').addEventListener('input', function() {
    checkPasswordMatch();
    updateSubmitButton();
});

document.getElementById('currentPassword').addEventListener('input', function() {
    updateSubmitButton();
});

// Función para actualizar la fortaleza de contraseña
function updatePasswordStrength(password) {
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.getElementById('strength-text');
    
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
            strengthText_content = password.length > 0 ? 'Muy débil' : 'Escribe una nueva contraseña';
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

// Función para actualizar los requisitos
function updatePasswordRequirements(password) {
    const requirements = {
        'req-length': password.length >= 8,
        'req-uppercase': /[A-Z]/.test(password),
        'req-lowercase': /[a-z]/.test(password),
        'req-number': /[0-9]/.test(password)
    };
    
    Object.entries(requirements).forEach(([id, met]) => {
        const element = document.getElementById(id);
        if (element) {
            const icon = element.querySelector('i');
            
            if (met) {
                element.classList.add('valid');
                icon.className = 'fas fa-check';
            } else {
                element.classList.remove('valid');
                icon.className = 'fas fa-times';
            }
        }
    });
}

// Función para verificar coincidencia de contraseñas
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchElement = document.getElementById('password-match');
    const matchText = document.getElementById('match-text');
    
    if (confirmPassword.length > 0) {
        matchElement.style.display = 'flex';
        
        if (newPassword === confirmPassword) {
            passwordsMatch = true;
            matchElement.style.color = 'var(--accent-green)';
            matchText.textContent = 'Las contraseñas coinciden';
            matchElement.querySelector('i').className = 'fas fa-check-circle';
        } else {
            passwordsMatch = false;
            matchElement.style.color = 'var(--accent-red)';
            matchText.textContent = 'Las contraseñas no coinciden';
            matchElement.querySelector('i').className = 'fas fa-times-circle';
        }
    } else {
        matchElement.style.display = 'none';
        passwordsMatch = false;
    }
}

// Función para actualizar el estado del botón submit
function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    const allFieldsFilled = currentPassword && newPassword && confirmPassword;
    const passwordValid = passwordStrength >= 3;
    const passwordsMatchValid = passwordsMatch;
    const passwordsDifferent = currentPassword !== newPassword;
    
    if (allFieldsFilled && passwordValid && passwordsMatchValid && passwordsDifferent) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    } else {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';
    }
}

// Funciones de utilidad
function mostrarMensaje(texto, tipo = 'success') {
    const mensaje = document.getElementById('mensaje');
    const mensajeTexto = document.getElementById('mensajeTexto');
    
    if (!mensaje || !mensajeTexto) return;

    // Limpiar clases anteriores
    mensaje.className = 'mensaje';

    // Agregar clase según tipo
    if (tipo === 'error') {
        mensaje.classList.add('mensaje-error');
    } else if (tipo === 'warning') {
        mensaje.classList.add('mensaje-warning');
    }

    // Agregar ícono según tipo
    let icono = '';
    switch (tipo) {
        case 'error':
            icono = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        case 'warning':
            icono = '<i class="fas fa-exclamation-circle"></i>';
            break;
        default:
            icono = '<i class="fas fa-check-circle"></i>';
    }

    mensajeTexto.innerHTML = `${icono} ${texto}`;
    mensaje.style.display = 'flex';

    // Auto-ocultar después de un tiempo
    setTimeout(() => {
        mensaje.style.display = 'none';
    }, tipo === 'error' ? 8000 : 5000);

    // Scroll hacia el mensaje
    mensaje.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function mostrarLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        if (show) {
            overlay.classList.add('show');
            overlay.style.display = 'flex';
        } else {
            overlay.classList.remove('show');
            overlay.style.display = 'none';
        }
    }
}

// Efecto de focus mejorado
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.parentElement.style.transform = 'scale(1.02)';
    });

    input.addEventListener('blur', function() {
        this.parentElement.parentElement.style.transform = 'scale(1)';
    });
});

// Prevenir pegado en campo de confirmar contraseña
document.getElementById('confirmPassword').addEventListener('paste', function(e) {
    e.preventDefault();
    mostrarMensaje('Por favor, escribe la contraseña manualmente para confirmar', 'warning');
});

// Inicializar estado
document.addEventListener('DOMContentLoaded', () => {
    updateSubmitButton();
});
</script>

</body>
</html>