<?php
// router.php - Versión final funcionando

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Rutas del frontend
if ($uri === '/' || $uri === '/index.php') {
    require_once 'frontend/index.php';
    exit;
}

if ($uri === '/login.php' || $uri === '/login') {
    require_once 'frontend/login.php';
    exit;
}

if ($uri === '/registro.php' || $uri === '/registro') {
    require_once 'frontend/registro.php';
    exit;
}

if ($uri === '/dashboard.php' || $uri === '/dashboard') {
    require_once 'frontend/dashboard.php';
    exit;
}

if ($uri === '/logout.php' || $uri === '/logout') {
    require_once 'frontend/logout.php';
    exit;
}

// Rutas de la API con manejo correcto
if (strpos($uri, '/api/') === 0) {

    // Rutas específicas de clientes
    if ($uri === '/api/clientes/login') {
        $_GET['accion'] = 'login';
        require_once 'backend/api/clientes.php';
        exit;
    }

    if ($uri === '/api/clientes/registro') {
        $_GET['accion'] = 'registro';
        require_once 'backend/api/clientes.php';
        exit;
    }

    if (strpos($uri, '/api/clientes') === 0) {
        require_once 'backend/api/clientes.php';
        exit;
    }

    if (strpos($uri, '/api/cursos') === 0) {
        require_once 'backend/api/cursos.php';
        exit;
    }

    if (strpos($uri, '/api/compras') === 0) {
        require_once 'backend/api/compras.php';
        exit;
    }

    if (strpos($uri, '/api/auth') === 0) {
        require_once 'backend/api/auth.php';
        exit;
    }

    if (strpos($uri, '/api/metodos_pago') === 0) {
        require_once 'backend/api/metodos_pago.php';
        exit;
    }

    // API no encontrada
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        "error" => "Ruta API no encontrada",
        "uri" => $uri,
        "method" => $method
    ]);
    exit;
}

// Servir archivos estáticos (CSS, JS, imágenes)
if (strpos($uri, '/assets/') === 0) {
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
            'svg' => 'image/svg+xml'
        ];

        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }

        readfile($filePath);
        exit;
    }
}

// 404 - Página no encontrada
http_response_code(404);
echo "404 - Página no encontrada: $uri";
?>