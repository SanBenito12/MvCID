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
    <title>Dashboard - EduPlatform</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .mensaje-warning {
            background: linear-gradient(45deg, #ffa726, #ff9800) !important;
            color: white !important;
        }

        .mensaje-error {
            background: linear-gradient(45deg, #ff6b6b, #ee5a52) !important;
            color: white !important;
        }

        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .modal-close:hover {
            color: #000;
        }

        .modal .form-group {
            margin-bottom: 20px;
        }

        .modal .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .modal input, .modal textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .modal input:focus, .modal textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .preview-image {
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 0;
            }
            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }
    </style>
    <script>
        const id_cliente = "<?= $_SESSION['id_cliente'] ?>";
        const llave_secreta = "<?= $_SESSION['llave_secreta'] ?>";
        const SUPABASE_SERVICE_ROLE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imd2cGtla3NidWpmZHN6Y2twdWtpIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0OTUxMDQ1NCwiZXhwIjoyMDY1MDg2NDU0fQ.rNXqhDiKveKgUdFnStIVer7QkpGNsSPwM_f9FheQKhQ";
    </script>
</head>
<body>
<!-- Header -->
<div class="container">
    <div class="header">
        <h1>
            <i class="fas fa-graduation-cap"></i>
            Dashboard de Cursos
        </h1>
        <a href="/logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Cerrar Sesión
        </a>
    </div>

    <div id="mensaje" class="mensaje" style="display: none;"></div>

    <!-- Crear Curso -->
    <div class="form-container">
        <h2>
            <i class="fas fa-plus-circle"></i>
            Crear Nuevo Curso
        </h2>
        <form id="formCurso">
            <div class="form-grid">
                <div class="form-group">
                    <label>Título del Curso</label>
                    <input type="text" name="titulo" placeholder="Ej: Desarrollo Web Completo" required>
                </div>
                <div class="form-group">
                    <label>Instructor</label>
                    <input type="text" name="instructor" placeholder="Nombre del instructor" required>
                </div>
                <div class="form-group">
                    <label>Precio (USD)</label>
                    <input type="number" name="precio" placeholder="99.99" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Imagen del Curso</label>
                    <input type="file" name="imagen" accept="image/*" required>
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" placeholder="Describe el contenido del curso..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Crear Curso
            </button>
        </form>
    </div>

    <!-- Mis Cursos -->
    <div class="courses-header">
        <h2>
            <i class="fas fa-book"></i>
            Mis Cursos Creados
        </h2>
    </div>
    <div id="listaCursos" class="courses-grid"></div>

    <!-- Comprar Cursos -->
    <div class="courses-header">
        <h2>
            <i class="fas fa-shopping-cart"></i>
            Cursos Disponibles
        </h2>
    </div>
    <div id="cursosDisponibles" class="courses-grid"></div>

    <!-- Mis Compras -->
    <div class="courses-header">
        <h2>
            <i class="fas fa-receipt"></i>
            Mis Compras
        </h2>
    </div>
    <div id="listaCompras" class="courses-grid"></div>
</div>

<!-- Modal de Edición -->
<div id="modalEdicion" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Curso</h3>
            <button class="modal-close" onclick="cerrarModalEdicion()">&times;</button>
        </div>

        <form id="formEditarCurso">
            <input type="hidden" id="editCursoId" name="id">

            <div class="form-group">
                <label>Título del Curso</label>
                <input type="text" id="editTitulo" name="titulo" required>
            </div>

            <div class="form-group">
                <label>Instructor</label>
                <input type="text" id="editInstructor" name="instructor" required>
            </div>

            <div class="form-group">
                <label>Precio (USD)</label>
                <input type="number" id="editPrecio" name="precio" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea id="editDescripcion" name="descripcion" required style="min-height: 100px;"></textarea>
            </div>

            <div class="form-group">
                <label>Imagen Actual</label>
                <img id="editImagenActual" src="" alt="Imagen actual" class="preview-image">
            </div>

            <div class="form-group">
                <label>Nueva Imagen (opcional)</label>
                <input type="file" id="editImagen" name="imagen" accept="image/*">
                <small style="color: #666;">Deja vacío para mantener la imagen actual</small>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
                <button type="button" onclick="cerrarModalEdicion()" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let id_creador = null;
    let metodos = [];
    let cursoEnEdicion = null;

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            console.log('Iniciando dashboard con credenciales:', { id_cliente, llave_secreta });

            // Validar sesión usando la API corregida
            const authUrl = `/api/auth?id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
            console.log('URL de autenticación:', authUrl);

            const auth = await fetch(authUrl);
            console.log('Respuesta de auth status:', auth.status);

            const data = await auth.json();
            console.log('Datos de autenticación:', data);

            if (!data.valido) {
                alert("Sesión inválida. Redirigiendo...");
                location.href = "/login";
                return;
            }

            id_creador = data.id_creador;
            console.log('ID creador obtenido:', id_creador);

            // Cargar datos iniciales EN ORDEN CORRECTO
            await cargarMetodosPago();        // 1️⃣ PRIMERO los métodos de pago
            await cargarCursos();             // 2️⃣ Luego mis cursos
            await cargarCursosDisponibles();  // 3️⃣ Luego cursos disponibles (usa métodos)
            await cargarCompras();            // 4️⃣ Finalmente las compras

            // Event listeners
            document.getElementById('formCurso').addEventListener('submit', crearCurso);
            document.getElementById('formEditarCurso').addEventListener('submit', guardarCambiosCurso);
        } catch (error) {
            console.error('Error al inicializar:', error);
            mostrarMensaje('Error al cargar la página: ' + error.message, 'error');
        }
    });

    function mostrarMensaje(texto, tipo = 'success') {
        const mensaje = document.getElementById('mensaje');
        mensaje.textContent = texto;

        // Limpiar clases anteriores
        mensaje.className = 'mensaje';

        // Agregar clase según tipo
        if (tipo === 'error') {
            mensaje.classList.add('mensaje-error');
        } else if (tipo === 'warning') {
            mensaje.classList.add('mensaje-warning');
        }
        // 'success' usa la clase base 'mensaje'

        mensaje.style.display = 'block';

        setTimeout(() => {
            mensaje.style.display = 'none';
        }, 8000); // Más tiempo para leer mensajes de debug
    }

    async function cargarCursos() {
        try {
            console.log('Cargando cursos para id_creador:', id_creador);

            // Usar la nueva API con id_cliente (no id_creador) y llave_secreta
            const url = `/api/cursos?id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
            console.log('URL de cursos:', url);

            const res = await fetch(url);
            console.log('Status respuesta cursos:', res.status);

            if (!res.ok) {
                const errorText = await res.text();
                console.error('Error en respuesta de cursos:', errorText);
                throw new Error(`Error ${res.status}: ${errorText}`);
            }

            const cursos = await res.json();
            console.log('Cursos obtenidos:', cursos);

            const cont = document.getElementById('listaCursos');

            if (!Array.isArray(cursos) || cursos.length === 0) {
                cont.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>No tienes cursos creados</h3>
                        <p>Crea tu primer curso usando el formulario de arriba</p>
                    </div>
                `;
                return;
            }

            cont.innerHTML = '';
            cursos.forEach(curso => {
                cont.innerHTML += `
                    <div class="curso">
                        <div class="curso-info">
                            <h3>${curso.titulo}</h3>
                            <p><strong>Instructor:</strong> ${curso.instructor}</p>
                            <p><strong>Descripción:</strong> ${curso.descripcion}</p>
                            <div class="curso-precio">
                                <span class="precio">$${curso.precio}</span>
                            </div>
                            <div class="curso-imagen">
                                <img src="${curso.imagen}" alt="${curso.titulo}">
                            </div>
                        </div>
                        <div class="curso-actions">
                            <button onclick="editarCurso(${curso.id})" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                            <button onclick="eliminarCurso(${curso.id})" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                                Eliminar
                            </button>
                        </div>
                    </div>
                `;
            });
        } catch (error) {
            console.error('Error al cargar cursos:', error);
            mostrarMensaje('Error al cargar tus cursos: ' + error.message, 'error');
        }
    }

    async function editarCurso(id) {
        try {
            console.log('🔧 Editando curso ID:', id);

            // Obtener datos del curso
            const res = await fetch(`/api/cursos?id=${id}`);
            if (!res.ok) {
                throw new Error(`Error ${res.status} al obtener curso`);
            }

            const curso = await res.json();
            console.log('📄 Datos del curso a editar:', curso);

            // Llenar el formulario
            document.getElementById('editCursoId').value = curso.id;
            document.getElementById('editTitulo').value = curso.titulo;
            document.getElementById('editInstructor').value = curso.instructor;
            document.getElementById('editPrecio').value = curso.precio;
            document.getElementById('editDescripcion').value = curso.descripcion;
            document.getElementById('editImagenActual').src = curso.imagen;

            cursoEnEdicion = curso;

            // Mostrar modal
            document.getElementById('modalEdicion').style.display = 'block';

        } catch (error) {
            console.error('❌ Error al cargar curso para editar:', error);
            mostrarMensaje('Error al cargar curso: ' + error.message, 'error');
        }
    }

    function cerrarModalEdicion() {
        document.getElementById('modalEdicion').style.display = 'none';
        cursoEnEdicion = null;
        document.getElementById('formEditarCurso').reset();
    }

    async function guardarCambiosCurso(e) {
        e.preventDefault();

        if (!cursoEnEdicion) {
            mostrarMensaje('Error: No hay curso en edición', 'error');
            return;
        }

        try {
            console.log('💾 Guardando cambios del curso...');

            const form = e.target;
            const formData = new FormData(form);
            const nuevaImagen = formData.get("imagen");

            let datosActualizados = {
                titulo: formData.get("titulo"),
                instructor: formData.get("instructor"),
                precio: parseFloat(formData.get("precio")),
                descripcion: formData.get("descripcion"),
                id_cliente: id_cliente,
                llave_secreta: llave_secreta
            };

            // Si hay nueva imagen, subirla
            if (nuevaImagen && nuevaImagen.size > 0) {
                console.log('📤 Subiendo nueva imagen...');

                const nombre = Date.now() + "_" + nuevaImagen.name;
                const upload = await fetch(`https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/cursos/${nombre}`, {
                    method: "POST",
                    headers: {
                        "Authorization": "Bearer " + SUPABASE_SERVICE_ROLE_KEY,
                        "x-upsert": "true",
                        "Content-Type": nuevaImagen.type
                    },
                    body: nuevaImagen
                });

                if (!upload.ok) {
                    throw new Error('Error al subir nueva imagen');
                }

                datosActualizados.imagen = `https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/public/cursos/${nombre}`;
                console.log('✅ Nueva imagen subida:', datosActualizados.imagen);
            }

            console.log('📡 Enviando actualización:', datosActualizados);

            // Enviar actualización
            const response = await fetch(`/api/cursos?id=${cursoEnEdicion.id}`, {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datosActualizados)
            });

            if (response.ok) {
                mostrarMensaje('✅ Curso actualizado exitosamente');
                cerrarModalEdicion();
                await cargarCursos(); // Recargar lista
                await cargarCursosDisponibles(); // Actualizar disponibles también
            } else {
                const errorData = await response.json();
                console.error('❌ Error al actualizar:', errorData);
                mostrarMensaje('Error al actualizar curso: ' + (errorData.error || 'Error desconocido'), 'error');
            }

        } catch (error) {
            console.error('💥 Error al guardar cambios:', error);
            mostrarMensaje('Error al guardar cambios: ' + error.message, 'error');
        }
    }

    // Resto de funciones (eliminarCurso, crearCurso, etc.) - mantenidas igual
    async function eliminarCurso(id) {
        // Mensaje de confirmación más claro
        const confirmacion = confirm(
            "⚠️ ¿Estás seguro de eliminar este curso?\n\n" +
            "ATENCIÓN: Esta acción también eliminará:\n" +
            "• Todas las compras asociadas a este curso\n" +
            "• Los registros de estudiantes que lo compraron\n\n" +
            "Esta acción NO se puede deshacer.\n\n" +
            "¿Continuar?"
        );

        if (!confirmacion) return;

        try {
            console.log(`🗑️ Eliminando curso ${id} con cascade delete...`);

            const url = `/api/cursos?id=${id}&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
            const response = await fetch(url, { method: "DELETE" });

            if (response.ok) {
                const responseData = await response.json();
                mostrarMensaje('✅ ' + (responseData.mensaje || 'Curso eliminado exitosamente'));
                await cargarCursos();
                await cargarCursosDisponibles();
                await cargarCompras(); // Actualizar compras también
            } else {
                const errorData = await response.json();
                mostrarMensaje('❌ Error al eliminar el curso: ' + (errorData.error || 'Error desconocido'), 'error');
            }
        } catch (error) {
            console.error('💥 Error:', error);
            mostrarMensaje('❌ Error al eliminar el curso: ' + error.message, 'error');
        }
    }

    async function crearCurso(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const imagen = formData.get("imagen");

        try {
            // Subir imagen a Supabase
            const nombre = Date.now() + "_" + imagen.name;
            const upload = await fetch(`https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/cursos/${nombre}`, {
                method: "POST",
                headers: {
                    "Authorization": "Bearer " + SUPABASE_SERVICE_ROLE_KEY,
                    "x-upsert": "true",
                    "Content-Type": imagen.type
                },
                body: imagen
            });

            if (!upload.ok) {
                throw new Error('Error al subir imagen');
            }

            const imagenURL = `https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/public/cursos/${nombre}`;

            // Crear curso con imagen
            const nuevoCurso = {
                titulo: formData.get("titulo"),
                instructor: formData.get("instructor"),
                descripcion: formData.get("descripcion"),
                precio: parseFloat(formData.get("precio")),
                imagen: imagenURL,
                id_cliente: id_cliente,
                llave_secreta: llave_secreta
            };

            console.log('Enviando curso:', nuevoCurso);

            const response = await fetch('/api/cursos', {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(nuevoCurso)
            });

            console.log('Respuesta crear curso:', response.status);

            if (response.ok) {
                mostrarMensaje('Curso creado exitosamente');
                form.reset();
                await cargarCursos();
                await cargarCursosDisponibles();
            } else {
                const errorData = await response.json();
                console.error('Error al crear curso:', errorData);
                mostrarMensaje('Error al crear el curso: ' + (errorData.error || 'Error desconocido'), 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error al crear el curso: ' + error.message, 'error');
        }
    }

    async function cargarMetodosPago() {
        try {
            console.log('🔄 Cargando métodos de pago...');

            // Intentar múltiples rutas en orden de prioridad
            const rutas = [
                '/api-metodos.php',
                '/api-metodos',
                '/backend/api/metodos_pago_simple.php',
                '/api/metodos_pago',
                '/backend/api/metodos_pago.php'
            ];

            let metodosObtenidos = false;
            let ultimoError = null;

            for (const ruta of rutas) {
                try {
                    console.log(`🔗 Probando ruta: ${ruta}`);
                    const res = await fetch(ruta);
                    console.log(`📊 Status ${ruta}: ${res.status}`);

                    if (res.ok) {
                        const responseText = await res.text();
                        console.log(`📝 Respuesta ${ruta}:`, responseText.substring(0, 200));

                        try {
                            metodos = JSON.parse(responseText);

                            if (Array.isArray(metodos) && metodos.length > 0) {
                                console.log(`✅ Métodos cargados desde ${ruta}:`, metodos);
                                metodosObtenidos = true;
                                break;
                            } else {
                                console.log(`⚠️ Respuesta vacía desde ${ruta}`);
                            }
                        } catch (parseError) {
                            console.log(`❌ Error JSON en ${ruta}:`, parseError.message);
                            ultimoError = `Error JSON en ${ruta}: ${parseError.message}`;
                        }
                    } else {
                        console.log(`❌ Error HTTP ${res.status} en ${ruta}`);
                        ultimoError = `Error HTTP ${res.status} en ${ruta}`;
                    }
                } catch (error) {
                    console.log(`❌ Error de red en ${ruta}:`, error.message);
                    ultimoError = `Error de red en ${ruta}: ${error.message}`;
                }
            }

            if (!metodosObtenidos) {
                console.warn('⚠️ Ninguna ruta funcionó, usando métodos por defecto');
                console.warn('Último error:', ultimoError);

                // Usar métodos por defecto
                metodos = [
                    { id: 1, nombre: 'Tarjeta de Crédito' },
                    { id: 2, nombre: 'PayPal' },
                    { id: 3, nombre: 'Transferencia Bancaria' }
                ];

                // Mostrar mensaje de advertencia al usuario
                mostrarMensaje('No se pudieron cargar métodos de pago desde el servidor. Usando métodos por defecto.', 'warning');
            }

            console.log('🎯 Métodos finales:', metodos);

            // 🔧 LLENAR SELECTORES EXISTENTES DESPUÉS DE CARGAR MÉTODOS
            setTimeout(() => {
                llenarSelectoresMetodosPago();
            }, 100); // Pequeño delay para asegurar que el DOM esté listo

        } catch (error) {
            console.error('💥 Error crítico al cargar métodos de pago:', error);

            // Usar métodos por defecto como último recurso
            metodos = [
                { id: 1, nombre: 'Tarjeta de Crédito' },
                { id: 2, nombre: 'PayPal' },
                { id: 3, nombre: 'Transferencia Bancaria' }
            ];

            mostrarMensaje('Error crítico cargando métodos de pago. Usando métodos por defecto.', 'error');
        }
    }

    // Función auxiliar para llenar selectores de métodos de pago
    function llenarSelectoresMetodosPago() {
        console.log('🔄 Llenando todos los selectores de métodos de pago...');

        const selectores = document.querySelectorAll('select[id^="pago_"]');
        console.log(`📊 Encontrados ${selectores.length} selectores`);

        selectores.forEach(select => {
            // Limpiar opciones existentes excepto la primera
            while (select.children.length > 1) {
                select.removeChild(select.lastChild);
            }

            // Agregar métodos de pago
            if (Array.isArray(metodos) && metodos.length > 0) {
                metodos.forEach(metodo => {
                    const option = document.createElement('option');
                    option.value = metodo.id;
                    option.textContent = metodo.nombre;
                    select.appendChild(option);
                });
                console.log(`✅ Selector ${select.id} llenado con ${metodos.length} métodos`);
            } else {
                console.warn(`⚠️ No hay métodos disponibles para ${select.id}`);
            }
        });
    }

    async function cargarCursosDisponibles() {
        try {
            console.log('Cargando cursos disponibles...');

            // Usar la nueva API para obtener cursos disponibles
            const url = `/api/cursos?disponibles=true&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
            console.log('URL cursos disponibles:', url);

            const res = await fetch(url);
            console.log('Status cursos disponibles:', res.status);

            if (!res.ok) {
                const errorText = await res.text();
                console.error('Error en cursos disponibles:', errorText);
                throw new Error(`Error ${res.status}: ${errorText}`);
            }

            const disponibles = await res.json();
            console.log('Cursos disponibles obtenidos:', disponibles);

            const cont = document.getElementById('cursosDisponibles');

            if (!Array.isArray(disponibles) || disponibles.length === 0) {
                cont.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No hay cursos disponibles</h3>
                        <p>Todos los cursos han sido adquiridos o no hay cursos de otros usuarios</p>
                    </div>
                `;
                return;
            }

            cont.innerHTML = '';
            disponibles.forEach(curso => {
                cont.innerHTML += `
                    <div class="curso curso-disponible">
                        <div class="curso-info">
                            <h3>${curso.titulo}</h3>
                            <p><strong>Instructor:</strong> ${curso.instructor}</p>
                            <p><strong>Descripción:</strong> ${curso.descripcion}</p>
                            <div class="curso-precio">
                                <span class="precio">${curso.precio}</span>
                            </div>
                            <div class="curso-imagen">
                                <img src="${curso.imagen}" alt="${curso.titulo}">
                            </div>
                        </div>
                        <div class="curso-compra">
                            <select id="pago_${curso.id}" class="form-group">
                                <option value="">Seleccionar método de pago</option>
                            </select>
                            <button onclick="comprarCurso(${curso.id})" class="btn btn-success">
                                <i class="fas fa-credit-card"></i>
                                Comprar Curso
                            </button>
                        </div>
                    </div>
                `;
            });

            // 🔧 LLENAR SELECTORES DESPUÉS DE CREAR EL HTML
            console.log('🎯 Llenando selectores con métodos:', metodos);
            disponibles.forEach(curso => {
                const select = document.getElementById(`pago_${curso.id}`);
                if (select && Array.isArray(metodos) && metodos.length > 0) {
                    console.log(`📝 Llenando selector para curso ${curso.id}`);
                    metodos.forEach(metodo => {
                        const option = document.createElement('option');
                        option.value = metodo.id;
                        option.textContent = metodo.nombre;
                        select.appendChild(option);
                        console.log(`➕ Agregada opción: ${metodo.nombre}`);
                    });
                } else {
                    console.warn(`⚠️ No se pudo llenar selector para curso ${curso.id}:`, {
                        selectExists: !!select,
                        metodosArray: Array.isArray(metodos),
                        metodosLength: metodos?.length || 0
                    });
                }
            });

        } catch (error) {
            console.error('Error al cargar cursos disponibles:', error);
            mostrarMensaje('Error al cargar cursos disponibles: ' + error.message, 'error');
        }
    }

    async function comprarCurso(idCurso) {
        const select = document.getElementById(`pago_${idCurso}`);
        if (!select.value) {
            mostrarMensaje('Selecciona un método de pago', 'error');
            return;
        }

        try {
            // Obtener precio del curso
            const cursoRes = await fetch(`/api/cursos?id=${idCurso}`);
            if (!cursoRes.ok) {
                throw new Error('Error al obtener información del curso');
            }

            const curso = await cursoRes.json();
            console.log('Curso para comprar:', curso);

            const compraData = {
                id_curso: idCurso,
                id_metodo_pago: parseInt(select.value),
                precio_pagado: curso.precio,
                id_cliente: id_cliente,
                llave_secreta: llave_secreta
            };

            console.log('Datos de compra:', compraData);

            const response = await fetch('/api/compras', {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(compraData)
            });

            console.log('Respuesta compra:', response.status);

            if (response.ok) {
                mostrarMensaje('¡Compra realizada exitosamente!');
                await cargarCursosDisponibles();
                await cargarCompras();
            } else {
                const errorData = await response.json();
                console.error('Error en compra:', errorData);
                mostrarMensaje('Error al procesar la compra: ' + (errorData.error || 'Error desconocido'), 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error al procesar la compra: ' + error.message, 'error');
        }
    }

    async function cargarCompras() {
        try {
            console.log('Cargando compras...');
            console.log('Parámetros:', { id_cliente, llave_secreta });

            const url = `/api/compras?id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
            console.log('URL compras:', url);

            const res = await fetch(url);
            console.log('Status compras:', res.status);
            console.log('Headers compras:', [...res.headers.entries()]);

            // Leer el texto de respuesta primero para debug
            const responseText = await res.text();
            console.log('Respuesta raw compras:', responseText.substring(0, 500));

            if (!res.ok) {
                console.error('Error HTTP en compras:', res.status, responseText);
                throw new Error(`Error ${res.status}: ${responseText}`);
            }

            // Intentar parsear JSON
            let compras;
            try {
                compras = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Error parsing JSON:', parseError);
                console.error('Respuesta completa:', responseText);
                throw new Error('Respuesta no es JSON válido');
            }

            console.log('Compras obtenidas:', compras);

            const cont = document.getElementById('listaCompras');

            if (!Array.isArray(compras) || compras.length === 0) {
                cont.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h3>No tienes compras realizadas</h3>
                        <p>Compra algún curso para verlo aquí</p>
                    </div>
                `;
                return;
            }

            cont.innerHTML = '';
            compras.forEach(c => {
                const fechaCompra = new Date(c.fecha_compra).toLocaleDateString('es-ES');
                cont.innerHTML += `
                    <div class="curso curso-comprado">
                        <div class="curso-info">
                            <h3>${c.cursos?.titulo || 'Curso no disponible'}</h3>
                            <p><strong>Instructor:</strong> ${c.cursos?.instructor || 'N/A'}</p>
                            <div class="curso-compra-info">
                                <div class="precio-pagado">
                                    <i class="fas fa-dollar-sign"></i>
                                    Pagado: ${c.precio_pagado}
                                </div>
                                <div class="fecha-compra">
                                    <i class="fas fa-calendar"></i>
                                    Fecha: ${fechaCompra}
                                </div>
                                <div class="metodo-pago">
                                    <i class="fas fa-credit-card"></i>
                                    Método: ${c.metodos_pago?.nombre || 'No especificado'}
                                </div>
                            </div>
                            ${c.cursos?.imagen ? `
                                <div class="curso-imagen">
                                    <img src="${c.cursos.imagen}" alt="${c.cursos.titulo}">
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
        } catch (error) {
            console.error('Error al cargar compras:', error);
            mostrarMensaje('Error al cargar tus compras: ' + error.message, 'error');

            // Mostrar estado de error
            const cont = document.getElementById('listaCompras');
            cont.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error al cargar compras</h3>
                    <p>${error.message}</p>
                    <button onclick="cargarCompras()" class="btn btn-primary">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                </div>
            `;
        }
    }

    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('modalEdicion');
        if (e.target === modal) {
            cerrarModalEdicion();
        }
    });
</script>
</body>
</html>