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

            // Compatibilidad hacia atrás para api-metodos
            case '/api-metodos':
            case '/api-metodos.php':
                require_once $this->projectRoot . '/backend/api/metodos_pago.php';
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
        if (isset($_SESSION['id_cliente']) && isset($_SESSION['llave_secreta'])) {
            header("Location: /dashboard");
        } else {
            header("Location: /login");
        }
        exit;
    }

    /**
     * Servir página del frontend
     */
    private function serveFrontendPage($page)
    {
        $filePath = $this->projectRoot . '/frontend/' . $page;

        if (file_exists($filePath)) {
            require_once $filePath;
        } else {
            error_log("Frontend page not found: $filePath");
            $this->handle404();
        }
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

            if ($this->uri === '/api/clientes/registro' && $this->method === 'POST') {
                $_GET['accion'] = 'registro';
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

            // Ruta de autenticación
            if (preg_match('#^/api/auth/?$#', $this->uri)) {
                require_once $this->projectRoot . '/backend/api/auth.php';
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
            '/backend/api/metodos_pago.php' => '/backend/api/metodos_pago.php'
        ];

        if (isset($allowedBackendRoutes[$this->uri])) {
            require_once $this->projectRoot . $allowedBackendRoutes[$this->uri];
            return true;
        }

        // Bloquear acceso directo a otros archivos del backend
        http_response_code(403);
        echo json_encode(["error" => "Acceso no permitido"]);
        return true;
    }

    /**
     * Manejar API no encontrada
     */
    private function handleApiNotFound()
    {
        http_response_code(404);
        echo json_encode([
            "error" => "Ruta API no encontrada",
            "uri" => $this->uri,
            "method" => $this->method,
            "available_routes" => [
                "POST /api/clientes/login",
                "POST /api/clientes/registro",
                "GET /api/clientes",
                "GET|POST|PATCH|DELETE /api/cursos",
                "GET|POST /api/compras",
                "GET|POST|PATCH|DELETE /api/metodos-pago",
                "GET /api/auth"
            ]
        ]);
    }

    /**
     * Manejar errores de API
     */
    private function handleApiError($exception)
    {
        error_log("API Error: " . $exception->getMessage());
        http_response_code(500);
        echo json_encode([
            "error" => "Error interno del servidor",
            "message" => $exception->getMessage()
        ]);
    }

    /**
     * Manejar 404 - Página no encontrada
     */
    private function handle404()
    {
        http_response_code(404);

        // Si es una petición de API, devolver JSON
        if (strpos($this->uri, '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode([
                "error" => "Endpoint no encontrado",
                "uri" => $this->uri,
                "method" => $this->method
            ]);
            return;
        }

        // Para peticiones web, mostrar página 404
        $this->show404Page();
    }

    /**
     * Mostrar página 404 personalizada
     */
    private function show404Page()
    {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Página no encontrada</title>
            <style>
                body {
                    font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
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
                    position: relative;
                    overflow: hidden;
                }
                .error-container::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%);
                }
                h1 {
                    font-size: 4rem;
                    margin: 0 0 20px 0;
                    background: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                }
                p {
                    font-size: 1.2rem;
                    margin: 20px 0;
                    color: #b8c5d6;
                    line-height: 1.6;
                }
                a {
                    color: #00d4ff;
                    text-decoration: none;
                    font-weight: bold;
                    font-size: 1.1rem;
                    display: inline-block;
                    padding: 12px 24px;
                    background: rgba(0, 212, 255, 0.1);
                    border-radius: 12px;
                    border: 1px solid rgba(0, 212, 255, 0.3);
                    transition: all 0.3s ease;
                    margin: 10px;
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
                .links {
                    margin-top: 30px;
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
}
?>