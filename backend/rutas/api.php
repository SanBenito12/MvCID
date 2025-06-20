<?php
require_once __DIR__ . '/../includes/db.php';

$route = $_SERVER['API_ROUTE'] ?? '';

if (empty($route)) {
    $uri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    $route = "$method $uri";
    $route = strtok($route, '?');
}

// Enrutamiento
switch ($route) {
    // === CLIENTES ===
    case "POST /clientes/login":
        $_GET['accion'] = 'login';
        require_once __DIR__ . "/../api/clientes.php";
        break;
        
    case "POST /clientes/registro":
        $_GET['accion'] = 'registro';
        require_once __DIR__ . "/../api/clientes.php";
        break;
    
    case "GET /clientes":
        require_once __DIR__ . "/../api/clientes.php";
        break;
    
    // === CURSOS ===
    case "GET /cursos":
        require_once __DIR__ . "/../api/cursos.php";
        break;
        
    case "POST /cursos":
        require_once __DIR__ . "/../api/cursos.php";
        break;
        
    case "PATCH /cursos":
        require_once __DIR__ . "/../api/cursos.php";
        break;
        
    case "DELETE /cursos":
        require_once __DIR__ . "/../api/cursos.php";
        break;
    
    // === COMPRAS ===
    case "GET /compras":
        require_once __DIR__ . "/../api/compras.php";
        break;
        
    case "POST /compras":
        require_once __DIR__ . "/../api/compras.php";
        break;
    
    // === RUTA NO ENCONTRADA ===
    default:
        http_response_code(404);
        echo json_encode([
            "error" => "Ruta API no encontrada",
            "ruta_recibida" => $route,
            "metodo" => $_SERVER['REQUEST_METHOD'],
            "uri" => $_SERVER['REQUEST_URI']
        ]);
        break;
}
?>