<?php
// test-metodos.php - Script para probar métodos de pago directamente
header("Content-Type: application/json");

require_once __DIR__ . '/backend/includes/db.php';

echo json_encode([
    "test" => "Prueba de métodos de pago",
    "timestamp" => date('Y-m-d H:i:s')
]);

try {
    echo "\n\n=== PRUEBA DE CONEXIÓN ===\n";

    // Probar conexión básica
    $testConnection = supabaseRequest("metodos_pago?select=count", "GET");
    echo "Status conexión: " . $testConnection["status"] . "\n";
    echo "Respuesta: " . $testConnection["raw"] . "\n";

    echo "\n=== PRUEBA DE OBTENER MÉTODOS ===\n";

    // Probar obtener métodos
    $metodos = supabaseRequest("metodos_pago?select=*", "GET");
    echo "Status métodos: " . $metodos["status"] . "\n";
    echo "Datos: " . json_encode($metodos["body"], JSON_PRETTY_PRINT) . "\n";

    if ($metodos["status"] === 200 && !empty($metodos["body"])) {
        echo "\n✅ MÉTODOS ENCONTRADOS: " . count($metodos["body"]) . "\n";
        foreach ($metodos["body"] as $metodo) {
            echo "- ID: " . $metodo["id"] . " | Nombre: " . $metodo["nombre"] . "\n";
        }
    } else {
        echo "\n❌ NO SE ENCONTRARON MÉTODOS\n";
    }

} catch (Exception $e) {
    echo "\n💥 ERROR: " . $e->getMessage() . "\n";
}
?>