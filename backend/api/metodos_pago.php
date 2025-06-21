<?php
// backend/api/metodos_pago.php - Versión simplificada
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../includes/db.php';

try {
    // Solo manejar GET para obtener métodos
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        exit;
    }

    // Intentar obtener métodos de Supabase
    $res = supabaseRequest("metodos_pago?select=*", "GET");

    if ($res["status"] === 200 && !empty($res["body"]) && is_array($res["body"])) {
        // Éxito: devolver métodos de la base de datos
        echo json_encode($res["body"]);
    } else {
        // Fallback: métodos por defecto
        $metodosDefecto = [
            ["id" => 1, "nombre" => "Tarjeta de Crédito"],
            ["id" => 2, "nombre" => "PayPal"],
            ["id" => 3, "nombre" => "OXXO / Efectivo"],
            ["id" => 4, "nombre" => "Transferencia Bancaria"],
            ["id" => 5, "nombre" => "Criptomonedas"]
        ];
        echo json_encode($metodosDefecto);
    }
} catch (Exception $e) {
    // Error: usar métodos por defecto
    error_log("Error en métodos de pago: " . $e->getMessage());

    $metodosDefecto = [
        ["id" => 1, "nombre" => "Tarjeta de Crédito"],
        ["id" => 2, "nombre" => "PayPal"],
        ["id" => 3, "nombre" => "OXXO / Efectivo"],
        ["id" => 4, "nombre" => "Transferencia Bancaria"]
    ];
    echo json_encode($metodosDefecto);
}
?>