<?php
// test_videos_direct.php - Prueba directa
session_start();

// Simular usuario logueado
$_SESSION['cliente_id'] = 1;
$_SESSION['cliente_nombre'] = 'Usuario Test';

echo "<!DOCTYPE html><html><head><title>Test Videos</title></head><body>";
echo "<h1>🧪 TEST DIRECTO DE VIDEOS</h1>";
echo "<p>Cliente ID: " . ($_SESSION['cliente_id'] ?? 'NO SET') . "</p>";
echo "<p>Cliente Nombre: " . ($_SESSION['cliente_nombre'] ?? 'NO SET') . "</p>";

echo "<h2>Probando carga de videos.php:</h2>";

if (file_exists('frontend/videos.php')) {
    echo "<p style='color: green;'>✅ frontend/videos.php existe</p>";
    echo "<hr>";
    
    // Incluir la página de videos
    try {
        include 'frontend/videos.php';
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ frontend/videos.php NO existe</p>";
}

echo "</body></html>";
?>