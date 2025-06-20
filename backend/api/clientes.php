<?php
// backend/api/clientes.php - API actualizada con controladores separados
session_start();
header('Content-Type: application/json');

// Incluir el controlador
require_once __DIR__ . '/../controladores/ClientesController.php';

// Crear instancia del controlador
$controller = new ClientesController();

// Obtener método y datos
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'POST') {
        // Determinar acción desde URL o input
        $accion = $_GET['accion'] ?? $input['accion'] ?? null;

        if ($accion === 'login') {
            handleLogin($controller, $input);
        } elseif ($accion === 'registro') {
            handleRegister($controller, $input);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no especificada'
            ]);
        }
    } elseif ($method === 'GET') {
        handleGet($controller);
    } elseif ($method === 'PATCH') {
        handleUpdate($controller, $input);
    } elseif ($method === 'DELETE') {
        handleDelete($controller);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}

/**
 * Manejar login
 */
function handleLogin($controller, $input) {
    if (!$input || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email es requerido']);
        return;
    }

    $resultado = $controller->login($input['email']);

    if ($resultado['success']) {
        // Crear sesión
        $_SESSION['id_cliente'] = $resultado['id_cliente'];
        $_SESSION['llave_secreta'] = $resultado['llave_secreta'];
        $_SESSION['email'] = $resultado['cliente']['email'];
        $_SESSION['nombre'] = $resultado['cliente']['nombre'];
        $_SESSION['apellido'] = $resultado['cliente']['apellido'];

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'id' => $resultado['cliente']['id'],
                'email' => $resultado['cliente']['email'],
                'nombre' => $resultado['cliente']['nombre'],
                'apellido' => $resultado['cliente']['apellido']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $resultado['error']
        ]);
    }
}

/**
 * Manejar registro
 */
function handleRegister($controller, $input) {
    if (!$input || !isset($input['email']) || !isset($input['nombre']) || !isset($input['apellido'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }

    $resultado = $controller->registrar($input['nombre'], $input['apellido'], $input['email']);

    if ($resultado['success']) {
        // Crear sesión automáticamente
        $_SESSION['id_cliente'] = $resultado['id_cliente'];
        $_SESSION['llave_secreta'] = $resultado['llave_secreta'];
        $_SESSION['email'] = $resultado['cliente']['email'];
        $_SESSION['nombre'] = $resultado['cliente']['nombre'];
        $_SESSION['apellido'] = $resultado['cliente']['apellido'];

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Registro exitoso',
            'data' => [
                'id' => $resultado['cliente']['id'] ?? null,
                'email' => $resultado['cliente']['email'],
                'nombre' => $resultado['cliente']['nombre'],
                'apellido' => $resultado['cliente']['apellido']
            ]
        ]);
    } else {
        $statusCode = (strpos($resultado['error'], 'ya está registrado') !== false) ? 409 : 400;
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $resultado['error']
        ]);
    }
}

/**
 * Manejar GET - Obtener clientes o perfil
 */
function handleGet($controller) {
    $id = $_GET['id'] ?? null;
    $perfil = $_GET['perfil'] ?? false;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if ($perfil && $id_cliente && $llave_secreta) {
        // Obtener perfil completo
        $resultado = $controller->obtenerPerfil($id_cliente, $llave_secreta);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(401);
            echo json_encode($resultado);
        }
    } elseif ($id) {
        // Obtener cliente específico
        $resultado = $controller->obtenerPorId($id);

        if ($resultado['success']) {
            echo json_encode($resultado);
        } else {
            http_response_code(404);
            echo json_encode($resultado);
        }
    } else {
        // Obtener todos los clientes
        $resultado = $controller->obtenerTodos();

        if ($resultado['success']) {
            echo json_encode($resultado['clientes']);
        } else {
            http_response_code(500);
            echo json_encode($resultado);
        }
    }
}

/**
 * Manejar actualización
 */
function handleUpdate($controller, $input) {
    $id = $_GET['id'] ?? null;
    $id_cliente = $_GET['id_cliente'] ?? $input['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? $input['llave_secreta'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        return;
    }

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Credenciales requeridas']);
        return;
    }

    // Validar que el usuario puede actualizar este perfil
    $validacion = $controller->validarCredenciales($id_cliente, $llave_secreta);
    if (!$validacion['success']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        return;
    }

    if ($validacion['cliente']['id'] != $id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No puedes actualizar este perfil']);
        return;
    }

    // Eliminar credenciales del input para actualización
    unset($input['id_cliente'], $input['llave_secreta']);

    $resultado = $controller->actualizar($id, $input);

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        http_response_code(400);
        echo json_encode($resultado);
    }
}

/**
 * Manejar eliminación
 */
function handleDelete($controller) {
    $id = $_GET['id'] ?? null;
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        return;
    }

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Credenciales requeridas']);
        return;
    }

    // Validar que el usuario puede eliminar este perfil
    $validacion = $controller->validarCredenciales($id_cliente, $llave_secreta);
    if (!$validacion['success']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        return;
    }

    if ($validacion['cliente']['id'] != $id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar este perfil']);
        return;
    }

    $resultado = $controller->eliminar($id);

    if ($resultado['success']) {
        // Destruir sesión si el usuario se elimina a sí mismo
        session_destroy();
        echo json_encode($resultado);
    } else {
        http_response_code(400);
        echo json_encode($resultado);
    }
}
?>