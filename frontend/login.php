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

        <button type="submit" class="login-btn">
            <i class="fas fa-sign-in-alt"></i>
            Acceder a mis cursos
        </button>
    </form>

    <!-- Mensaje de error/éxito -->
    <div id="mensaje" class="mensaje" style="display: none;">
        <i class="fas fa-info-circle"></i>
        <span id="mensajeTexto"></span>
    </div>
</div>

<script>
    // Manejar el formulario de login
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const container = document.querySelector('.login-container');
        const mensaje = document.getElementById('mensaje');
        const mensajeTexto = document.getElementById('mensajeTexto');
        const submitBtn = document.querySelector('.login-btn');

        // Mostrar estado de carga
        container.classList.add('loading');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';

        const email = document.getElementById('email').value;

        try {
            // Llamada a la API del backend
            const response = await fetch('/api/clientes/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
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

                // Restaurar botón después del error
                setTimeout(() => {
                    container.classList.remove('loading');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Acceder a mis cursos';
                }, 2000);
            }

        } catch (error) {
            // Error de conexión
            mensajeTexto.textContent = 'Error de conexión. Intenta nuevamente.';
            mensaje.className = 'mensaje mensaje-error';
            mensaje.style.display = 'block';

            // Restaurar botón
            container.classList.remove('loading');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Acceder a mis cursos';
        }
    });

    // Efecto de focus mejorado
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
</script>

<style>
    .mensaje {
        margin-top: 20px;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
    }

    .mensaje-exito {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .mensaje-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .loading {
        opacity: 0.7;
        pointer-events: none;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-btn:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>

</body>
</html>