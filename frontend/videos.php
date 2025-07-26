<?php
// frontend/videos.php
// Vista simple para mostrar videos de YouTube configurados

session_start();
if (!isset($_SESSION['cliente_id'])) {
    header('Location: /login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos del Tema - MVC SISTEMA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .videos-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-lg);
        }

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
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-primary);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .filter-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        }

        .video-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .video-card:hover {
            transform: translateY(-5px);
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

        .video-card:hover .video-thumbnail img {
            transform: scale(1.05);
        }

        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .video-card:hover .play-overlay {
            background: rgba(255, 0, 0, 0.9);
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
            font-size: 0.8rem;
            font-weight: 600;
        }

        .video-info {
            padding: var(--spacing-lg);
        }

        .video-title {
            font-weight: 600;
            font-size: 1.1rem;
            line-height: 1.4;
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: var(--spacing-md);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
        }

        .video-category {
            background: var(--accent-blue);
            color: white;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .video-date {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .video-actions {
            display: flex;
            gap: var(--spacing-sm);
        }

        .video-actions .btn {
            flex: 1;
            padding: var(--spacing-sm);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
        }

        .empty-state {
            text-align: center;
            padding: var(--spacing-2xl);
            color: var(--text-secondary);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: var(--spacing-lg);
            color: var(--border-color);
        }

        .loading {
            text-align: center;
            padding: var(--spacing-2xl);
            color: var(--text-secondary);
            grid-column: 1 / -1;
        }

        .loading i {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: var(--spacing-md);
            right: var(--spacing-md);
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            z-index: 1001;
        }

        .modal-close:hover {
            color: var(--text-primary);
        }

        .video-embed {
            width: 800px;
            height: 450px;
            max-width: 100%;
            border: none;
            border-radius: var(--radius-md);
        }

        @media (max-width: 768px) {
            .videos-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
                align-items: stretch;
            }

            .video-embed {
                width: 100%;
                height: 250px;
            }

            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="videos-container">
        <!-- Header -->
        <div class="header">
            <h1>
                <i class="fab fa-youtube"></i>
                Videos del Tema
            </h1>
            <div class="user-info">
                <a href="/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Dashboard
                </a>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-play-circle"></i>
                Videos Educativos
            </h1>
            <p>Colección curada de videos relacionados con nuestro tema principal</p>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="search-box">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-input" 
                        placeholder="Buscar videos..."
                        onkeyup="filtrarVideos()"
                    >
                </div>
                <div class="category-filters" id="categoryFilters">
                    <!-- Se llenarán dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Grid de Videos -->
        <div class="videos-grid" id="videosGrid">
            <div class="loading">
                <i class="fas fa-spinner"></i>
                <p>Cargando videos...</p>
            </div>
        </div>
    </div>

    <!-- Modal para reproducir video -->
    <div class="video-modal" id="videoModal">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
            <iframe id="videoEmbed" class="video-embed" allowfullscreen></iframe>
        </div>
    </div>

    <script>
        // Variables globales
        let todosLosVideos = [];
        let videosFiltrados = [];
        let categoriaActiva = '';

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            cargarVideos();
        });

        // Cargar videos desde la API
        async function cargarVideos() {
            try {
                const response = await fetch('/api/videos?action=todos');
                const data = await response.json();

                if (data.success) {
                    todosLosVideos = data.videos;
                    videosFiltrados = [...todosLosVideos];
                    
                    cargarCategorias();
                    mostrarVideos();
                } else {
                    mostrarError('Error al cargar videos: ' + data.error);
                }
            } catch (error) {
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