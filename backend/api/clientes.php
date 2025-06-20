<?php
// backend/api/clientes.php - Versión final funcionando
session_start();
header('Content-Type: application/json');

// Incluir la conexión a la base de datos usando ruta absoluta
require_once __DIR__ . '/../includes/db.php';

// Obtener método y datos
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'POST') {
        // Determinar acción desde URL o input
        $accion = $_GET['accion'] ?? $input['accion'] ?? null;

        if ($accion === 'login') {
            handleLogin($input);
        } elseif ($accion === 'registro') {
            handleRegister($input);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no especificada'
            ]);
        }
    } elseif ($method === 'GET') {
        getAllClients();
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

function handleLogin($input) {
    if (!$input || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email es requerido']);
        return;
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        return;
    }

    try {
        // Buscar el cliente en Supabase
        $endpoint = "clientes?email=eq." . urlencode($email) . "&select=*";
        $response = supabaseRequest($endpoint, "GET");

        if ($response['status'] !== 200) {
            throw new Exception('Error al consultar la base de datos');
        }

        $clientes = $response['body'];

        if (empty($clientes)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Email no registrado']);
            return;
        }

        $cliente = $clientes[0];

        // Crear sesión
        $_SESSION['id_cliente'] = $cliente['id_cliente'] ?? $cliente['id'];
        $_SESSION['llave_secreta'] = $cliente['llave_secreta'] ?? 'temp_' . $cliente['id'];
        $_SESSION['email'] = $cliente['email'];
        $_SESSION['nombre'] = $cliente['nombre'];
        $_SESSION['apellido'] = $cliente['apellido'];

        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'id' => $cliente['id'],
                'email' => $cliente['email'],
                'nombre' => $cliente['nombre'],
                'apellido' => $cliente['apellido']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ]);
    }
}

function handleRegister($input) {
    if (!$input || !isset($input['email']) || !isset($input['nombre']) || !isset($input['apellido'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    $nombre = trim($input['nombre']);
    $apellido = trim($input['apellido']);

    if (!$email || empty($nombre) || empty($apellido)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }

    try {
        // Verificar si el email ya existe
        $checkEndpoint = "clientes?email=eq." . urlencode($email) . "&select=id";
        $checkResponse = supabaseRequest($checkEndpoint, "GET");

        if ($checkResponse['status'] !== 200) {
            throw new Exception('Error al verificar email');
        }

        if (!empty($checkResponse['body'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
            return;
        }

        // Generar claves para compatibilidad
        $id_cliente = bin2hex(random_bytes(16));
        $llave_secreta = bin2hex(random_bytes(16));

        // Crear nuevo cliente
        $newCliente = [
            'email' => $email,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'id_cliente' => $id_cliente,
            'llave_secreta' => $llave_secreta,
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];

        $response = supabaseRequest("clientes", "POST", $newCliente);

        if ($response['status'] !== 201) {
            throw new Exception('Error al crear cliente');
        }

        $cliente = $response['body'][0] ?? $response['body'];

        // Crear sesión automáticamente
        $_SESSION['id_cliente'] = $cliente['id_cliente'];
        $_SESSION['llave_secreta'] = $cliente['llave_secreta'];
        $_SESSION['email'] = $cliente['email'];
        $_SESSION['nombre'] = $cliente['nombre'];
        $_SESSION['apellido'] = $cliente['apellido'];

        echo json_encode([
            'success' => true,
            'message' => 'Registro exitoso',
            'data' => [
                'id' => $cliente['id'] ?? null,
                'email' => $cliente['email'],
                'nombre' => $cliente['nombre'],
                'apellido' => $cliente['apellido']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ]);
    }
}

function getAllClients() {
    try {
        $response = supabaseRequest("clientes?select=id,email,nombre,apellido,created_at");
        echo json_encode($response['body']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>