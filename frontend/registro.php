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

<script>
    // Manejar el formulario de registro
    document.getElementById('registroForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const container = document.querySelector('.register-container');
        const mensaje = document.getElementById('mensaje');
        const mensajeTexto = document.getElementById('mensajeTexto');
        const submitBtn = document.querySelector('.register-btn');

        // Mostrar estado de carga
        container.classList.add('loading');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

        // Obtener datos del formulario
        const formData = {
            nombre: document.getElementById('nombre').value,
            apellido: document.getElementById('apellido').value,
            email: document.getElementById('email').value
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
                mensajeTexto.textContent = data.message || 'Error al registrar usuario';
                mensaje.className = 'mensaje mensaje-error';
                mensaje.style.display = 'block';
            }

        } catch (error) {
            // Error de conexión
            mensajeTexto.textContent = 'Error de conexión. Intenta nuevamente.';
            mensaje.className = 'mensaje mensaje-error';
            mensaje.style.display = 'block';
        }

        // Restaurar botón
        container.classList.remove('loading');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Crear mi cuenta';
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
</style>

</body>
</html>