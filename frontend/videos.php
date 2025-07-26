<?php
// frontend/videos.php
// Vista para mostrar videos de YouTube con integración completa de YouTube IFrame Player API

session_start();

// CORRECCIÓN: Usar 'id_cliente' consistente con el sistema
if (!isset($_SESSION['id_cliente'])) {
    header('Location: /login');
    exit();
}

// Configuración del cliente autenticado
$clienteId = $_SESSION['id_cliente'];
$clienteNombre = $_SESSION['nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos Educativos - YouTube Player | MVC SISTEMA</title>
    
    <!-- YouTube IFrame Player API -->
    <script src="https://www.youtube.com/iframe_api"></script>
    
    <!-- Estilos - SOLO VIDEOS.CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/videos.css" rel="stylesheet">
    
    <!-- Meta tags adicionales -->
    <meta name="description" content="Videos educativos con reproductor YouTube integrado - MVC Sistema">
    <meta name="keywords" content="videos, educativo, youtube, cursos, tutoriales, programación">
    <meta name="author" content="MVC Sistema">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph para redes sociales -->
    <meta property="og:title" content="Videos Educativos - MVC Sistema">
    <meta property="og:description" content="Explora nuestra colección de videos educativos con reproductor YouTube integrado">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
</head>
<body>
    <!-- Contenedor principal -->
    <div class="videos-container">
        <!-- Header principal -->
        <header class="page-header">
            <h1>
                <i class="fab fa-youtube" aria-hidden="true"></i>
                Videos Educativos
            </h1>
            <p>Explora nuestra colección de cursos y tutoriales con el reproductor YouTube integrado</p>
        </header>

        <!-- Mensaje de bienvenida -->
        <section class="welcome-message">
            <h3>
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                ¡Bienvenido, <?php echo htmlspecialchars($clienteNombre); ?>!
            </h3>
            <p>Disfruta de nuestros videos educativos con reproductor avanzado y controles personalizados. Encuentra contenido de calidad para mejorar tus habilidades de programación.</p>
        </section>

        <!-- Sección de filtros -->
        <section class="filters-section">
            <div class="filters-row">
                <div class="search-box">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-input" 
                        placeholder="Buscar videos por título, descripción o tags..."
                        aria-label="Buscar videos"
                        autocomplete="off"
                    >
                    <i class="fas fa-search search-icon" aria-hidden="true"></i>
                </div>
                
                <select id="categoriaFilter" class="filter-select" aria-label="Filtrar por categoría">
                    <option value="">Todas las categorías</option>
                </select>
                
                <select id="nivelFilter" class="filter-select" aria-label="Filtrar por nivel de dificultad">
                    <option value="">Todos los niveles</option>
                </select>
                
                <button id="clearFilters" class="btn btn-primary" aria-label="Limpiar todos los filtros" type="button">
                    <i class="fas fa-refresh" aria-hidden="true"></i>
                    Limpiar
                </button>
            </div>
        </section>

        <!-- Barra de estadísticas -->
        <section class="stats-bar" role="status" aria-label="Estadísticas de la colección de videos">
            <div class="stat-item" tabindex="0" role="button" aria-label="Total de videos disponibles">
                <i class="fas fa-play-circle" style="color: var(--accent-blue);" aria-hidden="true"></i>
                <div class="stat-content">
                    <div class="stat-number" id="totalVideos">0</div>
                    <div>Videos disponibles</div>
                </div>
            </div>
            
            <div class="stat-item" tabindex="0" role="button" aria-label="Videos mostrados actualmente">
                <i class="fas fa-filter" style="color: var(--accent-green);" aria-hidden="true"></i>
                <div class="stat-content">
                    <div class="stat-number" id="videosVisibles">0</div>
                    <div>Videos mostrados</div>
                </div>
            </div>
            
            <div class="stat-item" tabindex="0" role="button" aria-label="Duración total de contenido">
                <i class="fas fa-clock" style="color: var(--accent-orange);" aria-hidden="true"></i>
                <div class="stat-content">
                    <div class="stat-number" id="duracionTotal">0h</div>
                    <div>Duración total</div>
                </div>
            </div>
        </section>

        <!-- Spinner de carga -->
        <div class="loading-spinner active" id="loadingSpinner" role="status" aria-label="Cargando videos">
            <div class="spinner" aria-hidden="true"></div>
            <p>Cargando videos educativos...</p>
        </div>

        <!-- Grid principal de videos -->
        <main class="videos-grid" id="videosGrid" role="main">
            <!-- Los videos se cargarán aquí dinámicamente via JavaScript -->
        </main>

        <!-- Estado vacío cuando no hay resultados -->
        <div class="empty-state" id="emptyState" style="display: none;" role="status">
            <i class="fas fa-search" aria-hidden="true"></i>
            <h3>No se encontraron videos</h3>
            <p>Intenta cambiar los filtros de búsqueda, usar diferentes términos o explorar otras categorías disponibles.</p>
        </div>

        <!-- Mensaje de error -->
        <div class="error-message" id="errorMessage" style="display: none;" role="alert" aria-live="polite">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <strong>Error al cargar videos</strong>
            <p id="errorText">Ha ocurrido un problema al cargar el contenido. Por favor, verifica tu conexión a internet y recarga la página.</p>
        </div>
    </div>

    <!-- Modal del reproductor de YouTube -->
    <div class="modal" id="videoModal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true" aria-modal="true">
        <div class="modal-content">
            <header class="modal-header">
                <h2 class="modal-title" id="modalTitle">Reproductor de Video</h2>
                <button 
                    class="close-modal" 
                    id="closeModal" 
                    aria-label="Cerrar reproductor de video"
                    type="button"
                >
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </header>
            
            <div class="player-container">
                <div class="player-wrapper">
                    <div id="youtubePlayer" aria-label="Reproductor de YouTube embebido"></div>
                </div>
            </div>
            
            <div id="videoInfo" style="margin-top: var(--spacing-lg);">
                <!-- La información del video se cargará aquí dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script>
        // ===============================================
        // CONFIGURACIÓN Y VARIABLES GLOBALES
        // ===============================================
        
        // Variables globales de la aplicación
        let videos = [];
        let filteredVideos = [];
        let categorias = [];
        let niveles = [];
        let youtubePlayer = null;
        let currentVideoId = null;
        let playerReady = false;

        // Configuración centralizada
        const CONFIG = {
            API_BASE: '/api/videos',
            DEBOUNCE_DELAY: 300,
            ERROR_HIDE_DELAY: 5000,
            MAX_RETRIES: 3,
            RETRY_DELAY: 1000,
            PLAYER_CONFIG: {
                height: '400',
                width: '100%',
                playerVars: {
                    'autoplay': 0,
                    'controls': 1,
                    'showinfo': 1,
                    'rel': 0,
                    'modestbranding': 1,
                    'iv_load_policy': 3,
                    'fs': 1,
                    'cc_load_policy': 0,
                    'playsinline': 1
                }
            }
        };

        // ===============================================
        // YOUTUBE IFRAME API
        // ===============================================

        // Función ejecutada cuando la API de YouTube está lista
        function onYouTubeIframeAPIReady() {
            console.log('✅ YouTube IFrame API inicializada correctamente');
            
            try {
                youtubePlayer = new YT.Player('youtubePlayer', {
                    ...CONFIG.PLAYER_CONFIG,
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange,
                        'onError': onPlayerError,
                        'onPlaybackQualityChange': onPlaybackQualityChange
                    }
                });
            } catch (error) {
                console.error('❌ Error creando reproductor YouTube:', error);
                showError('Error al inicializar el reproductor de YouTube. Verifica tu conexión.');
            }
        }

        function onPlayerReady(event) {
            console.log('✅ Reproductor YouTube listo para usar');
            playerReady = true;
        }

        function onPlayerStateChange(event) {
            const states = {
                '-1': 'no iniciado',
                '0': 'finalizado',
                '1': 'reproduciendo',
                '2': 'pausado',
                '3': 'buffering',
                '5': 'video cargado'
            };
            
            const stateName = states[event.data] || 'desconocido';
            console.log(`🎬 Estado del reproductor: ${stateName}`);
            
            if (event.data === YT.PlayerState.PLAYING && currentVideoId) {
                logVideoPlay(currentVideoId);
            }
        }

        function onPlayerError(event) {
            console.error('❌ Error en reproductor YouTube:', event.data);
            
            const errorMessages = {
                2: 'ID de video inválido o parámetros incorrectos',
                5: 'Error de reproductor HTML5',
                100: 'Video no encontrado o fue eliminado',
                101: 'Video no disponible en reproductor embebido',
                150: 'Video restringido para reproductor embebido'
            };
            
            const message = errorMessages[event.data] || 'Error desconocido en el reproductor';
            showError(`Error del reproductor: ${message}`);
        }

        function onPlaybackQualityChange(event) {
            console.log(`📺 Calidad de reproducción: ${event.data}`);
        }

        // ===============================================
        // FUNCIONES DE INICIALIZACIÓN
        // ===============================================

        async function inicializar() {
            console.log('🚀 Inicializando aplicación de videos...');
            
            try {
                // Mostrar indicador de carga
                showLoading();
                
                // Cargar datos en paralelo para mejor rendimiento
                const promesas = [
                    cargarVideos(),
                    cargarCategorias(),
                    cargarNiveles()
                ];
                
                await Promise.allSettled(promesas);
                
                // Configurar eventos de la interfaz
                configurarEventListeners();
                
                // Actualizar estadísticas iniciales
                actualizarEstadisticas();
                
                // Verificar que Font Awesome esté cargado
                verificarRecursos();
                
                console.log('✅ Aplicación inicializada correctamente');
                
            } catch (error) {
                console.error('❌ Error en inicialización:', error);
                showError('Error al cargar la aplicación. Por favor, recarga la página.');
            } finally {
                hideLoading();
            }
        }

        // ===============================================
        // FUNCIONES DE API
        // ===============================================

        async function cargarVideos(reintentos = 0) {
            try {
                console.log('📥 Cargando videos desde API...');
                
                const response = await fetch(`${CONFIG.API_BASE}?action=todos`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    videos = data.videos || [];
                    filteredVideos = [...videos];
                    mostrarVideos(filteredVideos);
                    console.log(`✅ ${videos.length} videos cargados exitosamente`);
                } else {
                    throw new Error(data.error || 'Error desconocido al cargar videos');
                }
                
            } catch (error) {
                console.error('❌ Error cargando videos:', error);
                
                if (reintentos < CONFIG.MAX_RETRIES) {
                    console.log(`🔄 Reintentando... (${reintentos + 1}/${CONFIG.MAX_RETRIES})`);
                    setTimeout(() => cargarVideos(reintentos + 1), CONFIG.RETRY_DELAY);
                } else {
                    showError(`Error al cargar videos: ${error.message}`);
                    videos = [];
                    filteredVideos = [];
                }
            }
        }

        async function cargarCategorias() {
            try {
                const response = await fetch(`${CONFIG.API_BASE}?action=categorias`);
                const data = await response.json();
                
                if (data.success) {
                    categorias = data.categorias || [];
                    llenarSelectCategorias();
                    console.log(`✅ ${categorias.length} categorías cargadas`);
                } else {
                    console.warn('⚠️ No se pudieron cargar las categorías');
                }
            } catch (error) {
                console.error('❌ Error cargando categorías:', error);
                categorias = [];
            }
        }

        async function cargarNiveles() {
            try {
                const response = await fetch(`${CONFIG.API_BASE}?action=niveles`);
                const data = await response.json();
                
                if (data.success) {
                    niveles = data.niveles || [];
                    llenarSelectNiveles();
                    console.log(`✅ ${niveles.length} niveles cargados`);
                } else {
                    console.warn('⚠️ No se pudieron cargar los niveles');
                }
            } catch (error) {
                console.error('❌ Error cargando niveles:', error);
                niveles = [];
            }
        }

        // ===============================================
        // FUNCIONES DE INTERFAZ
        // ===============================================

        function llenarSelectCategorias() {
            const select = document.getElementById('categoriaFilter');
            
            // Limpiar opciones existentes (mantener la primera)
            while (select.children.length > 1) {
                select.removeChild(select.lastChild);
            }
            
            categorias.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria;
                option.textContent = categoria;
                select.appendChild(option);
            });
        }

        function llenarSelectNiveles() {
            const select = document.getElementById('nivelFilter');
            
            // Limpiar opciones existentes (mantener la primera)
            while (select.children.length > 1) {
                select.removeChild(select.lastChild);
            }
            
            niveles.forEach(nivel => {
                const option = document.createElement('option');
                option.value = nivel;
                option.textContent = nivel;
                select.appendChild(option);
            });
        }

        function mostrarVideos(videosToShow) {
            const grid = document.getElementById('videosGrid');
            const emptyState = document.getElementById('emptyState');
            
            if (videosToShow.length === 0) {
                grid.innerHTML = '';
                emptyState.style.display = 'block';
                grid.style.display = 'none';
                return;
            }
            
            emptyState.style.display = 'none';
            grid.style.display = 'grid';
            
            grid.innerHTML = videosToShow.map((video, index) => `
                <article class="video-card" data-video-id="${video.id}" style="animation-delay: ${index * 0.1}s">
                    <div class="video-thumbnail">
                        <img src="${video.urls?.thumbnail || '/assets/img/video-placeholder.jpg'}" 
                             alt="Miniatura del video: ${escapeHtml(video.titulo)}"
                             loading="lazy"
                             onerror="this.src='/assets/img/video-placeholder.jpg'; this.onerror=null;"
                        >
                        
                        <button 
                            class="play-overlay" 
                            onclick="reproducirVideo('${video.id}')"
                            aria-label="Reproducir video: ${escapeHtml(video.titulo)}"
                            type="button"
                            tabindex="0"
                        >
                            <i class="fas fa-play" aria-hidden="true"></i>
                        </button>
                        
                        <div class="video-duration" aria-label="Duración del video: ${video.duracion}">
                            ${video.duracion}
                        </div>
                    </div>
                    
                    <div class="video-content">
                        <span class="video-category" aria-label="Categoría: ${escapeHtml(video.categoria)}">
                            ${escapeHtml(video.categoria)}
                        </span>
                        
                        <h3 class="video-title">${escapeHtml(video.titulo)}</h3>
                        
                        <p class="video-description">${escapeHtml(video.descripcion)}</p>
                        
                        <div class="video-meta">
                            <div class="video-date">
                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                <time datetime="${video.fecha}">${formatearFecha(video.fecha)}</time>
                            </div>
                            <div class="video-level" aria-label="Nivel de dificultad: ${video.nivel || 'No especificado'}">
                                ${video.nivel || 'N/A'}
                            </div>
                        </div>
                    </div>
                </article>
            `).join('');
            
            // Actualizar contador
            document.getElementById('videosVisibles').textContent = videosToShow.length;
        }

        // ===============================================
        // FUNCIONES DEL REPRODUCTOR
        // ===============================================

        async function reproducirVideo(videoId) {
            if (!playerReady) {
                showError('El reproductor aún no está listo. Por favor, espera un momento.');
                return;
            }
            
            try {
                console.log(`🎬 Reproduciendo video: ${videoId}`);
                
                // Obtener información detallada del video
                const response = await fetch(`${CONFIG.API_BASE}?action=video&id=${encodeURIComponent(videoId)}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Error al cargar información del video');
                }
                
                const video = data.video;
                currentVideoId = videoId;
                
                // Actualizar modal con información del video
                actualizarModalInfo(video);
                
                // Cargar video en reproductor
                youtubePlayer.loadVideoById(videoId);
                
                // Mostrar modal
                mostrarModal();
                
            } catch (error) {
                console.error('❌ Error reproduciendo video:', error);
                showError(`Error al reproducir el video: ${error.message}`);
            }
        }

        function actualizarModalInfo(video) {
            document.getElementById('modalTitle').textContent = video.titulo;
            
            document.getElementById('videoInfo').innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg); margin-top: var(--spacing-lg);">
                    <div>
                        <h4><i class="fas fa-info-circle"></i> Información del Video</h4>
                        <p><strong>Categoría:</strong> ${escapeHtml(video.categoria)}</p>
                        <p><strong>Nivel:</strong> ${video.nivel || 'No especificado'}</p>
                        <p><strong>Duración:</strong> ${video.duracion}</p>
                        <p><strong>Fecha de publicación:</strong> ${formatearFecha(video.fecha)}</p>
                        ${video.tags ? `<p><strong>Tags:</strong> ${video.tags.map(tag => escapeHtml(tag)).join(', ')}</p>` : ''}
                    </div>
                    <div>
                        <h4><i class="fas fa-file-alt"></i> Descripción</h4>
                        <p>${escapeHtml(video.descripcion)}</p>
                        ${video.urls?.video ? `<p><strong>Ver en YouTube:</strong> <a href="${video.urls.video}" target="_blank" rel="noopener">Abrir en nueva pestaña</a></p>` : ''}
                    </div>
                </div>
            `;
        }

        function mostrarModal() {
            const modal = document.getElementById('videoModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            
            // Agregar clase active para animación
            requestAnimationFrame(() => {
                modal.classList.add('active');
            });
            
            // Bloquear scroll del body
            document.body.style.overflow = 'hidden';
            
            // Focus en botón cerrar para accesibilidad
            setTimeout(() => {
                document.getElementById('closeModal').focus();
            }, 100);
        }

        function cerrarModal() {
            const modal = document.getElementById('videoModal');
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            
            // Pausar video
            if (youtubePlayer && playerReady) {
                try {
                    youtubePlayer.pauseVideo();
                } catch (error) {
                    console.warn('⚠️ Error pausando video:', error);
                }
            }
            
            // Ocultar modal después de animación
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                currentVideoId = null;
            }, 300);
        }

        // ===============================================
        // EVENT LISTENERS
        // ===============================================

        function configurarEventListeners() {
            console.log('🎧 Configurando event listeners...');
            
            // Búsqueda con debounce
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;
            
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(aplicarFiltros, CONFIG.DEBOUNCE_DELAY);
            });
            
            // Filtros
            document.getElementById('categoriaFilter').addEventListener('change', aplicarFiltros);
            document.getElementById('nivelFilter').addEventListener('change', aplicarFiltros);
            
            // Botón limpiar
            document.getElementById('clearFilters').addEventListener('click', limpiarFiltros);
            
            // Modal
            document.getElementById('closeModal').addEventListener('click', cerrarModal);
            
            // Cerrar modal clickeando fuera
            document.getElementById('videoModal').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) {
                    cerrarModal();
                }
            });
            
            // Navegación por teclado
            document.addEventListener('keydown', manejarTeclado);
            
            // Estadísticas clickeables (opcional)
            document.querySelectorAll('.stat-item').forEach(item => {
                item.addEventListener('click', () => {
                    // Podrías agregar funcionalidad aquí
                    console.log('📊 Estadística clickeada');
                });
            });
        }

        function manejarTeclado(e) {
            // Escape para cerrar modal
            if (e.key === 'Escape' && document.getElementById('videoModal').classList.contains('active')) {
                cerrarModal();
            }
            
            // Enter/Space para botones de play
            if ((e.key === 'Enter' || e.key === ' ') && e.target.classList.contains('play-overlay')) {
                e.preventDefault();
                e.target.click();
            }
            
            // Atajo de teclado para búsqueda (Ctrl/Cmd + F)
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        }

        // ===============================================
        // FUNCIONES DE FILTRADO
        // ===============================================

        function aplicarFiltros() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const categoria = document.getElementById('categoriaFilter').value;
            const nivel = document.getElementById('nivelFilter').value;
            
            filteredVideos = videos.filter(video => {
                // Filtro de búsqueda
                const matchesSearch = !searchTerm || 
                    video.titulo.toLowerCase().includes(searchTerm) ||
                    video.descripcion.toLowerCase().includes(searchTerm) ||
                    (video.tags && video.tags.some(tag => tag.toLowerCase().includes(searchTerm)));
                
                // Filtro de categoría
                const matchesCategory = !categoria || video.categoria === categoria;
                
                // Filtro de nivel
                const matchesLevel = !nivel || video.nivel === nivel;
                
                return matchesSearch && matchesCategory && matchesLevel;
            });
            
            console.log(`🔍 Filtros aplicados: ${filteredVideos.length} de ${videos.length} videos`);
            mostrarVideos(filteredVideos);
            actualizarEstadisticas();
        }

        function limpiarFiltros() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoriaFilter').value = '';
            document.getElementById('nivelFilter').value = '';
            
            filteredVideos = [...videos];
            mostrarVideos(filteredVideos);
            actualizarEstadisticas();
            
            console.log('🧹 Filtros limpiados');
            
            // Focus en búsqueda para mejor UX
            document.getElementById('searchInput').focus();
        }

        // ===============================================
        // FUNCIONES DE ESTADÍSTICAS
        // ===============================================

        function actualizarEstadisticas() {
            document.getElementById('totalVideos').textContent = videos.length;
            document.getElementById('videosVisibles').textContent = filteredVideos.length;
            
            const duracionTotal = calcularDuracionTotal(filteredVideos);
            document.getElementById('duracionTotal').textContent = duracionTotal;
        }

        function calcularDuracionTotal(videosList) {
            let totalSegundos = 0;
            
            videosList.forEach(video => {
                const partes = video.duracion.split(':');
                if (partes.length >= 3) {
                    // Formato H:M:S
                    totalSegundos += parseInt(partes[0]) * 3600 + parseInt(partes[1]) * 60 + parseInt(partes[2]);
                } else if (partes.length === 2) {
                    // Formato M:S
                    totalSegundos += parseInt(partes[0]) * 60 + parseInt(partes[1]);
                }
            });
            
            const horas = Math.floor(totalSegundos / 3600);
            const minutos = Math.floor((totalSegundos % 3600) / 60);
            
            if (horas > 0) {
                return `${horas}h ${minutos}m`;
            } else if (minutos > 0) {
                return `${minutos}m`;
            } else {
                return '0m';
            }
        }

        // ===============================================
        // FUNCIONES DE UTILIDAD
        // ===============================================

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatearFecha(fecha) {
            if (!fecha) return 'Fecha no disponible';
            
            try {
                return new Date(fecha).toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (error) {
                console.warn('⚠️ Error formateando fecha:', error);
                return 'Fecha inválida';
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            
            errorText.textContent = message;
            errorDiv.style.display = 'block';
            
            console.error('💥 Error mostrado:', message);
            
            // Auto-ocultar
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, CONFIG.ERROR_HIDE_DELAY);
        }

        function showLoading() {
            document.getElementById('loadingSpinner').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').classList.remove('active');
        }

        function logVideoPlay(videoId) {
            console.log(`📊 Video reproducido: ${videoId}`);
            
            // Enviar estadísticas al backend (opcional)
            fetch('/api/videos/stats', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    video_id: videoId, 
                    action: 'play',
                    timestamp: new Date().toISOString(),
                    user_id: <?php echo json_encode($clienteId); ?>
                })
            }).catch(error => console.warn('⚠️ Error enviando estadísticas:', error));
        }

        function verificarRecursos() {
            // Verificar Font Awesome
            const iconTest = document.createElement('i');
            iconTest.className = 'fas fa-check';
            iconTest.style.position = 'absolute';
            iconTest.style.visibility = 'hidden';
            document.body.appendChild(iconTest);
            
            const iconStyles = window.getComputedStyle(iconTest);
            if (iconStyles.fontFamily.indexOf('Font Awesome') === -1) {
                console.warn('⚠️ Font Awesome no se cargó correctamente');
            }
            
            document.body.removeChild(iconTest);
        }

        // ===============================================
        // MANEJO GLOBAL DE ERRORES
        // ===============================================

        window.addEventListener('error', (e) => {
            console.error('💥 Error global:', e.error);
            showError('Ha ocurrido un error inesperado. Recarga la página si persiste.');
        });

        window.addEventListener('unhandledrejection', (e) => {
            console.error('💥 Promesa rechazada:', e.reason);
            showError('Error de conexión. Verifica tu conexión a internet.');
        });

        // ===============================================
        // INICIALIZACIÓN
        // ===============================================

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            console.log('🌟 DOM cargado, iniciando aplicación...');
            inicializar();
        });

        // Monitoreo de rendimiento
        window.addEventListener('load', () => {
            if ('performance' in window) {
                const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                console.log(`⚡ Página cargada en ${loadTime}ms`);
                
                if (loadTime > 3000) {
                    console.warn('⚠️ Tiempo de carga lento detectado');
                }
            }
        });

        // Service Worker (opcional para cache offline)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('✅ Service Worker registrado'))
                    .catch(err => console.log('❌ Error registrando Service Worker:', err));
            });
        }
    </script>

    <!-- Schema.org markup para SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "VideoGallery",
        "name": "Videos Educativos - MVC Sistema",
        "description": "Colección de videos educativos de programación con reproductor YouTube integrado",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>",
        "provider": {
            "@type": "Organization",
            "name": "MVC Sistema",
            "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>"
        },
        "educationalLevel": "Beginner to Advanced",
        "inLanguage": "es-ES",
        "keywords": "programación, tutoriales, PHP, JavaScript, HTML, CSS, MySQL"
    }
    </script>
</body>
</html>