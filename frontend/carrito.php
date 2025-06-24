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
    <title>Carrito de Compras - MVC SISTEMA</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos específicos del carrito */
        .carrito-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-lg);
        }

        .carrito-header {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: var(--spacing-xl) var(--spacing-2xl);
            border-radius: var(--radius-xl);
            margin-bottom: var(--spacing-xl);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .carrito-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-accent);
        }

        .carrito-main {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: var(--spacing-xl);
        }

        .carrito-items {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
        }

        .carrito-resumen {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            height: fit-content;
            position: sticky;
            top: var(--spacing-lg);
        }

        .item-carrito {
            display: flex;
            gap: var(--spacing-lg);
            padding: var(--spacing-xl);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-lg);
            background: var(--secondary-bg);
            transition: all var(--transition-normal);
        }

        .item-carrito:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--border-hover);
        }

        .item-imagen {
            flex: 0 0 120px;
            height: 80px;
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .item-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .item-titulo {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
        }

        .item-instructor {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: var(--spacing-sm);
        }

        .item-precio {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        .item-acciones {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: var(--spacing-sm);
        }

        .btn-eliminar {
            background: var(--gradient-danger);
            color: white;
            border: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-normal);
            font-size: 0.9rem;
        }

        .btn-eliminar:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .resumen-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-md) 0;
            border-bottom: 1px solid var(--border-color);
        }

        .resumen-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            border-top: 2px solid var(--accent-blue);
            padding-top: var(--spacing-lg);
            margin-top: var(--spacing-lg);
        }

        .btn-procesar {
            width: 100%;
            background: var(--gradient-success);
            color: white;
            border: none;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-normal);
            margin-top: var(--spacing-lg);
        }

        .btn-procesar:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-procesar:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .carrito-vacio {
            text-align: center;
            padding: var(--spacing-2xl);
            color: var(--text-secondary);
        }

        .carrito-vacio i {
            font-size: 4rem;
            margin-bottom: var(--spacing-lg);
            opacity: 0.5;
        }

        .metodo-pago-select {
            width: 100%;
            background: var(--secondary-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            color: var(--text-primary);
            margin-bottom: var(--spacing-lg);
        }

        .recomendaciones {
            margin-top: var(--spacing-2xl);
        }

        .recomendaciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--spacing-lg);
            margin-top: var(--spacing-lg);
        }

        .curso-recomendado {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            transition: all var(--transition-normal);
        }

        .curso-recomendado:hover {
            transform: translateY(-2px);
            border-color: var(--border-hover);
        }

        @media (max-width: 768px) {
            .carrito-main {
                grid-template-columns: 1fr;
            }

            .carrito-resumen {
                position: static;
                order: -1;
            }

            .item-carrito {
                flex-direction: column;
                text-align: center;
            }

            .item-imagen {
                flex: none;
                align-self: center;
            }
        }
    </style>
    <script>
        const id_cliente = "<?= $_SESSION['id_cliente'] ?>";
        const llave_secreta = "<?= $_SESSION['llave_secreta'] ?>";
    </script>
</head>
<body>
<div class="carrito-container">
    <!-- Header del Carrito -->
    <div class="carrito-header">
        <div>
            <h1>
                <i class="fas fa-shopping-cart"></i>
                Mi Carrito de Compras
            </h1>
            <p style="color: var(--text-secondary); margin-top: var(--spacing-sm);">
                Revisa tus cursos seleccionados antes de proceder al pago
            </p>
        </div>
        <div class="header-actions">
            <a href="/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <div id="mensaje" class="mensaje" style="display: none;"></div>

    <!-- Contenido Principal -->
    <div class="carrito-main">
        <!-- Items del Carrito -->
        <div class="carrito-items">
            <h2>
                <i class="fas fa-list"></i>
                Cursos en tu carrito
                <span id="contador-items" class="badge">0</span>
            </h2>
            
            <div id="lista-items">
                <!-- Los items se cargarán aquí dinámicamente -->
            </div>

            <!-- Botones de acción -->
            <div style="margin-top: var(--spacing-xl); display: flex; gap: var(--spacing-md);">
                <button id="btn-vaciar" class="btn btn-warning" onclick="vaciarCarrito()" style="display: none;">
                    <i class="fas fa-trash"></i>
                    Vaciar Carrito
                </button>
                <button id="btn-sincronizar" class="btn btn-secondary" onclick="sincronizarCarrito()">
                    <i class="fas fa-sync"></i>
                    Sincronizar
                </button>
            </div>
        </div>

        <!-- Resumen y Checkout -->
        <div class="carrito-resumen">
            <h3>
                <i class="fas fa-calculator"></i>
                Resumen de Compra
            </h3>

            <div id="resumen-detalles">
                <div class="resumen-item">
                    <span>Cantidad de cursos:</span>
                    <span id="resumen-cantidad">0</span>
                </div>
                <div class="resumen-item">
                    <span>Subtotal:</span>
                    <span id="resumen-subtotal">$0.00</span>
                </div>
                <div class="resumen-item resumen-total">
                    <span>Total a pagar:</span>
                    <span id="resumen-total">$0.00</span>
                </div>
            </div>

            <!-- Selección de método de pago -->
            <div style="margin-top: var(--spacing-lg);">
                <label style="color: var(--text-secondary); margin-bottom: var(--spacing-sm); display: block;">
                    <i class="fas fa-credit-card"></i>
                    Método de pago:
                </label>
                <select id="metodo-pago" class="metodo-pago-select">
                    <option value="">Seleccionar método de pago</option>
                </select>
            </div>

            <!-- Botón de procesar compra -->
            <button id="btn-procesar" class="btn-procesar" onclick="procesarCompra()" disabled>
                <i class="fas fa-credit-card"></i>
                Procesar Compra
            </button>

            <!-- Información adicional -->
            <div style="margin-top: var(--spacing-lg); padding: var(--spacing-md); background: var(--primary-bg); border-radius: var(--radius-md); font-size: 0.9rem; color: var(--text-muted);">
                <i class="fas fa-info-circle"></i>
                Tus cursos estarán disponibles inmediatamente después del pago.
            </div>
        </div>
    </div>

    <!-- Recomendaciones -->
    <div class="recomendaciones" id="recomendaciones-section" style="display: none;">
        <h2>
            <i class="fas fa-star"></i>
            Cursos Recomendados
        </h2>
        <div id="recomendaciones-grid" class="recomendaciones-grid">
            <!-- Las recomendaciones se cargarán aquí -->
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Procesando...</p>
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

<script src="/assets/js/carrito.js"></script>
</body>
</html>