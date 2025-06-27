<?php
// backend/controladores/ClientesController.php - Actualizado
require_once __DIR__ . '/../modelos/Cliente.php';

class ClientesController
{
    /**
     * Registrar un nuevo cliente con contraseña
     */
    public function registrar($nombre, $apellido, $email, $password)
    {
        // Validaciones básicas
        if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
            return [
                "success" => false,
                "error" => "Todos los campos son requeridos"
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                "success" => false,
                "error" => "Email inválido"
            ];
        }

        // Validar fortaleza de contraseña
        $validacionPassword = Cliente::validarPassword($password);
        if (!$validacionPassword["valida"]) {
            return [
                "success" => false,
                "error" => "Contraseña no válida: " . implode(", ", $validacionPassword["errores"])
            ];
        }

        // Verificar si el email ya existe
        if (Cliente::emailExiste($email)) {
            return [
                "success" => false,
                "error" => "El email ya está registrado"
            ];
        }

        // Crear cliente usando el modelo
        return Cliente::crear($nombre, $apellido, $email, $password);
    }

    /**
     * Login con email y contraseña
     */
    public function login($email, $password)
    {
        // Validaciones básicas
        if (empty($email) || empty($password)) {
            return [
                "success" => false,
                "error" => "Email y contraseña son requeridos"
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                "success" => false,
                "error" => "Email inválido"
            ];
        }

        // Autenticar usando el modelo
        $resultado = Cliente::autenticar($email, $password);

        if ($resultado["success"]) {
            $cliente = $resultado["cliente"];
            return [
                "success" => true,
                "mensaje" => "Cliente autenticado",
                "id_cliente" => $cliente["id_cliente"],
                "llave_secreta" => $cliente["llave_secreta"],
                "cliente" => $cliente
            ];
        } else {
            return $resultado;
        }
    }

    /**
     * Login solo por email (método legacy para compatibilidad)
     */
    public function loginSoloEmail($email)
    {
        // Validaciones
        if (empty($email)) {
            return [
                "success" => false,
                "error" => "Email es requerido"
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                "success" => false,
                "error" => "Email inválido"
            ];
        }

        // Buscar cliente usando el modelo
        $resultado = Cliente::obtenerPorEmail($email);

        if ($resultado["success"]) {
            $cliente = $resultado["cliente"];
            return [
                "success" => true,
                "mensaje" => "Cliente encontrado (método legacy)",
                "id_cliente" => $cliente["id_cliente"],
                "llave_secreta" => $cliente["llave_secreta"],
                "cliente" => $cliente
            ];
        } else {
            return [
                "success" => false,
                "error" => "Usuario no encontrado"
            ];
        }
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword($id_cliente, $llave_secreta, $password_actual, $password_nueva)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validaciones de contraseña
        if (empty($password_actual) || empty($password_nueva)) {
            return [
                "success" => false,
                "error" => "Contraseña actual y nueva son requeridas"
            ];
        }

        // Validar fortaleza de nueva contraseña
        $validacionPassword = Cliente::validarPassword($password_nueva);
        if (!$validacionPassword["valida"]) {
            return [
                "success" => false,
                "error" => "Nueva contraseña no válida: " . implode(", ", $validacionPassword["errores"])
            ];
        }

        return Cliente::cambiarPassword($id_cliente, $password_actual, $password_nueva);
    }

    /**
     * Validar credenciales de cliente
     */
    public function validarCredenciales($id_cliente, $llave_secreta)
    {
        if (empty($id_cliente) || empty($llave_secreta)) {
            return [
                "success" => false,
                "error" => "Credenciales incompletas"
            ];
        }

        return Cliente::validarCredenciales($id_cliente, $llave_secreta);
    }

    /**
     * Obtener todos los clientes (para administración)
     */
    public function obtenerTodos()
    {
        return Cliente::obtenerTodos();
    }

    /**
     * Obtener cliente por ID
     */
    public function obtenerPorId($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID inválido"
            ];
        }

        return Cliente::obtenerPorId($id);
    }

    /**
     * Actualizar información del cliente
     */
    public function actualizar($id, $datos)
    {
        // Validaciones
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID inválido"
            ];
        }

        // Validar email si se está actualizando
        if (isset($datos['email'])) {
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    "success" => false,
                    "error" => "Email inválido"
                ];
            }

            // Verificar que el nuevo email no esté en uso por otro cliente
            $clienteExistente = Cliente::obtenerPorEmail($datos['email']);
            if ($clienteExistente["success"] && $clienteExistente["cliente"]["id"] != $id) {
                return [
                    "success" => false,
                    "error" => "El email ya está en uso por otro cliente"
                ];
            }
        }

        return Cliente::actualizar($id, $datos);
    }

    /**
     * Eliminar cliente
     */
    public function eliminar($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID inválido"
            ];
        }

        // Verificar que el cliente existe
        $cliente = Cliente::obtenerPorId($id);
        if (!$cliente["success"]) {
            return [
                "success" => false,
                "error" => "Cliente no encontrado"
            ];
        }

        return Cliente::eliminar($id);
    }

    /**
     * Regenerar credenciales de un cliente
     */
    public function regenerarCredenciales($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID inválido"
            ];
        }

        // Generar nuevas credenciales ENCRIPTADO
        $nuevasCredenciales = [
            "id_cliente" => bin2hex(random_bytes(32)),
            "llave_secreta" => bin2hex(random_bytes(32))
        ];

        $resultado = Cliente::actualizar($id, $nuevasCredenciales);

        if ($resultado["success"]) {
            return [
                "success" => true,
                "mensaje" => "Credenciales regeneradas exitosamente",
                "nuevas_credenciales" => $nuevasCredenciales
            ];
        } else {
            return $resultado;
        }
    }

    /**
     * Buscar clientes por email
     */
    public function buscarPorEmail($email)
    {
        if (empty($email)) {
            return [
                "success" => false,
                "error" => "Email requerido para búsqueda"
            ];
        }

        return Cliente::obtenerPorEmail($email);
    }

    /**
     * Obtener perfil completo del cliente autenticado
     */
    public function obtenerPerfil($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = $this->validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return $validacion;
        }

        $cliente = $validacion["cliente"];

        // Obtener estadísticas adicionales
        require_once __DIR__ . '/../modelos/Curso.php';
        require_once __DIR__ . '/../modelos/Compra.php';

        $estadisticasCursos = Curso::obtenerEstadisticasCreador($cliente["id"]);
        $estadisticasCompras = Compra::obtenerEstadisticasCliente($cliente["id"]);
        $ingresos = Compra::obtenerIngresosPorCreador($cliente["id"]);

        return [
            "success" => true,
            "perfil" => [
                "cliente" => $cliente,
                "estadisticas_cursos" => $estadisticasCursos["success"] ? $estadisticasCursos["estadisticas"] : null,
                "estadisticas_compras" => $estadisticasCompras["success"] ? $estadisticasCompras["estadisticas"] : null,
                "ingresos" => $ingresos["success"] ? $ingresos["ingresos"] : null
            ]
        ];
    }

    /**
     * Validar fortaleza de contraseña ValidarContra
     */
    public function validarFortalezaPassword($password)
    {
        return Cliente::validarPassword($password);
    }
}