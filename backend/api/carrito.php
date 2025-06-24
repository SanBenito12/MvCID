<?php
// backend/api/carrito.php - API del carrito de compras
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Incluir el controlador
require_once __DIR__ . '/../controladores/CarritoController.php';

// Crear instancia del controlador
$controller = new CarritoController();

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Manejar preflight requests
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    switch ($method) {
        case 'GET':
            handleGet($controller);
            break;

        case 'POST':
            handlePost($controller);
            break;

        case 'DELETE':
            handleDelete($controller);
            break;

        case 'PATCH':
            handlePatch($controller);
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error interno del servidor",
        "message" => $e->getMessage()
    ]);
}

/**
 * Manejar GET - Obtener carrito, resumen, estadísticas, etc.
 */
function handleGet($controller) {
    $accion = $_GET['accion'] ?? 'carrito';
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;
    $id_curso = $_GET['id_curso'] ?? null;

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    switch ($accion) {
        case 'carrito':
            // Obtener carrito completo con items
            $resultado = $controller->obtenerCarrito($id_cliente, $llave_secreta);
            break;

        case 'resumen':
            // Obtener solo resumen (cantidad y total)
            $resultado = $controller->obtenerResumen($id_cliente, $llave_secreta);
            break;

        case 'estadisticas':
            // Obtener estadísticas del carrito
            $resultado = $controller->obtenerEstadisticas($id_cliente, $llave_secreta);
            break;

        case 'verificar':
            // Verificar si un curso específico está en el carrito
            if (!$id_curso) {
                http_response_code(400);
                echo json_encode(["error" => "ID de curso requerido"]);
                return;
            }
            $resultado = $controller->verificarCursoEnCarrito($id_curso, $id_cliente, $llave_secreta);
            break;

        case 'recomendaciones':
            // Obtener cursos recomendados
            $limite = $_GET['limite'] ?? 5;
            $resultado = $controller->obtenerRecomendaciones($id_cliente, $llave_secreta, $limite);
            break;

        case 'historial':
            // Obtener historial de carritos procesados
            $resultado = $controller->obtenerHistorial($id_cliente, $llave_secreta);
            break;

        case 'sincronizar':
            // Sincronizar carrito (limpiar items inválidos)
            $resultado = $controller->sincronizarCarrito($id_cliente, $llave_secreta);
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Acción no válida"]);
            return;
    }

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        $statusCode = determinarCodigoError($resultado['error']);
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar POST - Agregar cursos, procesar compra
 */
function handlePost($controller) {
    $data = json_decode(file_get_contents("php://input"), true);
    $accion = $_GET['accion'] ?? $data['accion'] ?? 'agregar';
    $id_cliente = $_GET['id_cliente'] ?? $data['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $data['llave_secreta'] ?? null;

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    switch ($accion) {
        case 'agregar':
            // Agregar curso al carrito
            $id_curso = $data['id_curso'] ?? null;
            if (!$id_curso) {
                http_response_code(400);
                echo json_encode(["error" => "ID de curso requerido"]);
                return;
            }
            $resultado = $controller->agregarCurso($id_curso, $id_cliente, $llave_secreta);
            break;

        case 'procesar':
            // Procesar compra del carrito
            $id_metodo_pago = $data['id_metodo_pago'] ?? null;
            if (!$id_metodo_pago) {
                http_response_code(400);
                echo json_encode(["error" => "Método de pago requerido"]);
                return;
            }
            $resultado = $controller->procesarCompra($id_metodo_pago, $id_cliente, $llave_secreta);
            break;

        case 'descuento':
            // Aplicar código de descuento
            $codigo = $data['codigo_descuento'] ?? null;
            if (!$codigo) {
                http_response_code(400);
                echo json_encode(["error" => "Código de descuento requerido"]);
                return;
            }
            $resultado = $controller->aplicarDescuento($codigo, $id_cliente, $llave_secreta);
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Acción no válida"]);
            return;
    }

    if ($resultado['success']) {
        $statusCode = ($accion === 'procesar') ? 201 : 200;
        http_response_code($statusCode);
        echo json_encode($resultado);
    } else {
        $statusCode = determinarCodigoError($resultado['error']);
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar DELETE - Eliminar curso o vaciar carrito
 */
function handleDelete($controller) {
    $accion = $_GET['accion'] ?? 'eliminar';
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    switch ($accion) {
        case 'eliminar':
            // Eliminar curso específico
            $id_curso = $_GET['id_curso'] ?? null;
            if (!$id_curso) {
                http_response_code(400);
                echo json_encode(["error" => "ID de curso requerido"]);
                return;
            }
            $resultado = $controller->eliminarCurso($id_curso, $id_cliente, $llave_secreta);
            break;

        case 'vaciar':
            // Vaciar carrito completamente
            $resultado = $controller->vaciarCarrito($id_cliente, $llave_secreta);
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Acción no válida"]);
            return;
    }

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        $statusCode = determinarCodigoError($resultado['error']);
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar PATCH - Actualizar carrito (futuras funcionalidades)
 */
function handlePatch($controller) {
    $data = json_decode(file_get_contents("php://input"), true);
    $accion = $_GET['accion'] ?? $data['accion'] ?? 'actualizar';
    $id_cliente = $_GET['id_cliente'] ?? $data['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $data['llave_secreta'] ?? null;

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    switch ($accion) {
        case 'sincronizar':
            // Sincronizar carrito
            $resultado = $controller->sincronizarCarrito($id_cliente, $llave_secreta);
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Acción no válida"]);
            return;
    }

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        $statusCode = determinarCodigoError($resultado['error']);
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Determinar código de error HTTP apropiado
 */
function determinarCodigoError($error) {
    if (strpos($error, 'Credenciales') !== false) {
        return 401;
    } elseif (strpos($error, 'no encontrado') !== false || 
              strpos($error, 'no existe') !== false) {
        return 404;
    } elseif (strpos($error, 'ya está') !== false || 
              strpos($error, 'ya has') !== false ||
              strpos($error, 'propio curso') !== false) {
        return 409; // Conflict
    } elseif (strpos($error, 'vacío') !== false) {
        return 422; // Unprocessable Entity
    } else {
        return 400; // Bad Request
    }
}

/**
 * Logging de actividad del carrito (opcional)
 */
function logActividad($accion, $id_cliente, $detalles = '') {
    $timestamp = date('Y-m-d H:i:s');
    $log = "[{$timestamp}] Carrito - Cliente: {$id_cliente}, Acción: {$accion}";
    if ($detalles) {
        $log .= ", Detalles: {$detalles}";
    }
    error_log($log);
}
?>