<?php
// backend/controladores/RouterController.php - Controlador principal de rutas

class RouterController
{
    private $uri;
    private $method;
    private $projectRoot;

    public function __construct()
    {
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->projectRoot = dirname(dirname(__DIR__));

        // Log para debug
        error_log("RouterController: URI = {$this->uri}, Method = {$this->method}");
    }

    /**
     * Manejar todas las rutas del sistema
     */
    public function handleRequest()
    {
        // Servir archivos estáticos primero
        if ($this->handleStaticFiles()) {
            return;
        }

        // Rutas del frontend
        if ($this->handleFrontendRoutes()) {
            return;
        }

        // Rutas de API
        if ($this->handleApiRoutes()) {
            return;
        }

        // Rutas específicas del backend
        if ($this->handleBackendRoutes()) {
            return;
        }

        // 404 - Página no encontrada
        $this->handle404();
    }

    /**
     * Manejar archivos estáticos (CSS, JS, imágenes, fuentes)
     */
    private function handleStaticFiles()
    {
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|otf|eot)$/i', $this->uri)) {
            $filePath = $this->projectRoot . $this->uri;

            if (file_exists($filePath)) {
                $this->serveStaticFile($filePath);
                return true;
            }

            http_response_code(404);
            echo "Archivo no encontrado: {$this->uri}";
            return true;
        }

        return false;
    }

    /**
     * Servir archivo estático con headers apropiados
     */
    private function serveStaticFile($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject'
        ];

        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);

            // Cache headers para archivos estáticos
            header('Cache-Control: public, max-age=31536000'); // 1 año
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }

        readfile($filePath);
    }

    /**
     * Manejar rutas del frontend
     */
    private function handleFrontendRoutes()
    {
        switch ($this->uri) {
            case '/':
            case '/index.php':
                $this->redirectToLogin();
                return true;

            case '/login':
            case '/login.php':
                $this->serveFrontendPage('login.php');
                return true;

            case '/registro':
            case '/registro.php':
                $this->serveFrontendPage('registro.php');
                return true;

            case '/dashboard':
            case '/dashboard.php':
                $this->serveFrontendPage('dashboard.php');
                return true;

            case '/carrito':
            case '/carrito.php':
                $this->serveFrontendPage('carrito.php');
                return true;

            case '/cambiar-password':
            case '/cambiar-password.php':
            case '/change-password':
            case '/password':
                $this->serveFrontendPage('cambiar-password.php');
                return true;
            
            case '/videos':
            case '/videos.php':
            case '/youtube':
                $this->serveFrontendPage('videos.php');
                return true;

            case '/perfil':
            case '/perfil.php':
            case '/profile':
                $this->serveFrontendPage('perfil.php');
                return true;

            case '/configuracion':
            case '/configuracion.php':
            case '/config':
            case '/settings':
                $this->serveFrontendPage('configuracion.php');
                return true;

            case '/logout':
            case '/logout.php':
                $this->serveFrontendPage('logout.php');
                return true;

            // Rutas de debug/testing
            case '/debug-dashboard':
            case '/debug-dashboard.php':
                $this->serveDebugPage('debug-dashboard.php');
                return true;

            case '/test-metodos':
            case '/test-metodos.php':
                $this->serveDebugPage('test-metodos.php');
                return true;

            case '/test-password':
            case '/test-password.php':
                $this->serveDebugPage('test-password.php');
                return true;

            // Compatibilidad hacia atrás para api-metodos
            case '/api-metodos':
            case '/api-metodos.php':
                require_once $this->projectRoot . '/backend/api/metodos_pago.php';
                return true;

            // Rutas de ayuda y documentación
            case '/help':
            case '/ayuda':
                $this->serveFrontendPage('ayuda.php');
                return true;

            case '/about':
            case '/acerca':
                $this->serveFrontendPage('acerca.php');
                return true;
        }

        return false;
    }

    /**
     * Redirigir a login o dashboard según sesión
     */
    private function redirectToLogin()
    {
        session_start();
        if (isset($_SESSION['cliente_id'])) {
            header("Location: /dashboard");
        } else {
            header("Location: /login");
        }
        exit;
    }

    /**
     * Servir página del frontend - SIN VERIFICACIÓN DE SESIÓN AQUÍ
     */
    private function serveFrontendPage($page)
    {
        $filePath = $this->projectRoot . '/frontend/' . $page;

        if (file_exists($filePath)) {
            error_log("✅ Sirviendo página: $page");
            require_once $filePath;
        } else {
            error_log("❌ Frontend page not found: $filePath");
            
            // Si es una página que requiere autenticación, crear una página genérica
            if (in_array($page, ['cambiar-password.php', 'perfil.php', 'configuracion.php'])) {
                $this->createGenericAuthPage($page);
            } else {
                $this->handle404();
            }
        }
    }

    /**
     * Crear página genérica para páginas que requieren autenticación
     */
    private function createGenericAuthPage($page)
    {
        session_start();
        if (!isset($_SESSION['cliente_id'])) {
            header("Location: /login");
            exit;
        }

        $pageTitle = '';
        $pageIcon = '';
        $pageDescription = '';

        switch ($page) {
            case 'cambiar-password.php':
                $pageTitle = 'Cambiar Contraseña';
                $pageIcon = 'fas fa-key';
                $pageDescription = 'Esta página está en construcción. Puedes cambiar tu contraseña desde el dashboard.';
                break;
            case 'perfil.php':
                $pageTitle = 'Mi Perfil';
                $pageIcon = 'fas fa-user';
                $pageDescription = 'Esta página está en construcción. Tu información de perfil se muestra en el dashboard.';
                break;
            case 'configuracion.php':
                $pageTitle = 'Configuración';
                $pageIcon = 'fas fa-cog';
                $pageDescription = 'Esta página están construcción. Las opciones de configuración están disponibles en el dashboard.';
                break;
        }

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $pageTitle ?> - MVC SISTEMA</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <link href="/assets/css/dashboard.css" rel="stylesheet">
        </head>
        <body>
        <div class="container">
            <div class="header">
                <h1>
                    <i class="<?= $pageIcon ?>"></i>
                    <?= $pageTitle ?>
                </h1>
                <div class="user-info">
                    <a href="/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>

            <div class="card-bg" style="padding: var(--spacing-2xl); text-align: center; border-radius: var(--radius-xl); border: 1px solid var(--border-color);">
                <i class="<?= $pageIcon ?>" style="font-size: 4rem; color: var(--accent-blue); margin-bottom: var(--spacing-lg);"></i>
                <h2 style="color: var(--text-primary); margin-bottom: var(--spacing-md);"><?= $pageTitle ?></h2>
                <p style="color: var(--text-secondary); font-size: 1.1rem; line-height: 1.6;"><?= $pageDescription ?></p>
                
                <div style="margin-top: var(--spacing-2xl);">
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Ir al Dashboard
                    </a>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
    }

    /**
     * Servir página de debug
     */
    private function serveDebugPage($page)
    {
        $filePath = $this->projectRoot . '/' . $page;

        if (file_exists($filePath)) {
            require_once $filePath;
        } else {
            error_log("Debug page not found: $filePath");
            $this->handle404();
        }
    }

    /**
     * Manejar rutas de API
     */
    private function handleApiRoutes()
    {
        if (strpos($this->uri, '/api/') !== 0) {
            return false;
        }

        // Configurar headers para API
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Manejar preflight requests
        if ($this->method === 'OPTIONS') {
            http_response_code(200);
            return true;
        }

        try {
            // Rutas específicas de clientes
            if ($this->uri === '/api/clientes/login' && $this->method === 'POST') {
                $_GET['accion'] = 'login';
                require_once $this->projectRoot . '/backend/api/clientes.php';
                return true;
            }

            if ($this->uri === '/api/clientes/login-legacy' && $this->method === 'POST') {
                $_GET['accion'] = 'login-legacy';
                require_once $this->projectRoot . '/backend/api/clientes.php';
                return true;
            }

            if ($this->uri === '/api/clientes/registro' && $this->method === 'POST') {
                $_GET['accion'] = 'registro';
                require_once $this->projectRoot . '/backend/api/clientes.php';
                return true;
            }

            if ($this->uri === '/api/clientes/cambiar-password' && $this->method === 'POST') {
                $_GET['accion'] = 'cambiar-password';
                require_once $this->projectRoot . '/backend/api/clientes.php';
                return true;
            }

            if (preg_match('#^/api/clientes/?$#', $this->uri)) {
                require_once $this->projectRoot . '/backend/api/clientes.php';
                return true;
            }

            // Rutas de cursos
            if (preg_match('#^/api/cursos/?$#', $this->uri)) {
                require_once $this->projectRoot . '/backend/api/cursos.php';
                return true;
            }

            // Rutas de compras
            if (preg_match('#^/api/compras/?$#', $this->uri)) {
                require_once $this->projectRoot . '/backend/api/compras.php';
                return true;
            }

            // Rutas de métodos de pago
            if (preg_match('#^/api/metodos-pago/?$#', $this->uri) ||
                preg_match('#^/api/metodos_pago/?$#', $this->uri) ||
                $this->uri === '/api-metodos' ||
                $this->uri === '/api-metodos.php') {
                require_once $this->projectRoot . '/backend/api/metodos_pago.php';
                return true;
            }

            // Rutas de carrito
            if (preg_match('#^/api/carrito/?$#', $this->uri)) {
                require_once $this->projectRoot . '/backend/api/carrito.php';
                return true;
            }

            // Ruta de autenticación
            if (preg_match('#^/api/auth/?$#', $this->uri)) {
                require_once $this->projectRoot . '/backend/api/auth.php';
                return true;
            }

            // Rutas de videos de YouTube - NUEVA FUNCIONALIDAD
            if (preg_match('#^/api/videos/?$#', $this->uri)) {
                require_once $this->projectRoot . '/backend/api/videos.php';
                return true;
            }

            // Rutas de utilidades
            if ($this->uri === '/api/test-connection' && $this->method === 'GET') {
                echo json_encode([
                    "success" => true,
                    "message" => "Conexión API funcionando correctamente",
                    "timestamp" => date('Y-m-d H:i:s'),
                    "server_info" => [
                        "php_version" => PHP_VERSION,
                        "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
                    ]
                ]);
                return true;
            }

            // API no encontrada
            $this->handleApiNotFound();
            return true;

        } catch (Exception $e) {
            $this->handleApiError($e);
            return true;
        }
    }

    /**
     * Manejar rutas específicas del backend
     */
    private function handleBackendRoutes()
    {
        if (strpos($this->uri, '/backend/') !== 0) {
            return false;
        }

        // Rutas permitidas del backend
        $allowedBackendRoutes = [
            '/backend/api/metodos_pago.php' => '/backend/api/metodos_pago.php',
            '/backend/api/carrito.php' => '/backend/api/carrito.php',
            '/backend/api/clientes.php' => '/backend/api/clientes.php',
            '/backend/api/videos.php' => '/backend/api/videos.php'
        ];

        if (isset($allowedBackendRoutes[$this->uri])) {
            require_once $this->projectRoot . $allowedBackendRoutes[$this->uri];
            return true;
        }

        // Bloquear acceso directo a otros archivos del backend
        http_response_code(403);
        echo json_encode(["error" => "Acceso denegado"]);
        return true;
    }

    /**
     * Manejar API no encontrada
     */
    private function handleApiNotFound()
    {
        http_response_code(404);
        echo json_encode([
            "error" => "API endpoint no encontrado",
            "uri" => $this->uri,
            "method" => $this->method,
            "available_endpoints" => [
                "POST /api/clientes/login",
                "POST /api/clientes/registro",
                "GET /api/cursos",
                "GET /api/compras",
                "GET /api/metodos-pago",
                "GET /api/carrito",
                "GET /api/videos",
                "GET /api/auth"
            ]
        ]);
    }

    /**
     * Manejar errores de API
     */
    private function handleApiError($e)
    {
        error_log("API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            "error" => "Error interno de la API",
            "message" => "Se produjo un error inesperado"
        ]);
    }

    /**
     * Manejar 404
     */
    private function handle404()
    {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Página no encontrada</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <style>
                body {
                    font-family: 'Inter', sans-serif;
                    background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                    color: white;
                    padding: 20px;
                }
                .error-container {
                    text-align: center;
                    background: rgba(22, 33, 62, 0.8);
                    backdrop-filter: blur(20px);
                    padding: 60px 40px;
                    border-radius: 24px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
                    max-width: 600px;
                }
                h1 {
                    font-size: 4rem;
                    margin: 0 0 20px 0;
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
                p { font-size: 1.2rem; margin: 20px 0; color: #b8c5d6; }
                .links {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 15px;
                    justify-content: center;
                    margin-top: 30px;
                }
                a {
                    color: #00d4ff;
                    text-decoration: none;
                    font-weight: bold;
                    padding: 12px 20px;
                    background: rgba(0, 212, 255, 0.1);
                    border-radius: 12px;
                    border: 1px solid rgba(0, 212, 255, 0.3);
                    transition: all 0.3s ease;
                    font-size: 0.9rem;
                }
                a:hover {
                    background: rgba(0, 212, 255, 0.2);
                    transform: translateY(-2px);
                    box-shadow: 0 8px 24px rgba(0, 212, 255, 0.3);
                }
                .debug {
                    background: rgba(0,0,0,0.3);
                    padding: 20px;
                    border-radius: 12px;
                    margin-top: 30px;
                    font-family: 'Fira Code', monospace;
                    font-size: 0.9rem;
                    color: #8892a6;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                @media (max-width: 768px) {
                    .error-container { padding: 40px 20px; }
                    h1 { font-size: 3rem; }
                    p { font-size: 1rem; }
                    a { display: block; margin: 10px 0; }
                }
            </style>
        </head>
        <body>
        <div class="error-container">
            <h1>404</h1>
            <p>Página no encontrada</p>
            <p>La ruta que buscas no existe en nuestro sistema</p>

            <div class="links">
                <a href="/">🏠 Inicio</a>
                <a href="/login">🔑 Login</a>
                <a href="/registro">👤 Registro</a>
                <a href="/dashboard">📊 Dashboard</a>
                <a href="/carrito">🛒 Carrito</a>
                <a href="/videos">🎬 Videos</a>
            </div>

            <div class="debug">
                <strong>🔍 Información de debug:</strong><br>
                URI: <?= htmlspecialchars($this->uri) ?><br>
                Método: <?= htmlspecialchars($this->method) ?><br>
                Timestamp: <?= date('Y-m-d H:i:s') ?>
            </div>
        </div>
        </body>
        </html>
        <?php
    }

    /**
     * Obtener información de rutas para debug
     */
    public function getRouteInfo()
    {
        return [
            'uri' => $this->uri,
            'method' => $this->method,
            'project_root' => $this->projectRoot,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Listar todas las rutas disponibles
     */
    public function listRoutes()
    {
        return [
            'frontend_routes' => [
                '/' => 'Redirige a login o dashboard',
                '/login' => 'Página de inicio de sesión',
                '/registro' => 'Página de registro',
                '/dashboard' => 'Dashboard principal',
                '/carrito' => 'Carrito de compras',
                '/cambiar-password' => 'Cambiar contraseña',
                '/perfil' => 'Perfil de usuario',
                '/configuracion' => 'Configuración',
                '/videos' => 'Videos educativos de YouTube',
                '/logout' => 'Cerrar sesión'
            ],
            'api_routes' => [
                'POST /api/clientes/login' => 'Login con contraseña',
                'POST /api/clientes/login-legacy' => 'Login solo con email',
                'POST /api/clientes/registro' => 'Registro de usuario',
                'POST /api/clientes/cambiar-password' => 'Cambiar contraseña',
                'GET /api/clientes' => 'Listar clientes',
                'GET|POST|PATCH|DELETE /api/cursos' => 'Gestión de cursos',
                'GET|POST /api/compras' => 'Gestión de compras',
                'GET /api/metodos-pago' => 'Métodos de pago',
                'GET|POST|DELETE|PATCH /api/carrito' => 'Gestión del carrito',
                'GET /api/auth' => 'Validación de autenticación',
                'GET /api/videos' => 'Gestión de videos de YouTube',
                'GET /api/test-connection' => 'Prueba de conexión'
            ]
        ];
    }
}
?>