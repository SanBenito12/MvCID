// ===============================================
// CARRITO JAVASCRIPT - GESTIÓN COMPLETA
// ===============================================

// Variables globales
let carritoData = null;
let metodosPago = [];
let recomendaciones = [];

// ===============================================
// INICIALIZACIÓN
// ===============================================
document.addEventListener('DOMContentLoaded', async () => {
    try {
        console.log('🛒 Iniciando carrito de compras...');
        mostrarLoading(true);

        // Cargar datos iniciales
        await cargarMetodosPago();
        await cargarCarrito();
        await cargarRecomendaciones();

        console.log('✅ Carrito inicializado correctamente');
    } catch (error) {
        console.error('❌ Error al inicializar carrito:', error);
        mostrarMensaje('Error al cargar el carrito: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
});

// ===============================================
// CARGA DE DATOS
// ===============================================
async function cargarCarrito() {
    try {
        const url = `/api/carrito?accion=carrito&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${await response.text()}`);
        }

        const data = await response.json();

        if (data.success) {
            carritoData = data.carrito;
            renderizarCarrito();
            actualizarResumen();
            actualizarContador();
        } else {
            throw new Error(data.error || 'Error al cargar carrito');
        }
    } catch (error) {
        console.error('❌ Error cargando carrito:', error);
        mostrarMensaje('Error al cargar el carrito: ' + error.message, 'error');
        mostrarCarritoVacio();
    }
}

async function cargarMetodosPago() {
    try {
        const rutas = [
            '/api/metodos-pago',
            '/api/metodos_pago',
            '/backend/api/metodos_pago.php',
            '/api-metodos'
        ];

        for (const ruta of rutas) {
            try {
                const response = await fetch(ruta);
                if (response.ok) {
                    metodosPago = await response.json();
                    if (Array.isArray(metodosPago) && metodosPago.length > 0) {
                        renderizarMetodosPago();
                        return;
                    }
                }
            } catch (error) {
                console.warn(`Error en ${ruta}:`, error.message);
            }
        }

        // Fallback
        metodosPago = [
            { id: 1, nombre: 'Tarjeta de Crédito' },
            { id: 2, nombre: 'PayPal' },
            { id: 3, nombre: 'Transferencia Bancaria' }
        ];
        renderizarMetodosPago();
    } catch (error) {
        console.error('❌ Error cargando métodos de pago:', error);
    }
}

async function cargarRecomendaciones() {
    try {
        const url = `/api/carrito?accion=recomendaciones&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}&limite=4`;
        const response = await fetch(url);

        if (response.ok) {
            const data = await response.json();
            if (data.success && data.recomendaciones.length > 0) {
                recomendaciones = data.recomendaciones;
                renderizarRecomendaciones();
            }
        }
    } catch (error) {
        console.warn('⚠️ Error cargando recomendaciones:', error);
    }
}

// ===============================================
// RENDERIZADO
// ===============================================
function renderizarCarrito() {
    const container = document.getElementById('lista-items');

    if (!carritoData || !carritoData.items || carritoData.items.length === 0) {
        mostrarCarritoVacio();
        return;
    }

    let html = '';
    carritoData.items.forEach(item => {
        html += crearItemHTML(item);
    });

    container.innerHTML = html;

    // Mostrar botón vaciar si hay items
    const btnVaciar = document.getElementById('btn-vaciar');
    if (btnVaciar) {
        btnVaciar.style.display = carritoData.items.length > 0 ? 'inline-flex' : 'none';
    }
}

function crearItemHTML(item) {
    const curso = item.cursos;
    const precio = parseFloat(item.precio_curso);

    return `
        <div class="item-carrito" data-curso-id="${curso.id}">
            <div class="item-imagen">
                <img src="${curso.imagen || '/assets/images/curso-default.jpg'}" 
                     alt="${escapeHtml(curso.titulo)}"
                     onerror="this.src='/assets/images/curso-default.jpg'">
            </div>
            <div class="item-info">
                <div class="item-titulo">${escapeHtml(curso.titulo)}</div>
                <div class="item-instructor">
                    <i class="fas fa-user-tie"></i>
                    ${escapeHtml(curso.instructor)}
                </div>
                <div class="item-precio">$${formatearPrecio(precio)}</div>
            </div>
            <div class="item-acciones">
                <button class="btn-eliminar" onclick="eliminarDelCarrito(${curso.id})">
                    <i class="fas fa-trash"></i>
                    Eliminar
                </button>
            </div>
        </div>
    `;
}

