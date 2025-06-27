// ===============================================
// DASHBOARD JAVASCRIPT - VERSIÓN SIMPLIFICADA
// ===============================================

// Variables globales
let id_creador = null;
let metodos = [];
let cursoEnEdicion = null;
let tabActual = 'mis-cursos';
let vistaActual = 'grid';

// Datos en caché
let misCursos = [];
let cursosDisponibles = [];
let misCompras = [];

// ===============================================
// INICIALIZACIÓN
// ===============================================
document.addEventListener('DOMContentLoaded', async () => {
    try {
        console.log('🚀 Iniciando dashboard...');
        mostrarLoading(true);

        // Validar sesión
        await validarSesion();

        // Cargar datos iniciales
        await cargarDatosIniciales();

        // Configurar event listeners
        configurarEventListeners();

        // Configurar búsquedas y filtros
        configurarBusquedas();
        configurarFiltros();

        console.log('✅ Dashboard inicializado correctamente');
        mostrarMensaje('Dashboard cargado correctamente', 'success');

    } catch (error) {
        console.error('❌ Error al inicializar dashboard:', error);
        mostrarMensaje('Error al cargar el dashboard: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
});

// ===============================================
// VALIDACIÓN DE SESIÓN
// ===============================================
async function validarSesion() {
    try {
        const authUrl = `/api/auth?id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(authUrl);
        const data = await response.json();

        if (!data.valido) {
            throw new Error('Sesión inválida');
        }

        id_creador = data.id_creador;
        console.log('✅ Sesión válida - ID creador:', id_creador);
        return data;
    } catch (error) {
        console.error('❌ Error de validación:', error);
        alert('Sesión inválida. Redirigiendo al login...');
        window.location.href = '/login';
        throw error;
    }
}

// ===============================================
// CARGA DE DATOS INICIALES
// ===============================================
async function cargarDatosIniciales() {
    try {
        await cargarMetodosPago();
        await cargarMisCursos();
        await cargarCursosDisponibles();
        await cargarMisCompras();
        actualizarEstadisticas();
    } catch (error) {
        console.error('❌ Error cargando datos iniciales:', error);
        throw error;
    }
}

// ===============================================
// DROPDOWN FUNCTIONALITY CORREGIDO - AGREGAR AL FINAL DE dashboard.js
// ===============================================

// Función principal para toggle del dropdown - MEJORADA
window.toggleDropdown = function() {
    console.log('🔽 Toggle dropdown called');
    
    const dropdown = document.getElementById('dropdown-menu');
    const dropdownContainer = document.querySelector('.dropdown');
    const configBtn = document.querySelector('.config-btn');
    
    if (!dropdown) {
        console.error('❌ Dropdown menu element not found');
        return;
    }
    
    if (!dropdownContainer) {
        console.error('❌ Dropdown container not found');
        return;
    }
    
    const isOpen = dropdown.classList.contains('show');
    
    console.log('📊 Estado actual:', {
        isOpen: isOpen,
        dropdown: !!dropdown,
        container: !!dropdownContainer,
        display: window.getComputedStyle(dropdown).display,
        opacity: window.getComputedStyle(dropdown).opacity,
        zIndex: window.getComputedStyle(dropdown).zIndex
    });
    
    if (isOpen) {
        // Cerrar dropdown
        dropdown.classList.remove('show');
        dropdownContainer.classList.remove('active');
        dropdown.style.pointerEvents = 'none';
        console.log('📤 Dropdown cerrado');
    } else {
        // Cerrar otros dropdowns primero
        closeAllDropdowns();
        
        // Abrir dropdown con timeout para asegurar renderizado
        setTimeout(() => {
            dropdown.classList.add('show');
            dropdownContainer.classList.add('active');
            dropdown.style.pointerEvents = 'auto';
            
            // Force reflow para asegurar que se renderice
            dropdown.offsetHeight;
            
            console.log('📥 Dropdown abierto');
            
            // Debug: verificar estilos después de abrir
            const computedStyle = window.getComputedStyle(dropdown);
            console.log('🔍 Estilos después de abrir:', {
                display: computedStyle.display,
                opacity: computedStyle.opacity,
                visibility: computedStyle.visibility,
                transform: computedStyle.transform,
                zIndex: computedStyle.zIndex,
                position: computedStyle.position
            });
        }, 10);
    }
};

// Función para cerrar dropdown
window.closeDropdown = function() {
    const dropdown = document.getElementById('dropdown-menu');
    const dropdownContainer = document.querySelector('.dropdown');
    
    if (dropdown && dropdownContainer) {
        dropdown.classList.remove('show');
        dropdownContainer.classList.remove('active');
        dropdown.style.pointerEvents = 'none';
        console.log('🔒 Dropdown cerrado programáticamente');
    }
};

// Función para cerrar todos los dropdowns
function closeAllDropdowns() {
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    const allContainers = document.querySelectorAll('.dropdown');
    
    allDropdowns.forEach(dropdown => {
        dropdown.classList.remove('show');
        dropdown.style.pointerEvents = 'none';
    });
    
    allContainers.forEach(container => {
        container.classList.remove('active');
    });
}

// Event listener para clicks fuera del dropdown - MEJORADO
document.addEventListener('click', function(event) {
    const dropdownContainer = document.querySelector('.dropdown');
    const dropdown = document.getElementById('dropdown-menu');
    
    // Verificar si el click fue dentro del dropdown o su contenedor
    if (dropdownContainer && !dropdownContainer.contains(event.target)) {
        console.log('👆 Click fuera del dropdown, cerrando...');
        closeDropdown();
    } else if (dropdownContainer && dropdownContainer.contains(event.target)) {
        console.log('👆 Click dentro del dropdown');
        
        // Si es un link, cerrar después de un delay
        if (event.target.tagName === 'A' || event.target.closest('a')) {
            setTimeout(() => {
                closeDropdown();
            }, 150);
        }
    }
});

// Event listener para tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        console.log('⌨️ ESC presionado, cerrando dropdown');
        closeDropdown();
    }
});

// Inicialización mejorada del dropdown
function initDropdown() {
    console.log('🚀 Inicializando dropdown functionality...');
    
    const dropdown = document.getElementById('dropdown-menu');
    const configBtn = document.querySelector('.config-btn');
    const dropdownContainer = document.querySelector('.dropdown');
    
    // Verificar elementos
    console.log('🔍 Elementos encontrados:', {
        dropdown: !!dropdown,
        configBtn: !!configBtn,
        container: !!dropdownContainer
    });
    
    if (!dropdown) {
        console.error('❌ Elemento dropdown-menu no encontrado');
        return false;
    }
    
    if (!configBtn) {
        console.error('❌ Botón config-btn no encontrado');
        return false;
    }
    
    // Configurar estilos iniciales forzados
    dropdown.style.position = 'absolute';
    dropdown.style.zIndex = '9999';
    dropdown.style.pointerEvents = 'none';
    
    // Event listeners para los items del dropdown
    const dropdownItems = dropdown.querySelectorAll('.dropdown-item');
    
    dropdownItems.forEach((item, index) => {
        console.log(`📋 Configurando item ${index + 1}: ${item.textContent.trim()}`);
        
        // Limpiar event listeners existentes
        item.removeEventListener('click', handleDropdownItemClick);
        item.addEventListener('click', handleDropdownItemClick);
        
        // Agregar efectos de hover
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(6px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Prevenir que clicks dentro del dropdown lo cierren
    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        console.log('👆 Click dentro del dropdown menu');
    });
    
    // Configurar botón principal
    configBtn.removeEventListener('click', toggleDropdown);
    configBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown();
    });
    
    // Soporte para teclado
    configBtn.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleDropdown();
        }
    });
    
    console.log('✅ Dropdown functionality initialized');
    console.log('📊 Items encontrados:', dropdownItems.length);
    
    return true;
}

// Handler para clicks en items del dropdown
function handleDropdownItemClick(e) {
    console.log(`🔗 Click en: ${this.textContent.trim()}`);
    
    // Si es un enlace válido, cerrar dropdown después de un delay
    if (this.href && !this.href.includes('javascript:')) {
        setTimeout(() => {
            closeDropdown();
        }, 100);
    } else if (this.onclick) {
        // Si tiene onclick, ejecutar y cerrar
        setTimeout(() => {
            closeDropdown();
        }, 100);
    }
}

// Función de debug mejorada
window.debugDropdown = function() {
    const dropdown = document.getElementById('dropdown-menu');
    const container = document.querySelector('.dropdown');
    const items = document.querySelectorAll('.dropdown-item');
    const configBtn = document.querySelector('.config-btn');
    
    console.log('🔍 DROPDOWN DEBUG AVANZADO:');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    
    // Elementos
    console.log('📦 ELEMENTOS:');
    console.log('  - Dropdown exists:', !!dropdown);
    console.log('  - Container exists:', !!container);
    console.log('  - Config button exists:', !!configBtn);
    console.log('  - Items count:', items.length);
    
    // Estados
    if (dropdown) {
        const isOpen = dropdown.classList.contains('show');
        console.log('📊 ESTADOS:');
        console.log('  - Is open:', isOpen);
        console.log('  - Container active:', container ? container.classList.contains('active') : false);
        console.log('  - Classes:', dropdown.className);
        
        // Estilos computados
        const styles = window.getComputedStyle(dropdown);
        console.log('🎨 ESTILOS COMPUTADOS:');
        console.log('  - Display:', styles.display);
        console.log('  - Opacity:', styles.opacity);
        console.log('  - Visibility:', styles.visibility);
        console.log('  - Transform:', styles.transform);
        console.log('  - Z-Index:', styles.zIndex);
        console.log('  - Position:', styles.position);
        console.log('  - Top:', styles.top);
        console.log('  - Right:', styles.right);
        console.log('  - Width:', styles.width);
        console.log('  - Height:', styles.height);
        
        // Posición en viewport
        const rect = dropdown.getBoundingClientRect();
        console.log('📍 POSICIÓN EN VIEWPORT:');
        console.log('  - Top:', rect.top);
        console.log('  - Right:', rect.right);
        console.log('  - Bottom:', rect.bottom);
        console.log('  - Left:', rect.left);
        console.log('  - Width:', rect.width);
        console.log('  - Height:', rect.height);
        console.log('  - Visible:', rect.width > 0 && rect.height > 0);
    }
    
    // Items
    console.log('📋 ITEMS:');
    items.forEach((item, i) => {
        console.log(`  ${i + 1}. ${item.textContent.trim()}`);
        console.log(`     - Href: ${item.href || 'N/A'}`);
        console.log(`     - Visible: ${item.offsetWidth > 0 && item.offsetHeight > 0}`);
    });
    
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
};

// Función para forzar visibilidad (para debugging)
window.forceDropdownVisible = function() {
    const dropdown = document.getElementById('dropdown-menu');
    if (dropdown) {
        dropdown.classList.add('force-visible');
        console.log('🔧 Dropdown forzado a visible');
    }
};

// Función para remover forzado
window.removeForceVisible = function() {
    const dropdown = document.getElementById('dropdown-menu');
    if (dropdown) {
        dropdown.classList.remove('force-visible');
        console.log('🔧 Forzado removido');
    }
};

// Función para re-inicializar si es necesario
window.reinitDropdown = function() {
    console.log('🔄 Re-inicializando dropdown...');
    
    // Limpiar estado
    closeAllDropdowns();
    
    // Re-inicializar
    setTimeout(() => {
        initDropdown();
        console.log('✅ Dropdown re-inicializado');
    }, 100);
};

// Event listener principal para inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 DOM loaded, inicializando dropdown...');
    
    // Esperar un poco para asegurar que todos los estilos estén cargados
    setTimeout(() => {
        const success = initDropdown();
        
        if (success) {
            console.log('✅ Dropdown inicializado correctamente');
            
            // Auto-debug en desarrollo
            if (window.location.hostname === 'localhost') {
                setTimeout(() => {
                    console.log('🔧 Auto-debug dropdown (desarrollo):');
                    debugDropdown();
                }, 1000);
            }
        } else {
            console.error('❌ Error al inicializar dropdown');
        }
    }, 500);
});

// Observer para detectar cambios en el DOM que puedan afectar el dropdown
if (window.MutationObserver) {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                const addedNodes = Array.from(mutation.addedNodes);
                const removedNodes = Array.from(mutation.removedNodes);
                
                // Si se agregó o removió el dropdown, re-inicializar
                const affectsDropdown = [...addedNodes, ...removedNodes].some(node => {
                    return node.nodeType === Node.ELEMENT_NODE && 
                           (node.classList?.contains('dropdown') || 
                            node.querySelector?.('.dropdown'));
                });
                
                if (affectsDropdown) {
                    console.log('🔄 Cambio detectado en dropdown, re-inicializando...');
                    setTimeout(reinitDropdown, 100);
                }
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Exponer funciones globalmente
window.toggleDropdown = toggleDropdown;
window.closeDropdown = closeDropdown;
window.debugDropdown = debugDropdown;
window.reinitDropdown = reinitDropdown;
window.forceDropdownVisible = forceDropdownVisible;
window.removeForceVisible = removeForceVisible;

console.log('✅ Dropdown module loaded successfully with enhanced debugging');

// ===============================================
// GESTIÓN DE MÉTODOS DE PAGO - VERSIÓN SIMPLE
// ===============================================
async function cargarMetodosPago() {
    try {
        console.log('🔄 Cargando métodos de pago...');

        const rutas = [
            '/api/metodos-pago',            // Nueva ruta principal
            '/api/metodos_pago',            // Ruta alternativa con underscore
            '/backend/api/metodos_pago.php', // Ruta de respaldo directa
            '/api-metodos',                 // Compatibilidad hacia atrás
            '/api-metodos.php'              // Compatibilidad hacia atrás
        ];

        let metodosObtenidos = false;

        for (const ruta of rutas) {
            try {
                console.log(`🔍 Intentando: ${ruta}`);
                const response = await fetch(ruta);

                if (response.ok) {
                    const data = await response.text();
                    metodos = JSON.parse(data);

                    if (Array.isArray(metodos) && metodos.length > 0) {
                        console.log('✅ Métodos cargados desde', ruta, ':', metodos);
                        metodosObtenidos = true;
                        break;
                    }
                }
            } catch (error) {
                console.warn(`⚠️ Error en ${ruta}:`, error.message);
            }
        }

        if (!metodosObtenidos) {
            console.warn('⚠️ Usando métodos por defecto');
            metodos = [
                { id: 1, nombre: 'Tarjeta de Crédito' },
                { id: 2, nombre: 'PayPal' },
                { id: 3, nombre: 'Transferencia Bancaria' },
                { id: 4, nombre: 'OXXO / Efectivo' }
            ];
            mostrarMensaje('Usando métodos de pago por defecto', 'warning');
        }

        return metodos;
    } catch (error) {
        console.error('❌ Error crítico cargando métodos:', error);
        metodos = [
            { id: 1, nombre: 'Tarjeta de Crédito' },
            { id: 2, nombre: 'PayPal' },
            { id: 3, nombre: 'Transferencia Bancaria' }
        ];
        return metodos;
    }
}

// ===============================================
// GESTIÓN DE CURSOS
// ===============================================
async function cargarMisCursos() {
    try {
        console.log('📚 Cargando mis cursos...');

        const url = `/api/cursos?id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${await response.text()}`);
        }

        misCursos = await response.json();
        console.log('✅ Mis cursos cargados:', misCursos.length);

        renderizarMisCursos();
        return misCursos;
    } catch (error) {
        console.error('❌ Error cargando mis cursos:', error);
        mostrarErrorEnContenedor('listaCursos', 'Error al cargar tus cursos: ' + error.message);
        throw error;
    }
}

async function cargarCursosDisponibles() {
    try {
        console.log('🛒 Cargando cursos disponibles...');

        const url = `/api/cursos?disponibles=true&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${await response.text()}`);
        }

        cursosDisponibles = await response.json();
        console.log('✅ Cursos disponibles cargados:', cursosDisponibles.length);

        renderizarCursosDisponibles();
        return cursosDisponibles;
    } catch (error) {
        console.error('❌ Error cargando cursos disponibles:', error);
        mostrarErrorEnContenedor('cursosDisponibles', 'Error al cargar cursos disponibles: ' + error.message);
        throw error;
    }
}

async function cargarMisCompras() {
    try {
        console.log('🧾 Cargando mis compras...');

        const url = `/api/compras?id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${await response.text()}`);
        }

        misCompras = await response.json();
        console.log('✅ Mis compras cargadas:', misCompras.length);

        renderizarMisCompras();
        return misCompras;
    } catch (error) {
        console.error('❌ Error cargando mis compras:', error);
        mostrarErrorEnContenedor('listaCompras', 'Error al cargar tus compras: ' + error.message);
        throw error;
    }
}

// ===============================================
// CONFIGURACIÓN DE EVENT LISTENERS
// ===============================================
function configurarEventListeners() {
    // Formulario de crear curso
    const formCurso = document.getElementById('formCurso');
    if (formCurso) {
        formCurso.addEventListener('submit', crearCurso);
    }

    // Formulario de editar curso
    const formEditarCurso = document.getElementById('formEditarCurso');
    if (formEditarCurso) {
        formEditarCurso.addEventListener('submit', guardarCambiosCurso);
    }

    // Preview de imagen en formulario principal
    const imagenInput = document.querySelector('input[name="imagen"]');
    if (imagenInput) {
        imagenInput.addEventListener('change', previewImagen);
    }

    // Preview de imagen en modal de edición
    const editImagenInput = document.getElementById('editImagen');
    if (editImagenInput) {
        editImagenInput.addEventListener('change', previewImagenEdit);
    }

    // Cerrar modales al hacer clic fuera
    document.addEventListener('click', manejarClickFueraModal);

    // Tecla ESC para cerrar modales
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarTodosLosModales();
        }
    });
}

// ===============================================
// RENDERIZADO DE CONTENIDO
// ===============================================
function renderizarMisCursos() {
    const container = document.getElementById('listaCursos');
    if (!container) return;

    if (!Array.isArray(misCursos) || misCursos.length === 0) {
        container.innerHTML = crearEstadoVacio(
            'fas fa-book-open',
            'No tienes cursos creados',
            'Crea tu primer curso usando el formulario de arriba'
        );
        return;
    }

    container.innerHTML = '';
    misCursos.forEach(curso => {
        const cursoElement = crearTarjetaCurso(curso, 'mis-cursos');
        container.appendChild(cursoElement);
    });
}

function renderizarCursosDisponibles() {
    const container = document.getElementById('cursosDisponibles');
    if (!container) return;

    if (!Array.isArray(cursosDisponibles) || cursosDisponibles.length === 0) {
        container.innerHTML = crearEstadoVacio(
            'fas fa-shopping-cart',
            'No hay cursos disponibles',
            'Todos los cursos han sido adquiridos o no hay cursos de otros usuarios'
        );
        return;
    }

    container.innerHTML = '';
    cursosDisponibles.forEach(curso => {
        const cursoElement = crearTarjetaCurso(curso, 'disponibles');
        container.appendChild(cursoElement);
    });
}

function renderizarMisCompras() {
    const container = document.getElementById('listaCompras');
    if (!container) return;

    if (!Array.isArray(misCompras) || misCompras.length === 0) {
        container.innerHTML = crearEstadoVacio(
            'fas fa-receipt',
            'No tienes compras realizadas',
            'Compra algún curso para verlo aquí'
        );
        return;
    }

    container.innerHTML = '';
    misCompras.forEach(compra => {
        const compraElement = crearTarjetaCompra(compra);
        container.appendChild(compraElement);
    });
}

// ===============================================
// CREACIÓN DE ELEMENTOS
// ===============================================
function crearTarjetaCurso(curso, tipo) {
    const article = document.createElement('article');
    article.className = `curso ${tipo === 'disponibles' ? 'curso-disponible' : ''}`;
    article.setAttribute('data-curso-id', curso.id);

    const imagen = crearImagenCurso(curso);
    const info = crearInfoCurso(curso);
    const acciones = tipo === 'mis-cursos' ? crearAccionesMisCursos(curso) : crearAccionesDisponibles(curso);

    article.appendChild(imagen);
    article.appendChild(info);
    article.appendChild(acciones);

    return article;
}

function crearImagenCurso(curso) {
    const div = document.createElement('div');
    div.className = 'curso-imagen';

    const img = document.createElement('img');
    img.src = curso.imagen || '/assets/images/curso-default.jpg';
    img.alt = curso.titulo;
    img.loading = 'lazy';

    // Manejar error de imagen
    img.onerror = function() {
        this.src = '/assets/images/curso-default.jpg';
    };

    div.appendChild(img);
    return div;
}

function crearInfoCurso(curso) {
    const div = document.createElement('div');
    div.className = 'curso-info';

    div.innerHTML = `
        <h3>${escapeHtml(curso.titulo)}</h3>
        <p><strong>Instructor:</strong> ${escapeHtml(curso.instructor)}</p>
        <p><strong>Descripción:</strong> ${escapeHtml(curso.descripcion)}</p>
        <div class="curso-precio">
            <span class="precio">$${formatearPrecio(curso.precio)}</span>
        </div>
    `;

    return div;
}

function crearAccionesMisCursos(curso) {
    const div = document.createElement('div');
    div.className = 'curso-actions';

    const btnEditar = document.createElement('button');
    btnEditar.className = 'btn btn-warning';
    btnEditar.innerHTML = '<i class="fas fa-edit"></i> Editar';
    btnEditar.onclick = () => editarCurso(curso.id);

    const btnEliminar = document.createElement('button');
    btnEliminar.className = 'btn btn-danger';
    btnEliminar.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
    btnEliminar.onclick = () => mostrarConfirmacion(
        `¿Estás seguro de eliminar el curso "${curso.titulo}"?\n\nEsta acción también eliminará todas las compras asociadas y no se puede deshacer.`,
        () => eliminarCurso(curso.id)
    );

    div.appendChild(btnEditar);
    div.appendChild(btnEliminar);

    return div;
}

function crearAccionesDisponibles(curso) {
    const div = document.createElement('div');
    div.className = 'curso-compra';

    const select = document.createElement('select');
    select.id = `pago_${curso.id}`;
    select.className = 'form-group';

    const optionDefault = document.createElement('option');
    optionDefault.value = '';
    optionDefault.textContent = 'Seleccionar método de pago';
    select.appendChild(optionDefault);

    // Agregar métodos de pago
    if (Array.isArray(metodos)) {
        metodos.forEach(metodo => {
            const option = document.createElement('option');
            option.value = metodo.id;
            option.textContent = metodo.nombre;
            select.appendChild(option);
        });
    }

    const btnComprar = document.createElement('button');
    btnComprar.className = 'btn btn-success';
    btnComprar.innerHTML = '<i class="fas fa-credit-card"></i> Comprar Curso';
    btnComprar.onclick = () => comprarCurso(curso.id);

    div.appendChild(select);
    div.appendChild(btnComprar);

    return div;
}

function crearTarjetaCompra(compra) {
    const article = document.createElement('article');
    article.className = 'curso curso-comprado';

    const fechaCompra = new Date(compra.fecha_compra).toLocaleDateString('es-ES');

    article.innerHTML = `
        <div class="curso-info">
            <h3>${escapeHtml(compra.cursos?.titulo || 'Curso no disponible')}</h3>
            <p><strong>Instructor:</strong> ${escapeHtml(compra.cursos?.instructor || 'N/A')}</p>
            <div class="curso-compra-info">
                <div class="precio-pagado">
                    <i class="fas fa-dollar-sign"></i>
                    Pagado: $${formatearPrecio(compra.precio_pagado)}
                </div>
                <div class="fecha-compra">
                    <i class="fas fa-calendar"></i>
                    Fecha: ${fechaCompra}
                </div>
                <div class="metodo-pago">
                    <i class="fas fa-credit-card"></i>
                    Método: ${escapeHtml(compra.metodos_pago?.nombre || 'No especificado')}
                </div>
            </div>
            ${compra.cursos?.imagen ? `
                <div class="curso-imagen">
                    <img src="${compra.cursos.imagen}" alt="${escapeHtml(compra.cursos.titulo)}" loading="lazy">
                </div>
            ` : ''}
        </div>
    `;

    return article;
}

function crearEstadoVacio(icono, titulo, descripcion) {
    return `
        <div class="empty-state">
            <i class="${icono}"></i>
            <h3>${titulo}</h3>
            <p>${descripcion}</p>
        </div>
    `;
}

// ===============================================
// GESTIÓN DE CURSOS - CRUD
// ===============================================
async function crearCurso(e) {
    e.preventDefault();

    try {
        mostrarLoading(true);
        const form = e.target;
        const formData = new FormData(form);
        const imagen = formData.get('imagen');

        if (!imagen || imagen.size === 0) {
            throw new Error('Por favor selecciona una imagen para el curso');
        }

        // Subir imagen a Supabase
        const nombre = `${Date.now()}_${imagen.name}`;
        const uploadResponse = await fetch(`https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/cursos/${nombre}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${SUPABASE_SERVICE_ROLE_KEY}`,
                'x-upsert': 'true',
                'Content-Type': imagen.type
            },
            body: imagen
        });

        if (!uploadResponse.ok) {
            throw new Error('Error al subir la imagen');
        }

        const imagenURL = `https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/public/cursos/${nombre}`;

        // Crear curso
        const nuevoCurso = {
            titulo: formData.get('titulo'),
            instructor: formData.get('instructor'),
            descripcion: formData.get('descripcion'),
            precio: parseFloat(formData.get('precio')),
            imagen: imagenURL,
            id_cliente: id_cliente,
            llave_secreta: llave_secreta
        };

        const response = await fetch('/api/cursos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(nuevoCurso)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Error al crear el curso');
        }

        mostrarMensaje('✅ Curso creado exitosamente', 'success');
        form.reset();

        // Limpiar preview
        const preview = document.getElementById('filePreview');
        if (preview) {
            preview.innerHTML = '';
            preview.classList.remove('show');
        }

        // Recargar datos
        await cargarMisCursos();
        await cargarCursosDisponibles();
        actualizarEstadisticas();

    } catch (error) {
        console.error('❌ Error creando curso:', error);
        mostrarMensaje('Error al crear el curso: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
}

async function editarCurso(id) {
    try {
        console.log('✏️ Editando curso ID:', id);

        const response = await fetch(`/api/cursos?id=${id}`);
        if (!response.ok) {
            throw new Error(`Error ${response.status} al obtener curso`);
        }

        const curso = await response.json();
        cursoEnEdicion = curso;

        // Llenar formulario de edición
        document.getElementById('editCursoId').value = curso.id;
        document.getElementById('editTitulo').value = curso.titulo;
        document.getElementById('editInstructor').value = curso.instructor;
        document.getElementById('editPrecio').value = curso.precio;
        document.getElementById('editDescripcion').value = curso.descripcion;
        document.getElementById('editImagenActual').src = curso.imagen;

        // Mostrar modal
        mostrarModal('modalEdicion');

    } catch (error) {
        console.error('❌ Error cargando curso para editar:', error);
        mostrarMensaje('Error al cargar curso: ' + error.message, 'error');
    }
}

async function guardarCambiosCurso(e) {
    e.preventDefault();

    if (!cursoEnEdicion) {
        mostrarMensaje('Error: No hay curso en edición', 'error');
        return;
    }

    try {
        mostrarLoading(true);
        const form = e.target;
        const formData = new FormData(form);
        const nuevaImagen = formData.get('imagen');

        let datosActualizados = {
            titulo: formData.get('titulo'),
            instructor: formData.get('instructor'),
            precio: parseFloat(formData.get('precio')),
            descripcion: formData.get('descripcion'),
            id_cliente: id_cliente,
            llave_secreta: llave_secreta
        };

        // Si hay nueva imagen, subirla
        if (nuevaImagen && nuevaImagen.size > 0) {
            const nombre = `${Date.now()}_${nuevaImagen.name}`;
            const upload = await fetch(`https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/cursos/${nombre}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${SUPABASE_SERVICE_ROLE_KEY}`,
                    'x-upsert': 'true',
                    'Content-Type': nuevaImagen.type
                },
                body: nuevaImagen
            });

            if (!upload.ok) {
                throw new Error('Error al subir nueva imagen');
            }

            datosActualizados.imagen = `https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/public/cursos/${nombre}`;
        }

        const response = await fetch(`/api/cursos?id=${cursoEnEdicion.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosActualizados)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Error al actualizar curso');
        }

        mostrarMensaje('✅ Curso actualizado exitosamente', 'success');
        cerrarModal('modalEdicion');

        // Recargar datos
        await cargarMisCursos();
        await cargarCursosDisponibles();

    } catch (error) {
        console.error('❌ Error actualizando curso:', error);
        mostrarMensaje('Error al actualizar curso: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
}

async function eliminarCurso(id) {
    try {
        mostrarLoading(true);

        const url = `/api/cursos?id=${id}&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(url, { method: 'DELETE' });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Error al eliminar curso');
        }

        const responseData = await response.json();
        mostrarMensaje('✅ ' + (responseData.mensaje || 'Curso eliminado exitosamente'), 'success');

        // Recargar datos
        await cargarMisCursos();
        await cargarCursosDisponibles();
        await cargarMisCompras();
        actualizarEstadisticas();

    } catch (error) {
        console.error('❌ Error eliminando curso:', error);
        mostrarMensaje('Error al eliminar curso: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
}

// ===============================================
// GESTIÓN DE COMPRAS
// ===============================================
async function comprarCurso(idCurso) {
    const select = document.getElementById(`pago_${idCurso}`);

    if (!select || !select.value) {
        mostrarMensaje('Por favor selecciona un método de pago', 'error');
        return;
    }

    try {
        mostrarLoading(true);

        // Obtener información del curso
        const cursoResponse = await fetch(`/api/cursos?id=${idCurso}`);
        if (!cursoResponse.ok) {
            throw new Error('Error al obtener información del curso');
        }

        const curso = await cursoResponse.json();

        const compraData = {
            id_curso: idCurso,
            id_metodo_pago: parseInt(select.value),
            precio_pagado: curso.precio,
            id_cliente: id_cliente,
            llave_secreta: llave_secreta
        };

        const response = await fetch('/api/compras', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(compraData)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Error al procesar la compra');
        }

        mostrarMensaje('🎉 ¡Compra realizada exitosamente!', 'success');

        // Recargar datos
        await cargarCursosDisponibles();
        await cargarMisCompras();
        actualizarEstadisticas();

    } catch (error) {
        console.error('❌ Error en compra:', error);
        mostrarMensaje('Error al procesar la compra: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
}

// ===============================================
// GESTIÓN DE TABS Y NAVEGACIÓN
// ===============================================
function cambiarTab(tabId) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Desactivar todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Mostrar tab seleccionado
    const tabContent = document.getElementById(`tab-${tabId}`);
    if (tabContent) {
        tabContent.classList.add('active');
    }

    // Activar botón correspondiente
    const tabBtn = document.querySelector(`[onclick="cambiarTab('${tabId}')"]`);
    if (tabBtn) {
        tabBtn.classList.add('active');
    }

    tabActual = tabId;
}

function cambiarVista(vista) {
    // Actualizar botones
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    const btnActivo = document.querySelector(`[data-view="${vista}"]`);
    if (btnActivo) {
        btnActivo.classList.add('active');
    }

    // Aplicar vista a grids
    document.querySelectorAll('.courses-grid').forEach(grid => {
        if (vista === 'list') {
            grid.classList.add('list-view');
        } else {
            grid.classList.remove('list-view');
        }
    });

    vistaActual = vista;
}

// ===============================================
// FUNCIONALIDADES DE BÚSQUEDA Y FILTROS
// ===============================================
function configurarBusquedas() {
    const searches = {
        'search-mis-cursos': () => filtrarCursos(misCursos, 'listaCursos'),
        'search-disponibles': () => filtrarCursos(cursosDisponibles, 'cursosDisponibles'),
        'search-compras': () => filtrarCompras()
    };

    Object.entries(searches).forEach(([id, callback]) => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', debounce(callback, 300));
        }
    });
}

function configurarFiltros() {
    const filterPrecio = document.getElementById('filter-precio');
    if (filterPrecio) {
        filterPrecio.addEventListener('change', () => {
            filtrarCursos(cursosDisponibles, 'cursosDisponibles');
        });
    }

    const sortCompras = document.getElementById('sort-compras');
    if (sortCompras) {
        sortCompras.addEventListener('change', ordenarCompras);
    }
}

function filtrarCursos(cursos, containerId) {
    const searchTerm = getSearchTerm(containerId);
    const precioFilter = document.getElementById('filter-precio')?.value || '';

    let cursosFiltrados = [...cursos];

    // Filtro de búsqueda
    if (searchTerm) {
        cursosFiltrados = cursosFiltrados.filter(curso =>
            curso.titulo.toLowerCase().includes(searchTerm.toLowerCase()) ||
            curso.instructor.toLowerCase().includes(searchTerm.toLowerCase()) ||
            curso.descripcion.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }

    // Filtro de precio
    if (precioFilter && containerId === 'cursosDisponibles') {
        cursosFiltrados = cursosFiltrados.filter(curso => {
            const precio = parseFloat(curso.precio);
            switch (precioFilter) {
                case '0-50': return precio >= 0 && precio <= 50;
                case '50-100': return precio > 50 && precio <= 100;
                case '100-200': return precio > 100 && precio <= 200;
                case '200+': return precio > 200;
                default: return true;
            }
        });
    }

    renderizarCursosFiltrados(cursosFiltrados, containerId);
}

function filtrarCompras() {
    const searchTerm = getSearchTerm('listaCompras');

    let comprasFiltradas = [...misCompras];

    if (searchTerm) {
        comprasFiltradas = comprasFiltradas.filter(compra =>
            compra.cursos?.titulo?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            compra.cursos?.instructor?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            compra.metodos_pago?.nombre?.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }

    renderizarComprasFiltradas(comprasFiltradas);
}

function getSearchTerm(containerId) {
    const searchMap = {
        'listaCursos': 'search-mis-cursos',
        'cursosDisponibles': 'search-disponibles',
        'listaCompras': 'search-compras'
    };

    const searchId = searchMap[containerId];
    const input = document.getElementById(searchId);
    return input ? input.value.trim() : '';
}

function renderizarCursosFiltrados(cursos, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (cursos.length === 0) {
        container.innerHTML = crearEstadoVacio(
            'fas fa-search',
            'No se encontraron resultados',
            'Intenta con otros términos de búsqueda'
        );
        return;
    }

    container.innerHTML = '';
    const tipo = containerId === 'listaCursos' ? 'mis-cursos' : 'disponibles';

    cursos.forEach(curso => {
        const element = crearTarjetaCurso(curso, tipo);
        container.appendChild(element);
    });
}

function renderizarComprasFiltradas(compras) {
    const container = document.getElementById('listaCompras');
    if (!container) return;

    if (compras.length === 0) {
        container.innerHTML = crearEstadoVacio(
            'fas fa-search',
            'No se encontraron compras',
            'Intenta con otros términos de búsqueda'
        );
        return;
    }

    container.innerHTML = '';
    compras.forEach(compra => {
        const element = crearTarjetaCompra(compra);
        container.appendChild(element);
    });
}

function ordenarCompras() {
    const sortValue = document.getElementById('sort-compras')?.value || 'fecha-desc';

    let comprasOrdenadas = [...misCompras];

    switch (sortValue) {
        case 'fecha-desc':
            comprasOrdenadas.sort((a, b) => new Date(b.fecha_compra) - new Date(a.fecha_compra));
            break;
        case 'fecha-asc':
            comprasOrdenadas.sort((a, b) => new Date(a.fecha_compra) - new Date(b.fecha_compra));
            break;
        case 'precio-desc':
            comprasOrdenadas.sort((a, b) => b.precio_pagado - a.precio_pagado);
            break;
        case 'precio-asc':
            comprasOrdenadas.sort((a, b) => a.precio_pagado - b.precio_pagado);
            break;
    }

    renderizarComprasFiltradas(comprasOrdenadas);
}

// ===============================================
// GESTIÓN DE MODALES
// ===============================================
function mostrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // Limpiar datos específicos del modal
    if (modalId === 'modalEdicion') {
        cursoEnEdicion = null;
        const form = document.getElementById('formEditarCurso');
        if (form) form.reset();
    }
}

function cerrarModalEdicion() {
    cerrarModal('modalEdicion');
}

function cerrarModalConfirmacion() {
    cerrarModal('modalConfirmacion');
}

function cerrarTodosLosModales() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('show');
    });
    document.body.style.overflow = 'auto';
    cursoEnEdicion = null;
}

function manejarClickFueraModal(e) {
    if (e.target.classList.contains('modal')) {
        cerrarTodosLosModales();
    }
}

function mostrarConfirmacion(mensaje, callback) {
    const textoElement = document.getElementById('confirmacionTexto');
    const btnConfirmar = document.getElementById('btnConfirmar');

    if (textoElement && btnConfirmar) {
        textoElement.textContent = mensaje;
        btnConfirmar.onclick = () => {
            callback();
            cerrarModalConfirmacion();
        };
        mostrarModal('modalConfirmacion');
    }
}

// ===============================================
// FUNCIONALIDADES ADICIONALES
// ===============================================
function toggleFormulario() {
    const content = document.getElementById('formContent');
    const btn = document.querySelector('.toggle-form-btn i');

    if (content && btn) {
        if (content.classList.contains('collapsed')) {
            content.classList.remove('collapsed');
            btn.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('collapsed');
            btn.style.transform = 'rotate(180deg)';
        }
    }
}

function previewImagen(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');

    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; border-radius: 8px;">`;
            preview.classList.add('show');
        };
        reader.readAsDataURL(file);
    }
}

function previewImagenEdit(e) {
    const file = e.target.files[0];
    const imgActual = document.getElementById('editImagenActual');

    if (file && imgActual) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imgActual.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function actualizarEstadisticas() {
    const totalCursosElement = document.getElementById('total-cursos');
    const totalComprasElement = document.getElementById('total-compras');
    const cursosDisponiblesElement = document.getElementById('cursos-disponibles');
    const totalIngresosElement = document.getElementById('total-ingresos');

    if (totalCursosElement) totalCursosElement.textContent = misCursos.length;
    if (totalComprasElement) totalComprasElement.textContent = misCompras.length;
    if (cursosDisponiblesElement) cursosDisponiblesElement.textContent = cursosDisponibles.length;

    if (totalIngresosElement) {
        const totalIngresos = misCompras.reduce((sum, compra) => sum + parseFloat(compra.precio_pagado || 0), 0);
        totalIngresosElement.textContent = `${formatearPrecio(totalIngresos)}`;
    }
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
        case 'info':
            icono = '<i class="fas fa-info-circle"></i>';
            break;
        default:
            icono = '<i class="fas fa-check-circle"></i>';
    }

    mensaje.innerHTML = `${icono} ${texto}`;
    mensaje.style.display = 'block';

    // Auto-ocultar después de un tiempo
    setTimeout(() => {
        mensaje.style.display = 'none';
    }, tipo === 'error' ? 8000 : 5000);

    // Scroll suave hacia el mensaje
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

function mostrarErrorEnContenedor(containerId, mensaje) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error al cargar contenido</h3>
                <p>${escapeHtml(mensaje)}</p>
                <button onclick="location.reload()" class="btn btn-primary">
                    <i class="fas fa-redo"></i>
                    Reintentar
                </button>
            </div>
        `;
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

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===============================================
// MANEJO DE ERRORES GLOBAL
// ===============================================
window.addEventListener('error', function(e) {
    console.error('Error global capturado:', e.error);
    mostrarMensaje('Se produjo un error inesperado. Por favor, recarga la página.', 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Promise rechazada no manejada:', e.reason);
    mostrarMensaje('Error de conexión. Verifica tu conexión a internet.', 'error');
});

// ===============================================
// FUNCIONES GLOBALES SIMPLES
// ===============================================
window.cambiarTab = cambiarTab;
window.cambiarVista = cambiarVista;
window.toggleFormulario = toggleFormulario;
window.editarCurso = editarCurso;
window.eliminarCurso = eliminarCurso;
window.comprarCurso = comprarCurso;
window.cerrarModalEdicion = cerrarModalEdicion;
window.cerrarModalConfirmacion = cerrarModalConfirmacion;

// ===============================================
// FUNCIONES ADICIONALES PARA EL CARRITO EN DASHBOARD
// (Agregar al final de tu dashboard.js existente)
// ===============================================

// Función para actualizar contador del carrito
async function actualizarContadorCarrito() {
    try {
        const url = `/api/carrito?accion=resumen&id_cliente=${encodeURIComponent(id_cliente)}&llave_secreta=${encodeURIComponent(llave_secreta)}`;
        const response = await fetch(url);

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                const contador = document.getElementById('carrito-contador');
                const cantidad = data.resumen.cantidad_items;

                if (contador) {
                    contador.textContent = cantidad;
                    if (cantidad > 0) {
                        contador.classList.add('show');
                    } else {
                        contador.classList.remove('show');
                    }
                }
            }
        }
    } catch (error) {
        console.warn('⚠️ Error actualizando contador carrito:', error);
    }
}

// Función simple para agregar al carrito
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
            await actualizarContadorCarrito();

            // Animar el botón del carrito
            const carritoBtn = document.querySelector('.carrito-btn');
            if (carritoBtn) {
                carritoBtn.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    carritoBtn.style.transform = 'scale(1)';
                }, 200);
            }

            // Preguntar si quiere ver el carrito
            setTimeout(() => {
                if (confirm('✅ Curso agregado al carrito.\n\n¿Quieres ver tu carrito ahora?')) {
                    window.location.href = '/carrito';
                }
            }, 1000);

        } else {
            mostrarMensaje('⚠️ ' + data.error, 'warning');
        }
    } catch (error) {
        console.error('❌ Error agregando al carrito:', error);
        mostrarMensaje('Error al agregar curso al carrito', 'error');
    } finally {
        mostrarLoading(false);
    }
}

// Modificar la función crearAccionesDisponibles existente
// Busca esta función en tu dashboard.js y reemplázala con esta versión:
function crearAccionesDisponibles(curso) {
    const div = document.createElement('div');
    div.className = 'curso-compra';

    // Solo botón agregar al carrito (simple)
    const btnCarrito = document.createElement('button');
    btnCarrito.className = 'btn btn-primary';
    btnCarrito.style.width = '100%';
    btnCarrito.style.marginBottom = 'var(--spacing-md)';
    btnCarrito.innerHTML = '<i class="fas fa-cart-plus"></i> Agregar al Carrito';
    btnCarrito.onclick = () => agregarAlCarrito(curso.id);

    // Información del precio
    const precioDiv = document.createElement('div');
    precioDiv.style.textAlign = 'center';
    precioDiv.style.fontSize = '1.2rem';
    precioDiv.style.fontWeight = 'bold';
    precioDiv.style.color = 'var(--accent-green)';
    precioDiv.style.marginTop = 'var(--spacing-md)';
    precioDiv.innerHTML = `$${formatearPrecio(curso.precio)}`;

    div.appendChild(btnCarrito);
    div.appendChild(precioDiv);

    return div;
}

// Actualizar contador al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    // Actualizar contador inmediatamente
    setTimeout(actualizarContadorCarrito, 1000);

    // Actualizar cada 30 segundos
    setInterval(actualizarContadorCarrito, 30000);
});

// Exponer funciones globalmente
window.agregarAlCarrito = agregarAlCarrito;
window.actualizarContadorCarrito = actualizarContadorCarrito;

