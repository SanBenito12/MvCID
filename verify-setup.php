<?php
// verify-setup.php - Verificador de configuración del proyecto

echo "=== VERIFICADOR DE CONFIGURACIÓN MVC SISTEMA ===\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar PHP y extensiones
echo "1. Verificando PHP y extensiones...\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    $success[] = "✅ PHP " . PHP_VERSION . " (Compatible)";
} else {
    $errors[] = "❌ PHP " . PHP_VERSION . " (Se requiere 7.4+)";
}

$requiredExtensions = ['curl', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "✅ Extensión '$ext' habilitada";
    } else {
        $errors[] = "❌ Extensión '$ext' faltante";
    }
}

// 2. Verificar estructura de directorios
echo "\n2. Verificando estructura de directorios...\n";
$requiredDirs = [
    'frontend' => 'Frontend (vistas)',
    'backend' => 'Backend (lógica)',
    'backend/includes' => 'Includes del backend',
    'backend/api' => 'APIs REST',
    'backend/controladores' => 'Controladores MVC',
    'assets' => 'Recursos estáticos',
    'assets/css' => 'Estilos CSS'
];

foreach ($requiredDirs as $dir => $desc) {
    if (is_dir($dir)) {
        $success[] = "✅ Directorio '$dir' existe ($desc)";
    } else {
        $errors[] = "❌ Directorio '$dir' faltante ($desc)";
    }
}

// 3. Verificar archivos críticos
echo "\n3. Verificando archivos críticos...\n";
$requiredFiles = [
    'index.php' => 'Router principal',
    'frontend/login.php' => 'Página de login',
    'frontend/registro.php' => 'Página de registro',
    'frontend/dashboard.php' => 'Dashboard principal',
    'frontend/logout.php' => 'Logout',
    'backend/includes/db.php' => 'Conexión a base de datos',
    'backend/api/clientes.php' => 'API de clientes',
    'backend/api/cursos.php' => 'API de cursos',
    'backend/api/compras.php' => 'API de compras',
    'backend/api/auth.php' => 'API de autenticación',
    'assets/css/dashboard.css' => 'Estilos del dashboard',
    'assets/css/estiloslog.css' => 'Estilos del login',
    'assets/css/estilosreg.css' => 'Estilos del registro'
];

foreach ($requiredFiles as $file => $desc) {
    if (file_exists($file)) {
        $success[] = "✅ Archivo '$file' existe ($desc)";
    } else {
        $errors[] = "❌ Archivo '$file' faltante ($desc)";
    }
}

// 4. Verificar configuración de Supabase
echo "\n4. Verificando configuración de Supabase...\n";
if (file_exists('backend/includes/db.php')) {
    $dbContent = file_get_contents('backend/includes/db.php');

    if (strpos($dbContent, 'define("SUPABASE_URL"') !== false) {
        $success[] = "✅ SUPABASE_URL definida";
    } else {
        $errors[] = "❌ SUPABASE_URL no definida";
    }

    if (strpos($dbContent, 'define("SUPABASE_KEY"') !== false) {
        $success[] = "✅ SUPABASE_KEY definida";
    } else {
        $errors[] = "❌ SUPABASE_KEY no definida";
    }

    if (strpos($dbContent, 'define(\'SUPABASE_SERVICE_ROLE_KEY\'') !== false) {
        $success[] = "✅ SUPABASE_SERVICE_ROLE_KEY definida";
    } else {
        $warnings[] = "⚠️ SUPABASE_SERVICE_ROLE_KEY no definida (necesaria para subir imágenes)";
    }

    if (function_exists('curl_init')) {
        $success[] = "✅ cURL disponible para conexiones a Supabase";
    } else {
        $errors[] = "❌ cURL no disponible (necesario para Supabase)";
    }
}

// 5. Verificar permisos de escritura
echo "\n5. Verificando permisos...\n";
if (is_writable('.')) {
    $success[] = "✅ Directorio raíz escribible";
} else {
    $warnings[] = "⚠️ Directorio raíz no escribible (puede afectar logs)";
}

// 6. Probar función de conexión a Supabase
echo "\n6. Probando conexión a Supabase...\n";
if (file_exists('backend/includes/db.php')) {
    try {
        require_once 'backend/includes/db.php';
        if (function_exists('supabaseRequest')) {
            $success[] = "✅ Función supabaseRequest disponible";

            // Intentar una petición de prueba
            try {
                $response = supabaseRequest('clientes?select=count', 'GET');
                if ($response['status'] >= 200 && $response['status'] < 300) {
                    $success[] = "✅ Conexión a Supabase exitosa";
                } else {
                    $warnings[] = "⚠️ Conexión a Supabase con status: " . $response['status'];
                }
            } catch (Exception $e) {
                $warnings[] = "⚠️ Error al conectar con Supabase: " . $e->getMessage();
            }
        } else {
            $errors[] = "❌ Función supabaseRequest no definida";
        }
    } catch (Exception $e) {
        $errors[] = "❌ Error al cargar db.php: " . $e->getMessage();
    }
}

// Mostrar resultados
echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMEN DE VERIFICACIÓN\n";
echo str_repeat("=", 50) . "\n";

if (!empty($success)) {
    echo "\n✅ CONFIGURACIONES CORRECTAS (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "   $item\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️ ADVERTENCIAS (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "   $item\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERRORES CRÍTICOS (" . count($errors) . "):\n";
    foreach ($errors as $item) {
        echo "   $item\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";

if (empty($errors)) {
    echo "🎉 ¡CONFIGURACIÓN LISTA!\n";
    echo "Puedes iniciar el servidor con:\n";
    echo "   php start-server.php\n";
    echo "   o\n";
    echo "   php -S localhost:8000 index.php\n\n";
    echo "Luego visita: http://localhost:8000\n";
} else {
    echo "🚨 CONFIGURACIÓN INCOMPLETA\n";
    echo "Corrige los errores críticos antes de continuar.\n\n";
    echo "Ayuda:\n";
    echo "- Verifica que todos los archivos estén en su lugar\n";
    echo "- Configura las credenciales de Supabase en backend/includes/db.php\n";
    echo "- Asegúrate de tener PHP 7.4+ con extensiones curl, json, mbstring\n";
}

echo str_repeat("=", 50) . "\n";
?>