function mostrarCarritoVacio() {
    const container = document.getElementById('lista-items');
    container.innerHTML = `
        <div class="carrito-vacio">
            <i class="fas fa-shopping-cart"></i>
            <h3>Tu carrito está vacío</h3>
            <p>Agrega algunos cursos desde el dashboard para comenzar tu aprendizaje</p>
            <a href="/dashboard" class="btn btn-primary" style="margin-top: var(--spacing-lg);">
                <i class="fas fa-search"></i>
                Explorar Cursos
            </a>
        </div>
    `;

    // Ocultar botón vaciar
    const btnVaciar = document.getElementById('btn-vaciar');
    if (btnVaciar) {
        btnVaciar.style.display = 'none';
    }

    // Deshabilitar botón procesar
    const btnProcesar = document.getElementById('btn-procesar');
    if (btnProcesar) {
        btnProcesar.disabled = true;
    }
}

function renderizarMetodosPago() {
    const select = document.getElementById('metodo-pago');
    if (!select) return;

    let html = '<option value="">Seleccionar método de pago</option>';
    metodosPago.forEach(metodo => {
        html += `<option value="${metodo.id}">${escapeHtml(metodo.nombre)}</option>`;
    });

    select.innerHTML = html;

    // Event listener para habilitar/deshabilitar botón procesar
    select.addEventListener('change', () => {
        const btnProcesar = document.getElementById('btn-procesar');
        const tieneItems = carritoData && carritoData.items && carritoData.items.length > 0;
        if (btnProcesar) {
            btnProcesar.disabled = !select.value || !tieneItems;
        }
    });
}

function renderizarRecomendaciones() {
    if (!recomendaciones || recomendaciones.length === 0) return;

    const section = document.getElementById('recomendaciones-section');
    const grid = document.getElementById('recomendaciones-grid');

    let html = '';
    recomendaciones.forEach(curso => {
        html += `
            <div class="curso-recomendado">
                <img src="${curso.imagen || '/assets/images/curso-default.jpg'}" 
                     alt="${escapeHtml(curso.titulo)}"
                     style="width: 100%; height: 150px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: var(--spacing-md);"
                     onerror="this.src='/assets/images/curso-default.jpg'">
                <h4 style="color: var(--text-primary); margin-bottom: var(--spacing-sm);">
                    ${escapeHtml(curso.titulo)}
                </h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--spacing-md);">
                    <i class="fas fa-user-tie"></i> ${escapeHtml(curso.instructor)}
                </p>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 1.2rem; font-weight: 700; color: var(--accent-green);">
                        $${formatearPrecio(curso.precio)}
                    </span>
                    <button class="btn btn-primary btn-sm" onclick="agregarAlCarrito(${curso.id})">
                        <i class="fas fa-plus"></i>
                        Agregar
                    </button>
                </div>
            </div>
        `;
    });

    grid.innerHTML = html;
    section.style.display = 'block';
}

function actualizarContador() {
    const contador = document.getElementById('contador-items');
    const cantidad = carritoData && carritoData.items ? carritoData.items.length : 0;

    if (contador) {
        contador.textContent = cantidad;
        contador.style.display = cantidad > 0 ? 'inline' : 'none';
    }
}

function actualizarResumen() {
    const cantidadEl = document.getElementById('resumen-cantidad');
    const subtotalEl = document.getElementById('resumen-subtotal');
    const totalEl = document.getElementById('resumen-total');

    let cantidad = 0;
    let total = 0;

    if (carritoData && carritoData.items) {
        cantidad = carritoData.items.length;
        total = carritoData.items.reduce((sum, item) => sum + parseFloat(item.precio_curso), 0);
    }

    if (cantidadEl) cantidadEl.textContent = cantidad;
    if (subtotalEl) subtotalEl.textContent = `${formatearPrecio(total)}`;
    if (totalEl) totalEl.textContent = `${formatearPrecio(total)}`;

    // Actualizar estado del botón procesar
    const btnProcesar = document.getElementById('btn-procesar');
    const metodoPago = document.getElementById('metodo-pago');
    if (btnProcesar) {
        btnProcesar.disabled = cantidad === 0 || !metodoPago?.value;
    }
}

