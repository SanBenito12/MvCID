<?php
// index.php - Router principal usando RouterController

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir el controlador de rutas
require_once __DIR__ . '/backend/controladores/RouterController.php';

try {
    // Crear instancia del controlador de rutas
    $router = new RouterController();

    // Manejar la petición
    $router->handleRequest();

} catch (Exception $e) {
    // Manejar errores críticos
    error_log("Error crítico en router: " . $e->getMessage());

    // Determinar si es una petición de API
    $isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;

    if ($isApiRequest) {
        // Respuesta de error para API
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Error interno del servidor',
            'message' => 'Se produjo un error inesperado',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Página de error para peticiones web
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error 500 - MVC Sistema</title>
            <style>
                body {
                    font-family: 'Inter', sans-serif;
                    background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                    color: white;
                    padding: 20px;
                }
                .error-container {
                    text-align: center;
                    background: rgba(22, 33, 62, 0.8);
                    backdrop-filter: blur(20px);
                    padding: 60px 40px;
                    border-radius: 24px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
                }
                h1 {
                    font-size: 4rem;
                    margin: 0 0 20px 0;
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
                p { font-size: 1.2rem; margin: 20px 0; color: #b8c5d6; }
                a {
                    color: #00d4ff;
                    text-decoration: none;
                    font-weight: bold;
                    padding: 12px 24px;
                    background: rgba(0, 212, 255, 0.1);
                    border-radius: 12px;
                    border: 1px solid rgba(0, 212, 255, 0.3);
                    display: inline-block;
                    margin-top: 20px;
                    transition: all 0.3s ease;
                }
                a:hover {
                    background: rgba(0, 212, 255, 0.2);
                    transform: translateY(-2px);
                }
            </style>
        </head>
        <body>
        <div class="error-container">
            <h1>500</h1>
            <p>Error interno del servidor</p>
            <p>Se produjo un error inesperado. Por favor, intenta nuevamente.</p>
            <a href="/">🏠 Volver al inicio</a>
        </div>
        </body>
        </html>
        <?php
    }
}
?>