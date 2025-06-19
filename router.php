<?php
// router.php - Archivo principal de enrutamiento

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

// Rutas de la API
if (strpos($uri, '/api/') === 0) {
    // Remover /api/ del inicio
    $apiPath = substr($uri, 4);
    
    // Crear la ruta completa para el switch
    $route = "$method /$apiPath";
    
    // Incluir el archivo de rutas API
    $_SERVER['API_ROUTE'] = $route;
    require_once 'backend/rutas/api.php';
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