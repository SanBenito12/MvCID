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

<script>
    let id_creador = null;

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            // Validar sesión usando la API corregida
            const auth = await fetch(`/api/auth?id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`);
            const data = await auth.json();

            if (!data.valido) {
                alert("Sesión inválida. Redirigiendo...");
                location.href = "/login";
                return;
            }

            id_creador = data.id_creador;

            // Cargar datos iniciales
            await cargarCursos();
            await cargarCursosDisponibles();
            await cargarMetodosPago();
            await cargarCompras();

            // Event listeners
            document.getElementById('formCurso').addEventListener('submit', crearCurso);
        } catch (error) {
            console.error('Error al inicializar:', error);
            mostrarMensaje('Error al cargar la página', 'error');
        }
    });

    function mostrarMensaje(texto, tipo = 'success') {
        const mensaje = document.getElementById('mensaje');
        mensaje.textContent = texto;
        mensaje.className = `mensaje ${tipo === 'error' ? 'mensaje-error' : ''}`;
        mensaje.style.display = 'block';

        setTimeout(() => {
            mensaje.style.display = 'none';
        }, 5000);
    }

    async function cargarCursos() {
        try {
            const res = await fetch(`/api/cursos?id_creador=${id_creador}`);
            const cursos = await res.json();
            const cont = document.getElementById('listaCursos');

            if (cursos.length === 0) {
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
            mostrarMensaje('Error al cargar tus cursos', 'error');
        }
    }

    async function eliminarCurso(id) {
        if (!confirm("¿Estás seguro de eliminar este curso?")) return;

        try {
            const response = await fetch(`/api/cursos?id=${id}`, { method: "DELETE" });
            if (response.ok) {
                mostrarMensaje('Curso eliminado exitosamente');
                await cargarCursos();
                await cargarCursosDisponibles(); // Refrescar cursos disponibles
            } else {
                mostrarMensaje('Error al eliminar el curso', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error al eliminar el curso', 'error');
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
                id_creador,
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString()
            };

            const response = await fetch('/api/cursos', {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(nuevoCurso)
            });

            if (response.ok) {
                mostrarMensaje('Curso creado exitosamente');
                form.reset();
                await cargarCursos();
                await cargarCursosDisponibles();
            } else {
                mostrarMensaje('Error al crear el curso', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error al crear el curso', 'error');
        }
    }

    async function cargarCursosDisponibles() {
        try {
            const resTodos = await fetch(`/api/cursos`);
            const resCompras = await fetch(`/api/compras?id_cliente=${id_creador}`);
            const cursos = await resTodos.json();
            const compras = await resCompras.json();
            const yaComprados = compras.map(c => c.id_curso);

            // Filtrar cursos: no creados por el usuario y no comprados
            const disponibles = cursos.filter(c =>
                c.id_creador !== id_creador && !yaComprados.includes(c.id)
            );

            const cont = document.getElementById('cursosDisponibles');

            if (disponibles.length === 0) {
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
                                <span class="precio">$${curso.precio}</span>
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

            // Llenar selectores de métodos de pago
            disponibles.forEach(curso => {
                const select = document.getElementById(`pago_${curso.id}`);
                if (select && metodos) {
                    metodos.forEach(metodo => {
                        const option = document.createElement('option');
                        option.value = metodo.id;
                        option.textContent = metodo.nombre;
                        select.appendChild(option);
                    });
                }
            });
        } catch (error) {
            console.error('Error al cargar cursos disponibles:', error);
            mostrarMensaje('Error al cargar cursos disponibles', 'error');
        }
    }

    let metodos = [];
    async function cargarMetodosPago() {
        try {
            const res = await fetch(`/backend/api/metodos_pago.php`);
            metodos = await res.json();
        } catch (error) {
            console.error('Error al cargar métodos de pago:', error);
            // Crear métodos por defecto si no existen
            metodos = [
                { id: 1, nombre: 'Tarjeta de Crédito' },
                { id: 2, nombre: 'PayPal' },
                { id: 3, nombre: 'Transferencia Bancaria' }
            ];
        }
    }

    async function comprarCurso(idCurso) {
        const select = document.getElementById(`pago_${idCurso}`);
        if (!select.value) {
            mostrarMensaje('Selecciona un método de pago', 'error');
            return;
        }

        try {
            const cursoRes = await fetch(`/api/cursos`);
            const cursos = await cursoRes.json();
            const curso = cursos.find(c => c.id === idCurso);

            if (!curso) {
                mostrarMensaje('Curso no encontrado', 'error');
                return;
            }

            const response = await fetch('/api/compras', {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    id_cliente: id_creador,
                    id_curso: idCurso,
                    id_metodo_pago: parseInt(select.value),
                    precio_pagado: curso.precio,
                    fecha_compra: new Date().toISOString()
                })
            });

            if (response.ok) {
                mostrarMensaje('¡Compra realizada exitosamente!');
                await cargarCursosDisponibles();
                await cargarCompras();
            } else {
                mostrarMensaje('Error al procesar la compra', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error al procesar la compra', 'error');
        }
    }

    async function cargarCompras() {
        try {
            const res = await fetch(`/api/compras?id_cliente=${id_creador}`);
            const compras = await res.json();
            const cont = document.getElementById('listaCompras');

            if (compras.length === 0) {
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
                                    Pagado: $${c.precio_pagado}
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
            mostrarMensaje('Error al cargar tus compras', 'error');
        }
    }
</script>
</body>
</html>