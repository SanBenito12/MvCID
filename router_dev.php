<?php
// router_dev.php - Router específico para servidor de desarrollo PHP

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Si es un archivo que existe, servirlo directamente
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Dejar que PHP sirva el archivo
}

// Rutas del frontend
switch ($uri) {
    case '/':
    case '/index.php':
        require_once __DIR__ . '/frontend/index.php';
        break;

    case '/login':
    case '/login.php':
        require_once __DIR__ . '/frontend/login.php';
        break;

    case '/registro':
    case '/registro.php':
        require_once __DIR__ . '/frontend/registro.php';
        break;

    case '/dashboard':
    case '/dashboard.php':
        require_once __DIR__ . '/frontend/dashboard.php';
        break;

    case '/logout':
    case '/logout.php':
        require_once __DIR__ . '/frontend/logout.php';
        break;

    // Rutas de API
    case '/api/clientes/login':
        $_GET['accion'] = 'login';
        require_once __DIR__ . '/backend/api/clientes.php';
        break;

    case '/api/clientes/registro':
        $_GET['accion'] = 'registro';
        require_once __DIR__ . '/backend/api/clientes.php';
        break;

    case '/api/cursos':
        require_once __DIR__ . '/backend/api/cursos.php';
        break;

    case '/api/compras':
        require_once __DIR__ . '/backend/api/compras.php';
        break;

    case '/api/auth':
        require_once __DIR__ . '/backend/api/auth.php';
        break;

    case '/api/metodos_pago':
        require_once __DIR__ . '/backend/api/metodos_pago.php';
        break;

    default:
        // Si empieza con /api/, es una API no encontrada
        if (strpos($uri, '/api/') === 0) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                "error" => "API no encontrada",
                "uri" => $uri,
                "method" => $method
            ]);
        } else {
            // 404 para páginas
            http_response_code(404);
            echo "<h1>404 - Página no encontrada</h1>";
            echo "<p>URI: $uri</p>";
            echo "<p><a href='/'>Ir al inicio</a></p>";
        }
        break;
}
?>