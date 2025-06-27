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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        const id_cliente = "<?= $_SESSION['id_cliente'] ?>";
        const llave_secreta = "<?= $_SESSION['llave_secreta'] ?>";
        const SUPABASE_SERVICE_ROLE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imd2cGtla3NidWpmZHN6Y2twdWtpIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0OTUxMDQ1NCwiZXhwIjoyMDY1MDg2NDU0fQ.rNXqhDiKveKgUdFnStIVer7QkpGNsSPwM_f9FheQKhQ";
    </script>
    <style>
        /* Estilos adicionales para el nuevo diseño sin dropdown */
        .user-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            flex-wrap: wrap;
        }

        .welcome-text {
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 500;
            margin-right: var(--spacing-lg);
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            flex-wrap: wrap;
        }

        .action-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--secondary-bg);
            color: var(--text-primary);
            text-decoration: none;
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-size: 0.9rem;
            font-weight: 500;
            min-height: 40px;
            white-space: nowrap;
        }

        .action-btn:hover {
            background: var(--accent-bg);
            color: var(--accent-blue);
            border-color: var(--accent-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            text-decoration: none;
        }

        .action-btn.primary {
            background: var(--gradient-accent);
            color: white;
            border-color: var(--accent-blue);
        }

        .action-btn.primary:hover {
            background: var(--gradient-accent);
            color: white;
            filter: brightness(1.1);
        }

        .action-btn.danger {
            background: var(--gradient-danger);
            color: white;
            border-color: var(--accent-red);
        }

        .action-btn.danger:hover {
            background: var(--gradient-danger);
            color: white;
            filter: brightness(1.1);
        }

        .carrito-btn {
            position: relative;
            text-decoration: none;
            color: var(--text-primary);
            padding: var(--spacing-sm) var(--spacing-md);
            border: 2px solid var(--accent-blue);
            border-radius: var(--radius-lg);
            background: rgba(0, 212, 255, 0.1);
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-weight: 600;
            min-height: 40px;
        }

        .carrito-btn:hover {
            background: var(--accent-blue);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            text-decoration: none;
        }

        .carrito-contador {
            background: var(--accent-red);
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8rem;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
            position: absolute;
            top: -8px;
            right: -8px;
            display: none;
            animation: pulse 2s infinite;
        }

        .carrito-contador.show {
            display: block;
        }

        /* Responsive design mejorado */
        @media (max-width: 1200px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .action-buttons {
                gap: var(--spacing-xs);
            }
            
            .action-btn {
                padding: var(--spacing-xs) var(--spacing-sm);
                font-size: 0.85rem;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: var(--spacing-lg);
                text-align: center;
                padding: var(--spacing-lg);
            }

            .header h1 {
                font-size: 1.8rem;
                margin-bottom: var(--spacing-md);
            }

            .user-info {
                flex-direction: column;
                gap: var(--spacing-md);
                width: 100%;
                align-items: center;
            }

            .welcome-text {
                margin-right: 0;
                text-align: center;
            }

            .action-buttons {
                justify-content: center;
                gap: var(--spacing-sm);
            }

            .action-btn {
                flex: 1;
                justify-content: center;
                min-width: 120px;
                font-size: 0.85rem;
            }

            .carrito-btn {
                width: 100%;
                justify-content: center;
                margin-bottom: var(--spacing-sm);
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: var(--spacing-md);
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .header h1 i {
                font-size: 2rem;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
                gap: var(--spacing-sm);
            }

            .action-btn {
                width: 100%;
                justify-content: center;
                min-width: auto;
            }

            .welcome-text {
                font-size: 1rem;
            }
        }

        /* Separador visual */
        .user-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: var(--spacing-md);
        }

        @media (max-width: 768px) {
            .user-section {
                align-items: center;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<!-- Header -->
<div class="container">
    <div class="header">
        <h1>
            <i class="fas fa-graduation-cap"></i>
            Dashboard de Cursos
        </h1>
        
        <div class="user-section">
            <span class="welcome-text">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            
            <div class="user-info">
                <!-- Botón del Carrito -->
                <a href="/carrito" class="carrito-btn">
                    <i class="fas fa-shopping-cart"></i>
                    Mi Carrito
                    <span id="carrito-contador" class="carrito-contador">0</span>
                </a>

                <!-- Botones de acción directos -->
                <div class="action-buttons">
                    <a href="/perfil" class="action-btn">
                        <i class="fas fa-user-edit"></i>
                        Perfil
                    </a>
                    
                    <a href="/estadisticas" class="action-btn">
                        <i class="fas fa-chart-bar"></i>
                        Estadísticas
                    </a>
                    
                    <a href="/cambiar-password" class="action-btn">
                        <i class="fas fa-key"></i>
                        Contraseña
                    </a>
                    
                    <a href="/logout" class="action-btn danger">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes -->
    <div id="mensaje" class="mensaje" style="display: none;"></div>

    <!-- Estadísticas Rápidas -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="total-cursos">0</div>
                <div class="stat-label">Mis Cursos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="total-compras">0</div>
                <div class="stat-label">Compras Realizadas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="total-ingresos">$0</div>
                <div class="stat-label">Ingresos Generados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="cursos-disponibles">0</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
    </div>

    <!-- Crear Curso -->
    <div class="form-container">
        <div class="form-header">
            <h2>
                <i class="fas fa-plus-circle"></i>
                Crear Nuevo Curso
            </h2>
            <button class="toggle-form-btn" onclick="toggleFormulario()">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="form-content" id="formContent">
            <form id="formCurso">
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-heading"></i>
                            Título del Curso
                        </label>
                        <input type="text" name="titulo" placeholder="Ej: Desarrollo Web Completo" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user-tie"></i>
                            Instructor
                        </label>
                        <input type="text" name="instructor" placeholder="Nombre del instructor" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-dollar-sign"></i>
                            Precio
                        </label>
                        <input type="number" name="precio" placeholder="99.99" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-image"></i>
                            Imagen del Curso
                        </label>
                        <input type="file" name="imagen" accept="image/*" required class="file-input">
                        <div class="file-preview" id="filePreview"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-align-left"></i>
                        Descripción
                    </label>
                    <textarea name="descripcion" placeholder="Describe el contenido del curso..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Crear Curso
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i>
                        Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs de Navegación -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="cambiarTab('mis-cursos')">
                <i class="fas fa-book"></i>
                Mis Cursos
            </button>
            <button class="tab-btn" onclick="cambiarTab('disponibles')">
                <i class="fas fa-shopping-cart"></i>
                Disponibles
            </button>
            <button class="tab-btn" onclick="cambiarTab('mis-compras')">
                <i class="fas fa-receipt"></i>
                Mis Compras
            </button>
        </div>
    </div>

    <!-- Contenido de Tabs -->
    <div class="tab-content active" id="tab-mis-cursos">
        <div class="courses-header">
            <h2>
                <i class="fas fa-book"></i>
                Mis Cursos Creados
            </h2>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar en mis cursos..." id="search-mis-cursos">
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" onclick="cambiarVista('grid')" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" onclick="cambiarVista('list')" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="listaCursos" class="courses-grid"></div>
    </div>

    <div class="tab-content" id="tab-disponibles">
        <div class="courses-header">
            <h2>
                <i class="fas fa-shopping-cart"></i>
                Cursos Disponibles
            </h2>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar cursos..." id="search-disponibles">
                </div>
                <div class="filter-box">
                    <select id="filter-precio">
                        <option value="">Todos los precios</option>
                        <option value="0-50">$0 - $50</option>
                        <option value="50-100">$50 - $100</option>
                        <option value="100-200">$100 - $200</option>
                        <option value="200+">$200+</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="cursosDisponibles" class="courses-grid"></div>
    </div>

    <div class="tab-content" id="tab-mis-compras">
        <div class="courses-header">
            <h2>
                <i class="fas fa-receipt"></i>
                Mis Compras
            </h2>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar en compras..." id="search-compras">
                </div>
                <div class="sort-box">
                    <select id="sort-compras">
                        <option value="fecha-desc">Más recientes</option>
                        <option value="fecha-asc">Más antiguos</option>
                        <option value="precio-desc">Precio mayor</option>
                        <option value="precio-asc">Precio menor</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="listaCompras" class="courses-grid"></div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Cargando...</p>
    </div>
</div>

<!-- Modal de Edición -->
<div id="modalEdicion" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Curso</h3>
            <button class="modal-close" onclick="cerrarModalEdicion()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditarCurso">
            <input type="hidden" id="editCursoId" name="id">

            <div class="form-group">
                <label>
                    <i class="fas fa-heading"></i>
                    Título del Curso
                </label>
                <input type="text" id="editTitulo" name="titulo" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-user-tie"></i>
                    Instructor
                </label>
                <input type="text" id="editInstructor" name="instructor" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-dollar-sign"></i>
                    Precio (USD)
                </label>
                <input type="number" id="editPrecio" name="precio" step="0.01" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-align-left"></i>
                    Descripción
                </label>
                <textarea id="editDescripcion" name="descripcion" required></textarea>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-image"></i>
                    Imagen Actual
                </label>
                <div class="current-image">
                    <img id="editImagenActual" src="" alt="Imagen actual">
                </div>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-upload"></i>
                    Nueva Imagen (opcional)
                </label>
                <input type="file" id="editImagen" name="imagen" accept="image/*" class="file-input">
                <small class="form-hint">Deja vacío para mantener la imagen actual</small>
            </div>

            <div class="modal-actions">
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

<!-- Modal de Confirmación -->
<div id="modalConfirmacion" class="modal">
    <div class="modal-content confirmation-modal">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Acción</h3>
        </div>
        <div class="modal-body">
            <p id="confirmacionTexto"></p>
        </div>
        <div class="modal-actions">
            <button id="btnConfirmar" class="btn btn-danger">
                <i class="fas fa-check"></i>
                Confirmar
            </button>
            <button onclick="cerrarModalConfirmacion()" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
        </div>
    </div>
</div>

<script src="/assets/js/dashboard.js"></script>
</body>
</html>