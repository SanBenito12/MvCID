<?php
// debug-dashboard.php - Script para debuggear problemas del dashboard

session_start();
header('Content-Type: application/json');

// Incluir dependencias
require_once __DIR__ . '/backend/modelos/Cliente.php';
require_once __DIR__ . '/backend/modelos/Curso.php';
require_once __DIR__ . '/backend/modelos/Compra.php';

$debug = [];

try {
    // 1. Verificar sesión
    $debug['sesion'] = [
        'existe' => isset($_SESSION['id_cliente']) && isset($_SESSION['llave_secreta']),
        'id_cliente' => $_SESSION['id_cliente'] ?? null,
        'llave_secreta' => $_SESSION['llave_secreta'] ?? null,
        'email' => $_SESSION['email'] ?? null
    ];

    if (!$debug['sesion']['existe']) {
        $debug['error'] = 'No hay sesión activa';
        echo json_encode($debug, JSON_PRETTY_PRINT);
        exit;
    }

    $id_cliente = $_SESSION['id_cliente'];
    $llave_secreta = $_SESSION['llave_secreta'];

    // 2. Probar validación de credenciales
    $debug['validacion_credenciales'] = Cliente::validarCredenciales($id_cliente, $llave_secreta);

    if (!$debug['validacion_credenciales']['success']) {
        $debug['error'] = 'Credenciales inválidas';
        echo json_encode($debug, JSON_PRETTY_PRINT);
        exit;
    }

    $cliente = $debug['validacion_credenciales']['cliente'];
    $id_creador = $cliente['id'];

    // 3. Probar obtener cursos
    try {
        $debug['cursos'] = Curso::obtenerPorCreador($id_creador);
    } catch (Exception $e) {
        $debug['error_cursos'] = $e->getMessage();
    }

    // 4. Probar obtener todos los cursos
    try {
        $debug['todos_cursos'] = Curso::obtenerTodos();
    } catch (Exception $e) {
        $debug['error_todos_cursos'] = $e->getMessage();
    }

    // 5. Probar obtener compras
    try {
        $debug['compras'] = Compra::obtenerPorCliente($id_creador);
    } catch (Exception $e) {
        $debug['error_compras'] = $e->getMessage();
    }

    // 6. Probar conexión directa a Supabase
    try {
        $respuesta = supabaseRequest("clientes?select=count", "GET");
        $debug['conexion_supabase'] = [
            'status' => $respuesta['status'],
            'conexion_ok' => $respuesta['status'] >= 200 && $respuesta['status'] < 300
        ];
    } catch (Exception $e) {
        $debug['error_supabase'] = $e->getMessage();
    }

    // 7. Información del cliente actual
    $debug['cliente_actual'] = [
        'id' => $cliente['id'],
        'nombre' => $cliente['nombre'],
        'email' => $cliente['email'],
        'id_cliente' => $cliente['id_cliente']
    ];

    echo json_encode($debug, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'error_general' => $e->getMessage(),
        'debug' => $debug
    ], JSON_PRETTY_PRINT);
}
?>