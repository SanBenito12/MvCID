<?php
// backend/api/videos.php
// API REST para videos de YouTube con integración completa de YouTube IFrame Player API

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../controladores/YouTubeSimpleController.php';

/**
 * Función para validar parámetros de entrada
 */
function validarParametros($parametros, $requeridos = [])
{
    $errores = [];
    
    foreach ($requeridos as $campo) {
        if (!isset($parametros[$campo]) || empty(trim($parametros[$campo]))) {
            $errores[] = "El campo '{$campo}' es requerido";
        }
    }
    
    return $errores;
}

/**
 * Función para sanitizar entrada
 */
function sanitizarEntrada($valor)
{
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

/**
 * Función para registrar log de API
 */
function registrarLog($accion, $parametros = [], $resultado = [])
{
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'accion' => $accion,
        'parametros' => $parametros,
        'success' => $resultado['success'] ?? false
    ];
    
    // En producción, guardar en archivo de log
    // error_log(json_encode($log), 3, __DIR__ . '/../logs/api_videos.log');
}

try {
    $controller = new YouTubeSimpleController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Solo soportamos GET para videos (REST read-only)
    if ($method !== 'GET') {
        http_response_code(405);
        $error = [
            'success' => false,
            'error' => 'Método no permitido. Solo GET está soportado.',
            'method_received' => $method,
            'allowed_methods' => ['GET']
        ];
        registrarLog('method_not_allowed', ['method' => $method], $error);
        echo json_encode($error, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Obtener y sanitizar parámetros
    $action = sanitizarEntrada($_GET['action'] ?? '');
    $parametros = [
        'action' => $action,
        'categoria' => sanitizarEntrada($_GET['categoria'] ?? ''),
        'q' => sanitizarEntrada($_GET['q'] ?? ''),
        'id' => sanitizarEntrada($_GET['id'] ?? ''),
        'nivel' => sanitizarEntrada($_GET['nivel'] ?? ''),
        'format' => sanitizarEntrada($_GET['format'] ?? 'json')
    ];
    
    switch ($action) {
        case 'todos':
        case '':
            // GET /api/videos?action=todos
            $resultado = $controller->obtenerTodos();
            registrarLog('obtener_todos', $parametros, $resultado);
            break;

        case 'categoria':
            // GET /api/videos?action=categoria&categoria=PHP
            $categoria = $parametros['categoria'];
            $resultado = $controller->obtenerPorCategoria($categoria);
            registrarLog('obtener_por_categoria', $parametros, $resultado);
            break;

        case 'nivel':
            // GET /api/videos?action=nivel&nivel=Principiante
            $nivel = $parametros['nivel'];
            if (empty($nivel)) {
                $resultado = [
                    'success' => false,
                    'error' => 'Parámetro nivel requerido',
                    'niveles_disponibles' => ['Principiante', 'Intermedio', 'Avanzado']
                ];
            } else {
                // Usar el modelo directamente para obtener por nivel
                require_once __DIR__ . '/../modelos/VideoSimple.php';
                $videos = VideoSimple::obtenerPorNivel($nivel);
                $resultado = [
                    'success' => true,
                    'videos' => $videos,
                    'nivel' => $nivel,
                    'total' => count($videos)
                ];
            }
            registrarLog('obtener_por_nivel', $parametros, $resultado);
            break;

        case 'buscar':
            // GET /api/videos?action=buscar&q=termino&categoria=PHP&nivel=Principiante
            $termino = $parametros['q'];
            $filtros = [];
            
            if (!empty($parametros['categoria'])) {
                $filtros['categoria'] = $parametros['categoria'];
            }
            
            if (!empty($parametros['nivel'])) {
                $filtros['nivel'] = $parametros['nivel'];
            }
            
            // Búsqueda avanzada con filtros
            require_once __DIR__ . '/../modelos/VideoSimple.php';
            $videos = VideoSimple::buscar($termino, $filtros);
            
            $resultado = [
                'success' => true,
                'videos' => $videos,
                'termino' => $termino,
                'filtros_aplicados' => $filtros,
                'total' => count($videos)
            ];
            
            registrarLog('buscar_videos', $parametros, $resultado);
            break;

        case 'categorias':
            // GET /api/videos?action=categorias
            $resultado = $controller->obtenerCategorias();
            registrarLog('obtener_categorias', $parametros, $resultado);
            break;

        case 'niveles':
            // GET /api/videos?action=niveles
            require_once __DIR__ . '/../modelos/VideoSimple.php';
            $niveles = VideoSimple::obtenerNiveles();
            $resultado = [
                'success' => true,
                'niveles' => $niveles
            ];
            registrarLog('obtener_niveles', $parametros, $resultado);
            break;

        case 'video':
            // GET /api/videos?action=video&id=VIDEO_ID
            $id = $parametros['id'];
            $errores = validarParametros($parametros, ['id']);
            
            if (!empty($errores)) {
                $resultado = [
                    'success' => false,
                    'error' => 'Parámetros inválidos',
                    'detalles' => $errores
                ];
                http_response_code(400);
            } else {
                $resultado = $controller->obtenerVideo($id);
            }
            
            registrarLog('obtener_video', $parametros, $resultado);
            break;

        case 'player-config':
            // GET /api/videos?action=player-config
            $resultado = $controller->obtenerConfiguracionPlayer();
            registrarLog('obtener_player_config', $parametros, $resultado);
            break;

        case 'thumbnails':
            // GET /api/videos?action=thumbnails&id=VIDEO_ID
            $id = $parametros['id'];
            $errores = validarParametros($parametros, ['id']);
            
            if (!empty($errores)) {
                $resultado = [
                    'success' => false,
                    'error' => 'ID de video requerido',
                    'detalles' => $errores
                ];
                http_response_code(400);
            } else {
                require_once __DIR__ . '/../modelos/VideoSimple.php';
                $thumbnails = VideoSimple::getThumbnailUrls($id);
                $resultado = [
                    'success' => true,
                    'video_id' => $id,
                    'thumbnails' => $thumbnails
                ];
            }
            
            registrarLog('obtener_thumbnails', $parametros, $resultado);
            break;

        case 'estadisticas':
            // GET /api/videos?action=estadisticas
            require_once __DIR__ . '/../modelos/VideoSimple.php';
            $estadisticas = VideoSimple::obtenerEstadisticas();
            $resultado = [
                'success' => true,
                'estadisticas' => $estadisticas,
                'generado_en' => date('Y-m-d H:i:s')
            ];
            registrarLog('obtener_estadisticas', $parametros, $resultado);
            break;

        case 'embed-url':
            // GET /api/videos?action=embed-url&id=VIDEO_ID&autoplay=1&controls=1
            $id = $parametros['id'];
            $errores = validarParametros($parametros, ['id']);
            
            if (!empty($errores)) {
                $resultado = [
                    'success' => false,
                    'error' => 'ID de video requerido',
                    'detalles' => $errores
                ];
                http_response_code(400);
            } else {
                // Parámetros opcionales para el embed
                $embedParams = [];
                
                $allowedParams = [
                    'autoplay', 'controls', 'showinfo', 'rel', 
                    'modestbranding', 'iv_load_policy', 'start', 'end'
                ];
                
                foreach ($allowedParams as $param) {
                    if (isset($_GET[$param])) {
                        $embedParams[$param] = intval($_GET[$param]);
                    }
                }
                
                require_once __DIR__ . '/../modelos/VideoSimple.php';
                $embedUrl = VideoSimple::getEmbedUrl($id, $embedParams);
                
                $resultado = [
                    'success' => true,
                    'video_id' => $id,
                    'embed_url' => $embedUrl,
                    'parametros' => $embedParams
                ];
            }
            
            registrarLog('generar_embed_url', $parametros, $resultado);
            break;

        case 'help':
        case 'ayuda':
            // GET /api/videos?action=help
            $resultado = [
                'success' => true,
                'api_info' => [
                    'version' => '1.0.0',
                    'descripcion' => 'API REST para gestión de videos de YouTube con integración completa del Player',
                    'base_url' => '/api/videos'
                ],
                'endpoints' => [
                    'GET /?action=todos' => 'Obtener todos los videos',
                    'GET /?action=categoria&categoria=NOMBRE' => 'Filtrar por categoría',
                    'GET /?action=nivel&nivel=NIVEL' => 'Filtrar por nivel',
                    'GET /?action=buscar&q=TERMINO' => 'Buscar videos',
                    'GET /?action=buscar&q=TERMINO&categoria=CAT&nivel=NIV' => 'Búsqueda con filtros',
                    'GET /?action=categorias' => 'Obtener lista de categorías',
                    'GET /?action=niveles' => 'Obtener lista de niveles',
                    'GET /?action=video&id=VIDEO_ID' => 'Obtener video específico',
                    'GET /?action=player-config' => 'Obtener configuración del player',
                    'GET /?action=thumbnails&id=VIDEO_ID' => 'Obtener URLs de thumbnails',
                    'GET /?action=embed-url&id=VIDEO_ID' => 'Generar URL de embed personalizada',
                    'GET /?action=estadisticas' => 'Obtener estadísticas generales',
                    'GET /?action=help' => 'Mostrar esta ayuda'
                ],
                'parametros_embed' => [
                    'autoplay' => 'Reproducción automática (0 o 1)',
                    'controls' => 'Mostrar controles (0, 1 o 2)',
                    'showinfo' => 'Mostrar información (0 o 1)',
                    'rel' => 'Videos relacionados (0 o 1)',
                    'modestbranding' => 'Branding modesto (0 o 1)',
                    'iv_load_policy' => 'Anotaciones (1 o 3)',
                    'start' => 'Tiempo de inicio en segundos',
                    'end' => 'Tiempo de fin en segundos'
                ],
                'ejemplos' => [
                    'Todos los videos' => '/api/videos?action=todos',
                    'Videos de PHP' => '/api/videos?action=categoria&categoria=PHP',
                    'Videos principiante' => '/api/videos?action=nivel&nivel=Principiante',
                    'Buscar JavaScript' => '/api/videos?action=buscar&q=javascript',
                    'Video específico' => '/api/videos?action=video&id=WZJ2JVrOJ6M',
                    'Embed personalizado' => '/api/videos?action=embed-url&id=WZJ2JVrOJ6M&autoplay=1&controls=1'
                ]
            ];
            registrarLog('ayuda', $parametros, $resultado);
            break;

        default:
            $resultado = [
                'success' => false,
                'error' => 'Acción no válida',
                'accion_recibida' => $action,
                'acciones_disponibles' => [
                    'todos', 'categoria', 'nivel', 'buscar', 'categorias', 
                    'niveles', 'video', 'player-config', 'thumbnails', 
                    'embed-url', 'estadisticas', 'help'
                ],
                'ayuda' => 'Use ?action=help para obtener documentación completa'
            ];
            http_response_code(400);
            registrarLog('accion_invalida', $parametros, $resultado);
            break;
    }

    // Agregar metadatos a la respuesta
    if ($resultado['success']) {
        $resultado['meta'] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'request_id' => uniqid(),
            'api_version' => '1.0.0'
        ];
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    $error = [
        'success' => false,
        'error' => 'Parámetros inválidos: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    registrarLog('parametros_invalidos', $parametros ?? [], $error);
    echo json_encode($error, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    $error = [
        'success' => false,
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    registrarLog('error_interno', $parametros ?? [], $error);
    echo json_encode($error, JSON_UNESCAPED_UNICODE);
}
?>