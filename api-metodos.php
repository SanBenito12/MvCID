<?php
// api-metodos.php - API simple para métodos de pago en la raíz
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/backend/includes/db.php';

try {
    $res = supabaseRequest("metodos_pago?select=*", "GET");

    if ($res["status"] === 200 && !empty($res["body"])) {
        echo json_encode($res["body"]);
    } else {
        // Fallback con métodos por defecto
        $metodosDefecto = [
            ["id" => 1, "nombre" => "Tarjeta de Crédito"],
            ["id" => 2, "nombre" => "PayPal"],
            ["id" => 3, "nombre" => "OXXO / Efectivo"]
        ];
        echo json_encode($metodosDefecto);
    }
} catch (Exception $e) {
    // Fallback con métodos por defecto
    $metodosDefecto = [
        ["id" => 1, "nombre" => "Tarjeta de Crédito"],
        ["id" => 2, "nombre" => "PayPal"],
        ["id" => 3, "nombre" => "OXXO / Efectivo"]
    ];
    echo json_encode($metodosDefecto);
}
?>