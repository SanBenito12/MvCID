<?php
// backend/api/metodos_pago_simple.php - API simplificada para métodos de pago
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../includes/db.php';

try {
    error_log("Métodos de pago - Iniciando consulta");

    $res = supabaseRequest("metodos_pago?select=*", "GET");

    error_log("Métodos de pago - Status: " . $res["status"]);
    error_log("Métodos de pago - Respuesta: " . $res["raw"]);

    if ($res["status"] === 200 && !empty($res["body"])) {
        echo json_encode($res["body"]);
    } else {
        // Si no hay métodos en la BD, devolver error para debug
        error_log("Métodos de pago - No encontrados en BD, usando fallback");

        $metodosDefecto = [
            ["id" => 1, "nombre" => "Tarjeta de Crédito"],
            ["id" => 2, "nombre" => "PayPal"],
            ["id" => 3, "nombre" => "Transferencia Bancaria"]
        ];
        echo json_encode($metodosDefecto);
    }
} catch (Exception $e) {
    error_log("Métodos de pago - Error: " . $e->getMessage());

    // Fallback con métodos por defecto
    $metodosDefecto = [
        ["id" => 1, "nombre" => "Tarjeta de Crédito"],
        ["id" => 2, "nombre" => "PayPal"],
        ["id" => 3, "nombre" => "Transferencia Bancaria"]
    ];
    echo json_encode($metodosDefecto);
}
?>