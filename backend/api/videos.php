<?php
// backend/api/videos.php
// API simple para videos de YouTube configurados

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../controladores/YouTubeSimpleController.php';

try {
    $controller = new YouTubeSimpleController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Solo soportamos GET para esta implementación simple
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido. Solo GET está soportado.'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'todos':
        case '':
            // GET /api/videos?action=todos
            $resultado = $controller->obtenerTodos();
            break;

        case 'categoria':
            // GET /api/videos?action=categoria&categoria=Tutoriales
            $categoria = $_GET['categoria'] ?? '';
            $resultado = $controller->obtenerPorCategoria($categoria);
            break;

        case 'buscar':
            // GET /api/videos?action=buscar&q=termino
            $termino = $_GET['q'] ?? '';
            $resultado = $controller->buscar($termino);
            break;

        case 'categorias':
            // GET /api/videos?action=categorias
            $resultado = $controller->obtenerCategorias();
            break;

        case 'video':
            // GET /api/videos?action=video&id=VIDEO_ID
            $id = $_GET['id'] ?? '';
            $resultado = $controller->obtenerVideo($id);
            break;

        default:
            $resultado = [
                'success' => false,
                'error' => 'Acción no válida',
                'acciones_disponibles' => [
                    'todos' => 'Obtener todos los videos',
                    'categoria' => 'Filtrar por categoría (?categoria=nombre)',
                    'buscar' => 'Buscar videos (?q=termino)',
                    'categorias' => 'Obtener lista de categorías',
                    'video' => 'Obtener video específico (?id=VIDEO_ID)'
                ]
            ];
            http_response_code(400);
            break;
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>