<?php
// frontend/videos.php
// Vista simple para mostrar videos de YouTube configurados

session_start();
// CORRECCIÓN: Cambiar 'cliente_id' por 'id_cliente'
if (!isset($_SESSION['id_cliente'])) {
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

        .category-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--secondary-bg);
            color: var(--text-primary);
            cursor: pointer;
            transition: all var(--transition-normal);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .category-btn:hover {
            background: var(--accent-bg);
            color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .category-btn.active {
            background: var(--gradient-accent);
            color: white;
            border-color: var(--accent-blue);
        }

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-2xl);
        }

        .video-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all var(--transition-normal);
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }

        .video-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-blue);
        }

        .video-thumbnail {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            overflow: hidden;
        }

        .video-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-normal);
        }

        .video-card:hover .video-thumbnail img {
            transform: scale(1.05);
        }

        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 64px;
            height: 64px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-normal);
        }

        .video-card:hover .play-overlay {
            background: var(--accent-red);
            transform: translate(-50%, -50%) scale(1.1);
        }

        .play-overlay i {
            color: white;
            font-size: 24px;
            margin-left: 4px;
        }

        .video-info {
            padding: var(--spacing-lg);
        }

        .video-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            display: flex;
            gap: var(--spacing-md);
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .video-duration {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .video-category {
            display: inline-block;
            padding: var(--spacing-xs) var(--spacing-sm);
            background: var(--accent-bg);
            color: var(--accent-blue);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            animation: fadeIn var(--transition-normal);
        }

        .video-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            width: 90%;
            max-width: 1000px;
            background: var(--primary-bg);
            border-radius: var(--radius-xl);
            overflow: hidden;
            animation: slideIn var(--transition-normal);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--border-color);
        }

        .modal-close {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--secondary-bg);
            border-radius: var(--radius-md);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-normal);
        }

        .modal-close:hover {
            background: var(--accent-red);
            color: white;
        }

        .video-player {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }

        .video-player iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .no-videos {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-muted);
        }

        .no-videos i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: var(--spacing-lg);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-lg);
            background: var(--secondary-bg);
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            transition: all var(--transition-normal);
            margin-bottom: var(--spacing-xl);
        }

        .back-btn:hover {
            background: var(--accent-bg);
            color: var(--accent-blue);
            transform: translateX(-4px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .videos-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
            }

            .search-box {
                width: 100%;
            }

            .modal-content {
                width: 95%;
                max-width: none;
            }
        }
    </style>
</head>
<body>

<div class="videos-container">
    <a href="/dashboard" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Volver al Dashboard
    </a>

    <div class="page-header">
        <h1>
            <i class="fas fa-play-circle"></i>
            Videos del Tema
        </h1>
        <p>Explora nuestra colección de videos educativos</p>
    </div>

    <div class="filters-section">
        <div class="filters-row">
            <div class="search-box">
                <input 
                    type="text" 
                    class="search-input" 
                    id="searchInput"
                    placeholder="Buscar videos..."
                >
            </div>
            <div class="category-filters" id="categoryFilters">
                <button class="category-btn active" data-category="">
                    Todos
                </button>
            </div>
        </div>
    </div>

    <div class="videos-grid" id="videosGrid">
        <!-- Los videos se cargarán aquí dinámicamente -->
    </div>

    <div class="no-videos" id="noVideos" style="display: none;">
        <i class="fas fa-video-slash"></i>
        <h3>No se encontraron videos</h3>
        <p>Intenta con otros filtros de búsqueda</p>
    </div>
</div>

<!-- Modal para reproducir videos -->
<div class="video-modal" id="videoModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Título del Video</h3>
            <button class="modal-close" onclick="closeVideoModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="video-player" id="videoPlayer">
            <!-- El iframe del video se insertará aquí -->
        </div>
    </div>
</div>

<script>
// Variables globales
let videosData = [];
let filteredVideos = [];
let currentCategory = '';
let searchTerm = '';

// Cargar videos al iniciar
document.addEventListener('DOMContentLoaded', () => {
    cargarVideos();
    configurarEventos();
});

