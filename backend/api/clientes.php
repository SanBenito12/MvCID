<?php
session_start();
header('Content-Type: application/json');

// Incluir la conexión a la base de datos
require_once '../includes/db.php';

// Obtener el método HTTP y la ruta
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];

// Función para extraer la acción de la URL
function getAction($path) {
    $parts = explode('/', trim($path, '/'));
    return end($parts); // Obtiene la última parte de la URL
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['accion'] ?? null;

try {
    switch ($method) {
        case 'POST':
            if ($action === 'login') {
                handleLogin($input);
            } elseif ($action === 'register') {
                handleRegister();
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint no encontrado']);
            }
            break;
            
        case 'GET':
            if ($action === 'profile') {
                getProfile();
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint no encontrado']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
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
        $response = supabaseRequest("clientes?email=eq.$email&select=*");
        
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
        $_SESSION['id_cliente'] = $cliente['id'];
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
            'message' => 'Error en el servidor',
            'error' => $e->getMessage()
        ]);
    }
}

function handleRegister() {
    $input = json_decode(file_get_contents('php://input'), true);
    
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
        $checkResponse = supabaseRequest("clientes?email=eq.$email&select=id");
        
        if ($checkResponse['status'] !== 200) {
            throw new Exception('Error al verificar email');
        }
        
        if (!empty($checkResponse['body'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
            return;
        }
        
        // Crear nuevo cliente
        $newCliente = [
            'email' => $email,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'fecha_registro' => date('Y-m-d H:i:s')
        ];
        
        $response = supabaseRequest("clientes", "POST", $newCliente);
        
        if ($response['status'] !== 201) {
            throw new Exception('Error al crear cliente');
        }
        
        $cliente = $response['body'][0];
        
        // Crear sesión automáticamente después del registro
        $_SESSION['id_cliente'] = $cliente['id'];
        $_SESSION['email'] = $cliente['email'];
        $_SESSION['nombre'] = $cliente['nombre'];
        $_SESSION['apellido'] = $cliente['apellido'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Registro exitoso',
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
            'message' => 'Error en el servidor',
            'error' => $e->getMessage()
        ]);
    }
}

function getProfile() {
    if (!isset($_SESSION['id_cliente'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $_SESSION['id_cliente'],
            'email' => $_SESSION['email'],
            'nombre' => $_SESSION['nombre'],
            'apellido' => $_SESSION['apellido']
        ]
    ]);
}
?>