// ===============================================
// OPERACIONES DEL CARRITO
// ===============================================
async function agregarAlCarrito(idCurso) {
    try {
        mostrarLoading(true);

        const response = await fetch('/api/carrito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'agregar',
                id_curso: idCurso,
                id_cliente: id_cliente,
                llave_secreta: llave_secreta
            })
        });

        const data = await response.json();

        if (data.success) {
            mostrarMensaje('✅ Curso agregado al carrito', 'success');
            await cargarCarrito();
            await cargarRecomendaciones(); // Actualizar recomendaciones
        } else {
            mostrarMensaje('Error: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('❌ Error agregando al carrito:', error);
        mostrarMensaje('Error al agregar curso al carrito', 'error');
    } finally {
        mostrarLoading(false);
    }
}

async function eliminarDelCarrito(idCurso) {
    mostrarConfirmacion(
        `¿Estás seguro de eliminar este curso del carrito?`,
        async () => {
            try {
                mostrarLoading(true);

                const url = `/api/carrito?accion=eliminar&id_curso=${idCurso}&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
                const response = await fetch(url, { method: 'DELETE' });

                const data = await response.json();

                if (data.success) {
                    mostrarMensaje('✅ Curso eliminado del carrito', 'success');
                    await cargarCarrito();
                    await cargarRecomendaciones(); // Actualizar recomendaciones
                } else {
                    mostrarMensaje('Error: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('❌ Error eliminando del carrito:', error);
                mostrarMensaje('Error al eliminar curso del carrito', 'error');
            } finally {
                mostrarLoading(false);
            }
        }
    );
}

async function vaciarCarrito() {
    mostrarConfirmacion(
        '¿Estás seguro de vaciar completamente tu carrito?\n\nEsta acción eliminará todos los cursos seleccionados.',
        async () => {
            try {
                mostrarLoading(true);

                const url = `/api/carrito?accion=vaciar&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
                const response = await fetch(url, { method: 'DELETE' });

                const data = await response.json();

                if (data.success) {
                    mostrarMensaje('✅ Carrito vaciado exitosamente', 'success');
                    await cargarCarrito();
                    await cargarRecomendaciones();
                } else {
                    mostrarMensaje('Error: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('❌ Error vaciando carrito:', error);
                mostrarMensaje('Error al vaciar el carrito', 'error');
            } finally {
                mostrarLoading(false);
            }
        }
    );
}

async function sincronizarCarrito() {
    try {
        mostrarLoading(true);

        const url = `/api/carrito?accion=sincronizar&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(url);

        const data = await response.json();

        if (data.success) {
            const mensaje = data.items_eliminados > 0
                ? `✅ Carrito sincronizado. ${data.items_eliminados} item(s) eliminado(s).`
                : '✅ Carrito sincronizado. No hay cambios.';

            mostrarMensaje(mensaje, 'success');
            await cargarCarrito();
        } else {
            mostrarMensaje('Error: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('❌ Error sincronizando carrito:', error);
        mostrarMensaje('Error al sincronizar el carrito', 'error');
    } finally {
        mostrarLoading(false);
    }
}

async function procesarCompra() {
    const metodoPago = document.getElementById('metodo-pago').value;

    if (!metodoPago) {
        mostrarMensaje('Por favor selecciona un método de pago', 'error');
        return;
    }

    if (!carritoData || !carritoData.items || carritoData.items.length === 0) {
        mostrarMensaje('Tu carrito está vacío', 'error');
        return;
    }

    const total = carritoData.items.reduce((sum, item) => sum + parseFloat(item.precio_curso), 0);
    const cantidad = carritoData.items.length;

    mostrarConfirmacion(
        `¿Confirmas la compra de ${cantidad} curso(s) por un total de ${formatearPrecio(total)}?\n\nEsta acción procesará el pago y vaciará tu carrito.`,
        async () => {
            try {
                mostrarLoading(true);

                const response = await fetch('/api/carrito', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        accion: 'procesar',
                        id_metodo_pago: parseInt(metodoPago),
                        id_cliente: id_cliente,
                        llave_secreta: llave_secreta
                    })
                });

                const data = await response.json();

                if (data.success) {
                    mostrarMensaje(
                        `🎉 ¡Compra procesada exitosamente!\n\n${data.compras_creadas} curso(s) adquirido(s) por ${formatearPrecio(data.total_pagado)}.\n\nTus cursos ya están disponibles en el dashboard.`,
                        'success'
                    );

                    // Limpiar carrito y recargar
                    await cargarCarrito();

                    // Resetear método de pago
                    document.getElementById('metodo-pago').value = '';

                    // Redirigir al dashboard después de un momento
                    setTimeout(() => {
                        window.location.href = '/dashboard?tab=mis-compras';
                    }, 3000);
                } else {
                    mostrarMensaje('Error al procesar la compra: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('❌ Error procesando compra:', error);
                mostrarMensaje('Error al procesar la compra', 'error');
            } finally {
                mostrarLoading(false);
            }
        }
    );
}

// ===============================================
// FUNCIONES DE UTILIDAD
// ===============================================
function mostrarMensaje(texto, tipo = 'success') {
    const mensaje = document.getElementById('mensaje');
    if (!mensaje) return;

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

    mensaje.innerHTML = `${icono} ${texto.replace(/\n/g, '<br>')}`;
    mensaje.style.display = 'block';

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

function mostrarConfirmacion(mensaje, callback) {
    const modal = document.getElementById('modalConfirmacion');
    const textoElement = document.getElementById('confirmacionTexto');
    const btnConfirmar = document.getElementById('btnConfirmar');

    if (textoElement && btnConfirmar && modal) {
        textoElement.innerHTML = mensaje.replace(/\n/g, '<br>');
        btnConfirmar.onclick = () => {
            callback();
            cerrarModalConfirmacion();
        };
        mostrarModal('modalConfirmacion');
    }
}

function mostrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModalConfirmacion() {
    const modal = document.getElementById('modalConfirmacion');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

function formatearPrecio(precio) {
    return parseFloat(precio || 0).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function escapeHtml(text) {
    if (typeof text !== 'string') return text;

    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// ===============================================
// EVENT LISTENERS GLOBALES
// ===============================================

// Cerrar modales al hacer clic fuera
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        cerrarModalConfirmacion();
    }
});

// Tecla ESC para cerrar modales
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarModalConfirmacion();
    }
});

// Manejar errores globales
window.addEventListener('error', function(e) {
    console.error('Error global capturado:', e.error);
    mostrarMensaje('Se produjo un error inesperado. Por favor, recarga la página.', 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Promise rechazada no manejada:', e.reason);
    mostrarMensaje('Error de conexión. Verifica tu conexión a internet.', 'error');
});

// ===============================================
// FUNCIONES EXPUESTAS GLOBALMENTE
// ===============================================
window.agregarAlCarrito = agregarAlCarrito;
window.eliminarDelCarrito = eliminarDelCarrito;
window.vaciarCarrito = vaciarCarrito;
window.sincronizarCarrito = sincronizarCarrito;
window.procesarCompra = procesarCompra;
window.cerrarModalConfirmacion = cerrarModalConfirmacion;

// ===============================================
// ACTUALIZACIÓN AUTOMÁTICA (OPCIONAL)
// ===============================================

// Sincronización automática cada 5 minutos
setInterval(async () => {
    try {
        await sincronizarCarrito();
    } catch (error) {
        console.warn('Error en sincronización automática:', error);
    }
}, 5 * 60 * 1000);

// Verificar estado al volver a la pestaña
document.addEventListener('visibilitychange', async () => {
    if (!document.hidden) {
        try {
            await cargarCarrito();
        } catch (error) {
            console.warn('Error al recargar carrito:', error);
        }
    }
});

console.log('✅ Carrito JavaScript inicializado correctamente');