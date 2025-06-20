<?php
// start-server.php - Script para iniciar el servidor de desarrollo

echo "=== MVC SISTEMA - Servidor de Desarrollo ===\n";
echo "Iniciando servidor en http://localhost:8000\n";
echo "Rutas disponibles:\n";
echo "  • http://localhost:8000/ (redirige a login)\n";
echo "  • http://localhost:8000/login\n";
echo "  • http://localhost:8000/registro\n";
echo "  • http://localhost:8000/dashboard\n";
echo "  • http://localhost:8000/logout\n";
echo "\nAPIs disponibles:\n";
echo "  • POST /api/clientes/login\n";
echo "  • POST /api/clientes/registro\n";
echo "  • GET|POST|PATCH|DELETE /api/cursos\n";
echo "  • GET|POST /api/compras\n";
echo "  • GET /api/auth\n";
echo "\nPresiona Ctrl+C para detener el servidor\n";
echo "=========================================\n\n";

// Verificar que estemos en el directorio correcto
if (!file_exists('index.php')) {
    echo "ERROR: No se encuentra index.php en el directorio actual.\n";
    echo "Asegúrate de ejecutar este script desde la raíz del proyecto.\n";
    exit(1);
}

// Verificar estructura de directorios
$requiredDirs = ['frontend', 'backend', 'assets'];
foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        echo "ERROR: No se encuentra el directorio '$dir'\n";
        exit(1);
    }
}

// Verificar archivos críticos
$requiredFiles = [
    'frontend/login.php',
    'frontend/registro.php',
    'frontend/dashboard.php',
    'backend/includes/db.php',
    'backend/api/clientes.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        echo "ERROR: No se encuentra el archivo '$file'\n";
        exit(1);
    }
}

echo "✅ Estructura de proyecto verificada\n";
echo "🚀 Iniciando servidor...\n\n";

// Iniciar servidor de desarrollo
passthru('php -S localhost:8000 index.php');
?>