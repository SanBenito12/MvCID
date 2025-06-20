<?php
// backend/api/compras.php - API actualizada con controladores separados
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en respuesta JSON

// Incluir el controlador
require_once __DIR__ . '/../controladores/ComprasController.php';

// Crear instancia del controlador
$controller = new ComprasController();

$method = $_SERVER['REQUEST_METHOD'];

try {
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
 * Manejar GET - Obtener compras
 */
function handleGet($controller) {
    $id = $_GET['id'] ?? null;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $id_curso = $_GET['id_curso'] ?? null;
    $estadisticas = $_GET['estadisticas'] ?? false;
    $ingresos = $_GET['ingresos'] ?? false;
    $fecha_inicio = $_GET['fecha_inicio'] ?? null;
    $fecha_fin = $_GET['fecha_fin'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;
    $puede_comprar = $_GET['puede_comprar'] ?? null;

    if (!$llave_secreta && ($id_cliente || $id_curso || $estadisticas || $ingresos)) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    if ($id && $id_cliente && $llave_secreta) {
        // Obtener compra específica
        $resultado = $controller->obtenerPorId($id, $id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado['compra']);
        } else {
            // Determinar código de error
            $statusCode = 404; // Default
            if (strpos($resultado['error'], 'Credenciales') !== false) {
                $statusCode = 401;
            } elseif (strpos($resultado['error'], 'permisos') !== false) {
                $statusCode = 403;
            }

            http_response_code($statusCode);
            echo json_encode($resultado);
        }
    } elseif ($puede_comprar && $id_cliente && $llave_secreta) {
        // Verificar si puede comprar un curso
        $resultado = $controller->puedeComprar($puede_comprar, $id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($estadisticas && $id_cliente && $llave_secreta) {
        // Obtener estadísticas de compras del cliente
        $resultado = $controller->obtenerEstadisticas($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($ingresos && $id_cliente && $llave_secreta) {
        // Obtener ingresos del creador
        $resultado = $controller->obtenerIngresos($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($fecha_inicio && $fecha_fin && $id_cliente && $llave_secreta) {
        // Obtener compras por rango de fechas
        $resultado = $controller->obtenerPorRangoFechas($fecha_inicio, $fecha_fin, $id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($id_curso && $id_cliente && $llave_secreta) {
        // Obtener compras de un curso específico
        $resultado = $controller->obtenerComprasPorCurso($id_curso, $id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado['compras']);
        } else {
            // Determinar código de error
            $statusCode = 403; // Default
            if (strpos($resultado['error'], 'Credenciales') !== false) {
                $statusCode = 401;
            }

            http_response_code($statusCode);
            echo json_encode($resultado);
        }
    } elseif ($id_cliente && $llave_secreta) {
        // Obtener compras de un cliente
        $resultado = $controller->obtenerComprasPorCliente($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado['compras']);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } else {
        // Obtener todas las compras (requiere autenticación)
        if (!$id_cliente || !$llave_secreta) {
            http_response_code(400);
            echo json_encode(["error" => "Credenciales requeridas para ver todas las compras"]);
            return;
        }

        $resultado = $controller->obtenerTodas();

        if ($resultado['success']) {
            echo json_encode($resultado['compras']);
        } else {
            http_response_code(500);
            echo json_encode($resultado);
        }
    }
}

/**
 * Manejar POST - Crear compra
 */
function handlePost($controller) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_cliente = $_GET['id_cliente'] ?? $data['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $data['llave_secreta'] ?? null;
    $reembolso = $_GET['reembolso'] ?? $data['reembolso'] ?? false;

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    // Remover credenciales de los datos
    unset($data['id_cliente'], $data['llave_secreta'], $data['reembolso']);

    if ($reembolso) {
        // Procesar reembolso
        $id_compra = $data['id_compra'] ?? null;
        $motivo = $data['motivo'] ?? 'Solicitud del cliente';

        if (!$id_compra) {
            http_response_code(400);
            echo json_encode(["error" => "ID de compra requerido para reembolso"]);
            return;
        }

        $resultado = $controller->procesarReembolso($id_compra, $motivo, $id_cliente, $llave_secreta);
    } else {
        // Realizar nueva compra
        $resultado = $controller->realizarCompra($data, $id_cliente, $llave_secreta);
    }

    if ($resultado['success']) {
        $statusCode = $reembolso ? 200 : 201;
        http_response_code($statusCode);
        echo json_encode($resultado);
    } else {
        // Determinar código de error
        $statusCode = 400; // Default
        if (strpos($resultado['error'], 'Credenciales') !== false) {
            $statusCode = 401;
        }

        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}

/**
 * Manejar DELETE - Cancelar compra
 */
function handleDelete($controller) {
    $id = $_GET['id'] ?? null;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID de compra requerido"]);
        return;
    }

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(["error" => "Credenciales requeridas"]);
        return;
    }

    $resultado = $controller->cancelarCompra($id, $id_cliente, $llave_secreta);

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        // Determinar código de error
        $statusCode = 400; // Default
        if (strpos($resultado['error'], 'Credenciales') !== false) {
            $statusCode = 401;
        } elseif (strpos($resultado['error'], 'no es tuya') !== false) {
            $statusCode = 403;
        }

        http_response_code($statusCode);
        echo json_encode($resultado);
    }
}
?>