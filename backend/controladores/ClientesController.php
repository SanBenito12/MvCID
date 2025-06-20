<?php
// backend/controladores/ClientesController.php
require_once __DIR__ . '/../modelos/Cliente.php';

class ClientesController
{
    /**
     * Registrar un nuevo cliente
     */
    public function registrar($nombre, $apellido, $email)
    {
        // Validaciones
        if (empty($nombre) || empty($apellido) || empty($email)) {
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

        // Verificar si el email ya existe
        if (Cliente::emailExiste($email)) {
            return [
                "success" => false,
                "error" => "El email ya está registrado"
            ];
        }

        // Crear cliente usando el modelo
        return Cliente::crear($nombre, $apellido, $email);
    }

    /**
     * Login por email
     */
    public function login($email)
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
     * Cambiar credenciales de un cliente
     */
    public function regenerarCredenciales($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID inválido"
            ];
        }

        // Generar nuevas credenciales
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
}