<?php
// backend/api/cursos.php - API actualizada con controladores separados
header("Content-Type: application/json");

// Incluir el controlador
require_once __DIR__ . '/../controladores/CursosController.php';

// Crear instancia del controlador
$controller = new CursosController();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($controller);
            break;

        case 'POST':
            handlePost($controller);
            break;

        case 'PATCH':
            handlePatch($controller);
            break;

        case 'DELETE':
            handleDelete($controller);
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
 * Manejar GET - Obtener cursos
 */
function handleGet($controller) {
    $id = $_GET['id'] ?? null;
    $id_creador = $_GET['id_creador'] ?? null;
    $titulo = $_GET['titulo'] ?? null;
    $precio_min = $_GET['precio_min'] ?? null;
    $precio_max = $_GET['precio_max'] ?? null;
    $disponibles = $_GET['disponibles'] ?? false;
    $estadisticas = $_GET['estadisticas'] ?? false;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if ($id) {
        // Obtener curso específico
        $resultado = $controller->obtenerPorId($id);

        if ($resultado['success']) {
            echo json_encode($resultado['curso']);
        } else {
            http_response_code(404);
            echo json_encode($resultado);
        }
    } elseif ($estadisticas && $id_cliente && $llave_secreta) {
        // Obtener estadísticas del creador
        $resultado = $controller->obtenerEstadisticas($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($disponibles && $id_cliente && $llave_secreta) {
        // Obtener cursos disponibles para compra
        $resultado = $controller->obtenerDisponiblesParaCliente($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado['cursos']);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($titulo) {
        // Buscar por título
        $resultado = $controller->buscarPorTitulo($titulo);

        if ($resultado['success']) {
            echo json_encode($resultado['cursos']);
        } else {
            http_response_code(400);
            echo json_encode($resultado);
        }
    } elseif ($precio_min !== null && $precio_max !== null) {
        // Filtrar por rango de precio
        $resultado = $controller->obtenerPorRangoPrecio($precio_min, $precio_max);

        if ($resultado['success']) {
            echo json_encode($resultado['cursos']);
        } else {
            http_response_code(400);
            echo json_encode($resultado);
        }
    } elseif ($id_creador) {
        // Obtener cursos de un creador específico usando id_creador directamente
        $resultado = $controller->obtenerCursosPorCreador($id_creador);

        if ($resultado['success']) {
            echo json_encode($resultado['cursos']);
        } else {
            http_response_code(400);
            echo json_encode($resultado);
        }
    } elseif ($id_cliente && $llave_secreta) {
        // Obtener cursos del cliente autenticado
        $resultado = $controller->obtenerCursosPorCliente($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado['cursos']);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } else {
        // Obtener todos los cursos
        $resultado = $controller->obtenerTodos();

        if ($resultado['success']) {
            echo json_encode($resultado['cursos']);
        } else {
            http_response_code(500);
            echo json_encode($resultado);
        }
    }
}

/**
 * Manejar POST - Crear curso
 */
function handlePost($controller) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_cliente = $_GET['id_cliente'] ?? $data['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $data['llave_secreta'] ?? null;

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    // Remover credenciales de los datos del curso
    unset($data['id_cliente'], $data['llave_secreta']);

    $resultado = $controller->crear($data, $id_cliente, $llave_secreta);

    if ($resultado['success']) {
        http_response_code(201);
        echo json_encode($resultado);
    } else {
        if (strpos($resultado['error'], 'Credenciales') !== false) {
            $statusCode = 401;
        } else {
            $statusCode = 400;
        }
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar PATCH - Actualizar curso
 */
function handlePatch($controller) {
    $id = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);
    $id_cliente = $_GET['id_cliente'] ?? $data['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $data['llave_secreta'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID del curso requerido"]);
        return;
    }

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    // Remover credenciales de los datos del curso
    unset($data['id_cliente'], $data['llave_secreta']);

    $resultado = $controller->actualizar($id, $data, $id_cliente, $llave_secreta);

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        if (strpos($resultado['error'], 'Credenciales') !== false) {
            $statusCode = 401;
        } elseif (strpos($resultado['error'], 'permisos') !== false) {
            $statusCode = 403;
        } else {
            $statusCode = 400;
        }
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar DELETE - Eliminar curso
 */
function handleDelete($controller) {
    $id = $_GET['id'] ?? null;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID del curso requerido"]);
        return;
    }

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    $resultado = $controller->eliminar($id, $id_cliente, $llave_secreta);

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        if (strpos($resultado['error'], 'Credenciales') !== false) {
            $statusCode = 401;
        } elseif (strpos($resultado['error'], 'permisos') !== false) {
            $statusCode = 403;
        } else {
            $statusCode = 400;
        }
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}
?>