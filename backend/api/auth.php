<?php
// backend/api/auth.php - API de autenticación corregida
header("Content-Type: application/json");

// Incluir modelos necesarios
require_once __DIR__ . '/../modelos/Cliente.php';

try {
    $id_cliente = $_GET['id_cliente'] ?? null;
    $llave_secreta = $_GET['llave_secreta'] ?? null;

    // Log para debug
    error_log("Auth API - Recibido id_cliente: $id_cliente, llave_secreta: $llave_secreta");

    if (!$id_cliente || !$llave_secreta) {
        http_response_code(400);
        echo json_encode([
            "error" => "Faltan datos de autenticación",
            "valido" => false
        ]);
        exit;
    }

    // Usar el modelo Cliente para validar credenciales
    $resultado = Cliente::validarCredenciales($id_cliente, $llave_secreta);

    error_log("Auth API - Resultado validación: " . json_encode($resultado));

    if ($resultado["success"]) {
        $cliente = $resultado["cliente"];

        echo json_encode([
            "valido" => true,
            "id_creador" => $cliente["id"],
            "cliente" => [
                "id" => $cliente["id"],
                "nombre" => $cliente["nombre"],
                "apellido" => $cliente["apellido"],
                "email" => $cliente["email"]
            ]
        ]);
    } else {
        echo json_encode([
            "valido" => false,
            "error" => $resultado["error"]
        ]);
    }

} catch (Exception $e) {
    error_log("Auth API - Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "valido" => false,
        "error" => "Error interno del servidor: " . $e->getMessage()
    ]);
}
?>