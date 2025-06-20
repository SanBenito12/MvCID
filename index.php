<?php
// index.php - Router principal para servidor de desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Log para debug
error_log("Router: URI = $uri, Method = $method");

// Servir archivos estáticos directamente
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/', $uri)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath)) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf'
        ];

        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }

        readfile($filePath);
        exit;
    }
    // Si el archivo no existe, devolver 404
    http_response_code(404);
    echo "Archivo no encontrado: $uri";
    exit;
}

// === RUTAS DEL FRONTEND ===
switch ($uri) {
    case '/':
    case '/index.php':
        // Redirigir a login si no hay sesión
        session_start();
        if (isset($_SESSION['id_cliente']) && isset($_SESSION['llave_secreta'])) {
            header("Location: /dashboard");
        } else {
            header("Location: /login");
        }
        exit;

    case '/login':
    case '/login.php':
        require_once __DIR__ . '/frontend/login.php';
        exit;

    case '/registro':
    case '/registro.php':
        require_once __DIR__ . '/frontend/registro.php';
        exit;

    case '/dashboard':
    case '/dashboard.php':
        require_once __DIR__ . '/frontend/dashboard.php';
        exit;

    case '/logout':
    case '/logout.php':
        require_once __DIR__ . '/frontend/logout.php';
        exit;

    case '/debug-dashboard':
    case '/debug-dashboard.php':
        require_once __DIR__ . '/debug-dashboard.php';
        exit;

    case '/test-metodos':
    case '/test-metodos.php':
        require_once __DIR__ . '/test-metodos.php';
        exit;

    case '/api-metodos':
    case '/api-metodos.php':
        require_once __DIR__ . '/api-metodos.php';
        exit;
}

// === RUTAS DE API ===
if (strpos($uri, '/api/') === 0) {
    // Configurar headers para API
    header('Content-Type: application/json');

    // Manejar CORS si es necesario
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    try {
        // Rutas específicas de clientes
        if ($uri === '/api/clientes/login' && $method === 'POST') {
            $_GET['accion'] = 'login';
            require_once __DIR__ . '/backend/api/clientes.php';
            exit;
        }

        if ($uri === '/api/clientes/registro' && $method === 'POST') {
            $_GET['accion'] = 'registro';
            require_once __DIR__ . '/backend/api/clientes.php';
            exit;
        }

        if (preg_match('#^/api/clientes/?$#', $uri)) {
            require_once __DIR__ . '/backend/api/clientes.php';
            exit;
        }

        // Rutas de cursos
        if (preg_match('#^/api/cursos/?$#', $uri)) {
            require_once __DIR__ . '/backend/api/cursos.php';
            exit;
        }

        // Rutas de compras
        if (preg_match('#^/api/compras/?$#', $uri)) {
            require_once __DIR__ . '/backend/api/compras.php';
            exit;
        }

        // Ruta de autenticación
        if (preg_match('#^/api/auth/?$#', $uri)) {
            require_once __DIR__ . '/backend/api/auth.php';
            exit;
        }

        // API no encontrada
        http_response_code(404);
        echo json_encode([
            "error" => "Ruta API no encontrada",
            "uri" => $uri,
            "method" => $method,
            "available_routes" => [
                "POST /api/clientes/login",
                "POST /api/clientes/registro",
                "GET /api/clientes",
                "GET|POST|PATCH|DELETE /api/cursos",
                "GET|POST /api/compras",
                "GET /api/auth"
            ]
        ]);
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error interno del servidor",
            "message" => $e->getMessage()
        ]);
        exit;
    }
}

// === RUTAS BACKEND ESPECÍFICAS ===
if (strpos($uri, '/backend/') === 0) {
    // Rutas específicas del backend que pueden ser llamadas directamente
    if ($uri === '/backend/api/metodos_pago.php') {
        require_once __DIR__ . '/backend/api/metodos_pago.php';
        exit;
    }

    // Bloquear acceso directo a otros archivos del backend
    http_response_code(403);
    echo json_encode(["error" => "Acceso no permitido"]);
    exit;
}

// === 404 - PÁGINA NO ENCONTRADA ===
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: white;
        }
        .error-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 50px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        h1 { font-size: 4rem; margin: 0; }
        p { font-size: 1.2rem; margin: 20px 0; }
        a {
            color: #ffd700;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
        }
        a:hover { text-decoration: underline; }
        .debug {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="error-container">
    <h1>404</h1>
    <p>Página no encontrada</p>
    <p><a href="/">← Volver al inicio</a></p>

    <div class="debug">
        <strong>Debug Info:</strong><br>
        URI: <?= htmlspecialchars($uri) ?><br>
        Método: <?= htmlspecialchars($method) ?><br>
        Rutas disponibles:<br>
        • <a href="/login">Login</a><br>
        • <a href="/registro">Registro</a><br>
        • <a href="/dashboard">Dashboard</a>
    </div>
</div>
</body>
</html>