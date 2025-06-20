<?php
// backend/controladores/MetodosPagoController.php
require_once __DIR__ . '/../modelos/MetodoPago.php';
require_once __DIR__ . '/../modelos/Cliente.php';

class MetodosPagoController
{
    /**
     * Obtener todos los métodos de pago
     */
    public function obtenerTodos()
    {
        return MetodoPago::obtenerTodos();
    }

    /**
     * Obtener método de pago por ID
     */
    public function obtenerPorId($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID de método de pago inválido"
            ];
        }

        return MetodoPago::obtenerPorId($id);
    }

    /**
     * Crear nuevo método de pago (solo administradores)
     */
    public function crear($nombre, $id_cliente = null, $llave_secreta = null)
    {
        // Validar autenticación si se proporcionan credenciales
        if ($id_cliente && $llave_secreta) {
            $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

            if (!$validacion["success"]) {
                return [
                    "success" => false,
                    "error" => "Credenciales inválidas"
                ];
            }
        }

        // Validaciones
        if (empty($nombre)) {
            return [
                "success" => false,
                "error" => "El nombre del método de pago es requerido"
            ];
        }

        // Verificar que no existe ya un método con ese nombre
        if (MetodoPago::existePorNombre($nombre)) {
            return [
                "success" => false,
                "error" => "Ya existe un método de pago con ese nombre"
            ];
        }

        return MetodoPago::crear($nombre);
    }

    /**
     * Actualizar método de pago
     */
    public function actualizar($id, $nombre, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validaciones
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID de método de pago inválido"
            ];
        }

        if (empty($nombre)) {
            return [
                "success" => false,
                "error" => "El nombre del método de pago es requerido"
            ];
        }

        // Verificar que el método existe
        $metodo = MetodoPago::obtenerPorId($id);
        if (!$metodo["success"]) {
            return [
                "success" => false,
                "error" => "Método de pago no encontrado"
            ];
        }

        // Verificar que no hay otro método con el mismo nombre
        if (MetodoPago::existePorNombre($nombre)) {
            // Verificar que no es el mismo método
            $metodoExistente = MetodoPago::obtenerTodos();
            if ($metodoExistente["success"]) {
                foreach ($metodoExistente["metodos"] as $m) {
                    if ($m["nombre"] === $nombre && $m["id"] != $id) {
                        return [
                            "success" => false,
                            "error" => "Ya existe otro método de pago con ese nombre"
                        ];
                    }
                }
            }
        }

        return MetodoPago::actualizar($id, $nombre);
    }

    /**
     * Eliminar método de pago
     */
    public function eliminar($id, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validaciones
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID de método de pago inválido"
            ];
        }

        // Verificar que el método existe
        $metodo = MetodoPago::obtenerPorId($id);
        if (!$metodo["success"]) {
            return [
                "success" => false,
                "error" => "Método de pago no encontrado"
            ];
        }

        // Verificar que no hay compras asociadas a este método
        require_once __DIR__ . '/../modelos/Compra.php';
        $endpoint = "compras?select=id&id_metodo_pago=eq." . intval($id);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            return [
                "success" => false,
                "error" => "No se puede eliminar el método de pago porque tiene compras asociadas"
            ];
        }

        return MetodoPago::eliminar($id);
    }

    /**
     * Obtener estadísticas de uso de métodos de pago
     */
    public function obtenerEstadisticas($id_cliente = null, $llave_secreta = null)
    {
        // Validar autenticación si se proporcionan credenciales
        if ($id_cliente && $llave_secreta) {
            $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

            if (!$validacion["success"]) {
                return [
                    "success" => false,
                    "error" => "Credenciales inválidas"
                ];
            }
        }

        return MetodoPago::obtenerEstadisticasUso();
    }

    /**
     * Inicializar métodos de pago por defecto
     */
    public function inicializarDefecto()
    {
        return MetodoPago::inicializarDefecto();
    }

    /**
     * Verificar disponibilidad de método de pago
     */
    public function verificarDisponibilidad($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID de método de pago inválido"
            ];
        }

        $metodo = MetodoPago::obtenerPorId($id);

        if (!$metodo["success"]) {
            return [
                "success" => false,
                "disponible" => false,
                "error" => "Método de pago no encontrado"
            ];
        }

        return [
            "success" => true,
            "disponible" => true,
            "metodo" => $metodo["metodo"]
        ];
    }

    /**
     * Obtener métodos de pago populares
     */
    public function obtenerPopulares($limite = 5)
    {
        $estadisticas = MetodoPago::obtenerEstadisticasUso();

        if (!$estadisticas["success"]) {
            return $estadisticas;
        }

        // Ordenar por uso y tomar los primeros
        $metodosPopulares = array_slice($estadisticas["estadisticas"], 0, intval($limite), true);

        return [
            "success" => true,
            "metodos_populares" => $metodosPopulares,
            "total_analizados" => $estadisticas["total_compras"]
        ];
    }

    /**
     * Buscar métodos de pago por nombre
     */
    public function buscarPorNombre($nombre)
    {
        if (empty($nombre)) {
            return [
                "success" => false,
                "error" => "Nombre requerido para búsqueda"
            ];
        }

        $todosMedodos = MetodoPago::obtenerTodos();

        if (!$todosMedodos["success"]) {
            return $todosMedodos;
        }

        // Filtrar por nombre (búsqueda insensible a mayúsculas)
        $resultados = array_filter($todosMedodos["metodos"], function($metodo) use ($nombre) {
            return stripos($metodo["nombre"], $nombre) !== false;
        });

        return [
            "success" => true,
            "resultados" => array_values($resultados)
        ];
    }
}