// Configurar eventos
function configurarEventos() {
    // Búsqueda con debounce
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchTerm = e.target.value.toLowerCase();
            filtrarVideos();
        }, 300);
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.getElementById('videoModal').classList.contains('active')) {
            closeVideoModal();
        }
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('videoModal').addEventListener('click', (e) => {
        if (e.target.id === 'videoModal') {
            closeVideoModal();
        }
    });
}

// Cargar videos desde la API
async function cargarVideos() {
    try {
        const response = await fetch('/api/videos?action=todos');
        const data = await response.json();

        if (data.success) {
            videosData = data.videos;
            filteredVideos = [...videosData];
            
            // Cargar categorías
            cargarCategorias();
            
            // Mostrar videos
            mostrarVideos();
        } else {
            console.error('Error al cargar videos:', data.error);
            mostrarMensajeError();
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        mostrarMensajeError();
    }
}

// Cargar categorías disponibles
async function cargarCategorias() {
    try {
        const response = await fetch('/api/videos?action=categorias');
        const data = await response.json();

        if (data.success && data.categorias.length > 0) {
            const container = document.getElementById('categoryFilters');
            
            // Mantener el botón "Todos"
            container.innerHTML = `
                <button class="category-btn active" data-category="" onclick="cambiarCategoria('')">
                    Todos
                </button>
            `;

            // Agregar categorías
            data.categorias.forEach(categoria => {
                const btn = document.createElement('button');
                btn.className = 'category-btn';
                btn.dataset.category = categoria;
                btn.textContent = categoria;
                btn.onclick = () => cambiarCategoria(categoria);
                container.appendChild(btn);
            });
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
}

// Cambiar categoría
function cambiarCategoria(categoria) {
    currentCategory = categoria;
    
    // Actualizar botones
    document.querySelectorAll('.category-btn').forEach(btn => {
        if (btn.dataset.category === categoria) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    filtrarVideos();
}

// Filtrar videos
function filtrarVideos() {
    filteredVideos = videosData.filter(video => {
        const matchesCategory = !currentCategory || video.categoria === currentCategory;
        const matchesSearch = !searchTerm || 
            video.titulo.toLowerCase().includes(searchTerm) ||
            (video.descripcion && video.descripcion.toLowerCase().includes(searchTerm));
        
        return matchesCategory && matchesSearch;
    });

    mostrarVideos();
}

// Mostrar videos en la grilla
function mostrarVideos() {
    const grid = document.getElementById('videosGrid');
    const noVideos = document.getElementById('noVideos');

    if (filteredVideos.length === 0) {
        grid.innerHTML = '';
        noVideos.style.display = 'block';
        return;
    }

    noVideos.style.display = 'none';
    
    grid.innerHTML = filteredVideos.map(video => `
        <div class="video-card" onclick="abrirVideo('${video.id}', '${video.titulo.replace(/'/g, "\\'")}')">
            <div class="video-thumbnail">
                <img src="https://img.youtube.com/vi/${video.id}/maxresdefault.jpg" 
                     onerror="this.src='https://img.youtube.com/vi/${video.id}/hqdefault.jpg'"
                     alt="${video.titulo}">
                <div class="play-overlay">
                    <i class="fas fa-play"></i>
                </div>
            </div>
            <div class="video-info">
                <h3 class="video-title">${video.titulo}</h3>
                <div class="video-meta">
                    <span class="video-duration">
                        <i class="far fa-clock"></i>
                        ${video.duracion}
                    </span>
                    <span class="video-category">${video.categoria}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// Abrir video en modal
function abrirVideo(videoId, titulo) {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    const modalTitle = document.getElementById('modalTitle');

    modalTitle.textContent = titulo;
    
    // Cargar el iframe del video
    player.innerHTML = `
        <iframe 
            src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    `;

    modal.classList.add('active');
}

// Cerrar modal de video
function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    
    modal.classList.remove('active');
    player.innerHTML = ''; // Detener la reproducción
}

// Mostrar mensaje de error
function mostrarMensajeError() {
    const grid = document.getElementById('videosGrid');
    grid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: var(--spacing-2xl);">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--accent-orange); margin-bottom: var(--spacing-lg);"></i>
            <h3>Error al cargar los videos</h3>
            <p style="color: var(--text-muted);">Por favor, intenta nuevamente más tarde</p>
        </div>
    `;
}
</script>

</body>
</html>