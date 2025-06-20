<?php
// backend/api/metodos_pago.php - API actualizada con controladores separados
header("Content-Type: application/json");

// Incluir el controlador
require_once __DIR__ . '/../controladores/MetodosPagoController.php';

// Crear instancia del controlador
$controller = new MetodosPagoController();

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
 * Manejar GET - Obtener métodos de pago
 */
function handleGet($controller) {
    $id = $_GET['id'] ?? null;
    $estadisticas = $_GET['estadisticas'] ?? false;
    $populares = $_GET['populares'] ?? false;
    $buscar = $_GET['buscar'] ?? null;
    $disponible = $_GET['disponible'] ?? null;
    $inicializar = $_GET['inicializar'] ?? false;
    $limite = $_GET['limite'] ?? 5;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if ($id) {
        // Obtener método de pago específico
        $resultado = $controller->obtenerPorId($id);

        if ($resultado['success']) {
            echo json_encode($resultado['metodo']);
        } else {
            http_response_code(404);
            echo json_encode($resultado);
        }
    } elseif ($inicializar) {
        // Inicializar métodos de pago por defecto
        $resultado = $controller->inicializarDefecto();
        echo json_encode($resultado);
    } elseif ($estadisticas) {
        // Obtener estadísticas de uso
        $resultado = $controller->obtenerEstadisticas($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($populares) {
        // Obtener métodos más populares
        $resultado = $controller->obtenerPopulares($limite);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(500);
            echo json_encode($resultado);
        }
    } elseif ($buscar) {
        // Buscar métodos por nombre
        $resultado = $controller->buscarPorNombre($buscar);

        if ($resultado['success']) {
            echo json_encode($resultado['resultados']);
        } else {
            http_response_code(400);
            echo json_encode($resultado);
        }
    } elseif ($disponible) {
        // Verificar disponibilidad de método específico
        $resultado = $controller->verificarDisponibilidad($disponible);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(404);
            echo json_encode($resultado);
        }
    } else {
        // Obtener todos los métodos de pago
        $resultado = $controller->obtenerTodos();

        if ($resultado['success']) {
            echo json_encode($resultado['metodos']);
        } else {
            http_response_code(500);
            echo json_encode($resultado);
        }
    }
}

/**
 * Manejar POST - Crear método de pago
 */
function handlePost($controller) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_cliente = $_GET['id_cliente'] ?? $data['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $data['llave_secreta'] ?? null;
    $nombre = $data['nombre'] ?? null;

    if (!$nombre) {
        http_response_code(400);
        echo json_encode(["error" => "Nombre del método de pago requerido"]);
        return;
    }

    $resultado = $controller->crear($nombre, $id_cliente, $llave_secreta);

    if ($resultado['success']) {
        http_response_code(201);
        echo json_encode($resultado);
    } else {
        if (strpos($resultado['error'], 'Credenciales') !== false) {
            $statusCode = 401;
        } elseif (strpos($resultado['error'], 'ya existe') !== false) {
            $statusCode = 409;
        } else {
            $statusCode = 400;
        }
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar PATCH - Actualizar método de pago
 */
function handlePatch($controller) {
    $id = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);
    $id_cliente = $_GET['id_cliente'] ?? $data['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $data['llave_secreta'] ?? null;
    $nombre = $data['nombre'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID del método de pago requerido"]);
        return;
    }

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    if (!$nombre) {
        http_response_code(400);
        echo json_encode(["error" => "Nombre del método de pago requerido"]);
        return;
    }

    $resultado = $controller->actualizar($id, $nombre, $id_cliente, $llave_secreta);

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        if (strpos($resultado['error'], 'Credenciales') !== false) {
            $statusCode = 401;
        } elseif (strpos($resultado['error'], 'ya existe') !== false) {
            $statusCode = 409;
        } elseif (strpos($resultado['error'], 'no encontrado') !== false) {
            $statusCode = 404;
        } else {
            $statusCode = 400;
        }
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar DELETE - Eliminar método de pago
 */
function handleDelete($controller) {
    $id = $_GET['id'] ?? null;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID del método de pago requerido"]);
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
        } elseif (strpos($resultado['error'], 'no encontrado') !== false) {
            $statusCode = 404;
        } elseif (strpos($resultado['error'], 'compras asociadas') !== false) {
            $statusCode = 409;
        } else {
            $statusCode = 400;
        }
        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}
?>