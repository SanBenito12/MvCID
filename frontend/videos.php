<?php
// frontend/videos.php
// Vista para mostrar videos de YouTube - CORREGIDA

session_start();

// Verificar autenticación
if (!isset($_SESSION['cliente_id'])) {
    header('Location: /login');
    exit();
}

// Obtener datos del usuario para el header
$nombreUsuario = $_SESSION['cliente_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos Educativos - MVC SISTEMA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-rgb: 37, 99, 235;
            --accent-blue: #3b82f6;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --card-bg: #ffffff;
            --background-light: #f9fafb;
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 0.75rem;
            --spacing-lg: 1rem;
            --spacing-xl: 1.5rem;
            --spacing-2xl: 2rem;
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-light);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Header Navigation */
        .header-nav {
            background: var(--card-bg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .nav-actions {
            display: flex;
            gap: var(--spacing-md);
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md) var(--spacing-lg);
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--background-light);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--card-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Main Container */
        .videos-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
            background: linear-gradient(135deg, var(--primary-color), var(--accent-blue));
            color: white;
            padding: var(--spacing-2xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
        }

        .page-header h1 {
            margin: 0 0 var(--spacing-md) 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        /* Filters Section */
        .filters-section {
            background: var(--card-bg);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }

        .filters-row {
            display: flex;
            gap: var(--spacing-lg);
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
        }

        .search-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .category-filters {
            display: flex;
            gap: var(--spacing-sm);
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: var(--spacing-sm) var(--spacing-lg);
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-secondary);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Videos Grid */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-2xl);
        }

        .video-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .video-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .video-thumbnail {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            cursor: pointer;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .video-thumbnail:hover img {
            transform: scale(1.05);
        }

        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .video-thumbnail:hover .play-overlay {
            background: rgba(37, 99, 235, 0.9);
            transform: translate(-50%, -50%) scale(1.1);
        }

        .video-duration {
            position: absolute;
            bottom: var(--spacing-sm);
            right: var(--spacing-sm);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .video-info {
            padding: var(--spacing-lg);
        }

        .video-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            line-height: 1.4;
        }

        .video-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: var(--spacing-md);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
        }

        .video-category {
            background: var(--primary-color);
            color: white;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .video-date {
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .video-actions {
            display: flex;
            gap: var(--spacing-sm);
        }

        .video-actions .btn {
            flex: 1;
            justify-content: center;
            font-size: 0.8rem;
            padding: var(--spacing-sm) var(--spacing-md);
        }

        /* Modal */
        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            position: relative;
            width: 90%;
            max-width: 900px;
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg);
            background: var(--background-light);
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            padding: var(--spacing-sm);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }

        .close-btn:hover {
            background: var(--danger-color);
            color: white;
        }

        .video-embed {
            width: 100%;
            height: 500px;
            border: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: var(--spacing-2xl);
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: var(--spacing-lg);
            color: var(--text-muted);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: var(--spacing-md);
            color: var(--text-primary);
        }

        .empty-state p {
            font-size: 0.875rem;
        }

        /* Loading State */
        .loading-state {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-2xl);
            color: var(--text-secondary);
        }

        .loading-spinner {
            border: 3px solid var(--border-color);
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin-right: var(--spacing-md);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .videos-container {
                padding: 0 var(--spacing-md);
            }

            .page-header {
                padding: var(--spacing-lg);
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .filters-row {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: unset;
            }

            .videos-grid {
                grid-template-columns: 1fr;
            }

            .video-embed {
                height: 300px;
            }

            .nav-container {
                flex-direction: column;
                gap: var(--spacing-md);
            }
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <div class="header-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fab fa-youtube"></i>
                Videos Educativos
            </div>
            <div class="nav-actions">
                <span>Hola, <?php echo htmlspecialchars($nombreUsuario); ?></span>
                <a href="/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="videos-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fab fa-youtube"></i> Videos Educativos</h1>
            <p>Aprende con nuestros videos tutoriales sobre programación y desarrollo web</p>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="search-box">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-input" 
                        placeholder="Buscar videos por título o descripción..."
                        onkeyup="filtrarVideos()"
                    >
                </div>
                <div class="category-filters" id="categoryFilters">
                    <!-- Los filtros se cargan dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Videos Grid -->
        <div class="videos-grid" id="videosGrid">
            <div class="loading-state">
                <div class="loading-spinner"></div>
                Cargando videos...
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div id="videoModal" class="video-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Reproduciendo Video</h3>
                <button class="close-btn" onclick="cerrarModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <iframe id="videoEmbed" class="video-embed" allowfullscreen></iframe>
        </div>
    </div>

    <script>
        // Variables globales
        let todosLosVideos = [];
        let videosFiltrados = [];
        let categoriaActiva = '';

        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎬 Iniciando página de videos...');
            cargarVideos();
        });

        // Cargar videos desde la API
        async function cargarVideos() {
            try {
                console.log('📡 Cargando videos desde API...');
                const response = await fetch('/api/videos?action=todos');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('📊 Respuesta de API:', data);

                if (data.success && data.videos) {
                    todosLosVideos = data.videos;
                    videosFiltrados = [...todosLosVideos];
                    
                    console.log(`✅ Cargados ${todosLosVideos.length} videos`);
                    
                    cargarCategorias();
                    mostrarVideos();
                } else {
                    mostrarError('Error al cargar videos: ' + (data.error || 'Respuesta inválida'));
                }
            } catch (error) {
                console.error('❌ Error cargando videos:', error);
                mostrarError('Error de conexión: ' + error.message);
            }
        }

        // Cargar filtros de categorías
        async function cargarCategorias() {
            try {
                const response = await fetch('/api/videos?action=categorias');
                const data = await response.json();

                if (data.success) {
                    const container = document.getElementById('categoryFilters');
                    
                    // Botón "Todas"
                    container.innerHTML = `
                        <button class="filter-btn active" onclick="filtrarPorCategoria('')">
                            Todas
                        </button>
                    `;

                    // Botones de categorías
                    data.categorias.forEach(categoria => {
                        container.innerHTML += `
                            <button class="filter-btn" onclick="filtrarPorCategoria('${categoria}')">
                                ${categoria}
                            </button>
                        `;
                    });
                }
            } catch (error) {
                console.error('Error al cargar categorías:', error);
            }
        }

        // Mostrar videos en el grid
        function mostrarVideos() {
            const container = document.getElementById('videosGrid');
            
            if (videosFiltrados.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>No se encontraron videos</h3>
                        <p>Intenta ajustar los filtros de búsqueda</p>
                    </div>
                `;
                return;
            }

            const videosHtml = videosFiltrados.map(video => `
                <div class="video-card">
                    <div class="video-thumbnail" onclick="abrirVideo('${video.id}')">
                        <img src="https://img.youtube.com/vi/${video.id}/mqdefault.jpg" 
                             alt="${video.titulo}" 
                             loading="lazy">
                        <div class="play-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="video-duration">${video.duracion}</div>
                    </div>
                    <div class="video-info">
                        <h3 class="video-title">${video.titulo}</h3>
                        <p class="video-description">${video.descripcion}</p>
                        <div class="video-meta">
                            <span class="video-category">${video.categoria}</span>
                            <span class="video-date">${formatearFecha(video.fecha)}</span>
                        </div>
                        <div class="video-actions">
                            <button onclick="abrirVideo('${video.id}')" class="btn btn-primary">
                                <i class="fas fa-play"></i>
                                Ver Video
                            </button>
                            <a href="https://www.youtube.com/watch?v=${video.id}" 
                               target="_blank" class="btn btn-secondary">
                                <i class="fas fa-external-link-alt"></i>
                                YouTube
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = videosHtml;
        }

        // Filtrar por categoría
        function filtrarPorCategoria(categoria) {
            categoriaActiva = categoria;
            
            // Actualizar botones activos
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            aplicarFiltros();
        }

        // Filtrar por texto de búsqueda
        function filtrarVideos() {
            aplicarFiltros();
        }

        // Aplicar todos los filtros
        function aplicarFiltros() {
            const termino = document.getElementById('searchInput').value.toLowerCase();
            
            videosFiltrados = todosLosVideos.filter(video => {
                // Filtro por categoría
                const coincideCategoria = !categoriaActiva || video.categoria === categoriaActiva;
                
                // Filtro por búsqueda de texto
                const coincideTexto = !termino || 
                    video.titulo.toLowerCase().includes(termino) ||
                    video.descripcion.toLowerCase().includes(termino);
                
                return coincideCategoria && coincideTexto;
            });

            mostrarVideos();
        }

        // Abrir video en modal
        function abrirVideo(videoId) {
            console.log('🎬 Abriendo video:', videoId);
            
            const modal = document.getElementById('videoModal');
            const embed = document.getElementById('videoEmbed');
            
            embed.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            modal.style.display = 'flex';
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }

        // Cerrar modal
        function cerrarModal() {
            const modal = document.getElementById('videoModal');
            const embed = document.getElementById('videoEmbed');
            
            embed.src = '';
            modal.style.display = 'none';
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
        }

        // Cerrar modal al hacer clic fuera del contenido
        document.getElementById('videoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });

        // Mostrar error
        function mostrarError(mensaje) {
            const container = document.getElementById('videosGrid');
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error</h3>
                    <p>${mensaje}</p>
                    <button onclick="cargarVideos()" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                </div>
            `;
        }

        // Formatear fecha
        function formatearFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
    </script>
</body>
</html>