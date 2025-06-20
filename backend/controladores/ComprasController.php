<?php
// backend/controladores/ComprasController.php
require_once __DIR__ . '/../modelos/Compra.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../modelos/Curso.php';
require_once __DIR__ . '/../modelos/MetodoPago.php';

class ComprasController
{
    /**
     * Realizar una nueva compra
     */
    public function realizarCompra($datos, $id_cliente, $llave_secreta)
    {
        // Validar credenciales del cliente
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validaciones de datos requeridos
        $camposRequeridos = ['id_curso', 'id_metodo_pago', 'precio_pagado'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                return [
                    "success" => false,
                    "error" => "El campo '$campo' es requerido"
                ];
            }
        }

        // Validar que el curso existe
        $curso = Curso::obtenerPorId($datos['id_curso']);
        if (!$curso["success"]) {
            return [
                "success" => false,
                "error" => "El curso especificado no existe"
            ];
        }

        // Validar que el método de pago existe
        $metodoPago = MetodoPago::obtenerPorId($datos['id_metodo_pago']);
        if (!$metodoPago["success"]) {
            return [
                "success" => false,
                "error" => "El método de pago especificado no existe"
            ];
        }

        // Verificar que el cliente no sea el creador del curso
        if ($curso["curso"]["id_creador"] == $validacion["cliente"]["id"]) {
            return [
                "success" => false,
                "error" => "No puedes comprar tu propio curso"
            ];
        }

        // Verificar que el cliente no haya comprado ya este curso
        if (Compra::yaCompro($validacion["cliente"]["id"], $datos['id_curso'])) {
            return [
                "success" => false,
                "error" => "Ya has comprado este curso"
            ];
        }

        // Validar precio
        if (!is_numeric($datos['precio_pagado']) || $datos['precio_pagado'] <= 0) {
            return [
                "success" => false,
                "error" => "El precio pagado debe ser un número válido mayor a 0"
            ];
        }

        // Asignar cliente a la compra
        $datos['id_cliente'] = $validacion["cliente"]["id"];

        // Crear la compra
        return Compra::crear($datos);
    }

    /**
     * Obtener compras por cliente autenticado
     */
    public function obtenerComprasPorCliente($id_cliente, $llave_secreta)
    {
        // Validar credenciales del cliente
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        return Compra::obtenerPorCliente($validacion["cliente"]["id"]);
    }

    /**
     * Obtener todas las compras (para administración)
     */
    public function obtenerTodas()
    {
        return Compra::obtenerTodas();
    }

    /**
     * Obtener compras de un curso específico
     */
    public function obtenerComprasPorCurso($id_curso, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Verificar que el usuario es propietario del curso
        if (!Curso::esPropiertario($id_curso, $validacion["cliente"]["id"])) {
            return [
                "success" => false,
                "error" => "No tienes permisos para ver las compras de este curso"
            ];
        }

        return Compra::obtenerPorCurso($id_curso);
    }

    /**
     * Obtener compra por ID
     */
    public function obtenerPorId($id, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        $compra = Compra::obtenerPorId($id);

        if (!$compra["success"]) {
            return $compra;
        }

        // Verificar que el cliente puede ver esta compra (es su compra o es el creador del curso)
        $clienteId = $validacion["cliente"]["id"];
        $esComprador = $compra["compra"]["id_cliente"] == $clienteId;
        $esCreador = isset($compra["compra"]["cursos"]) && $compra["compra"]["cursos"]["id_creador"] == $clienteId;

        if (!$esComprador && !$esCreador) {
            return [
                "success" => false,
                "error" => "No tienes permisos para ver esta compra"
            ];
        }

        return $compra;
    }

    /**
     * Cancelar compra (reembolso)
     */
    public function cancelarCompra($id, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Obtener la compra
        $compra = Compra::obtenerPorId($id);

        if (!$compra["success"]) {
            return $compra;
        }

        // Verificar que el cliente es quien hizo la compra
        if ($compra["compra"]["id_cliente"] != $validacion["cliente"]["id"]) {
            return [
                "success" => false,
                "error" => "No puedes cancelar una compra que no es tuya"
            ];
        }

        // Verificar tiempo límite para cancelación (opcional - 24 horas)
        $fechaCompra = new DateTime($compra["compra"]["fecha_compra"]);
        $ahora = new DateTime();
        $diferencia = $ahora->diff($fechaCompra);

        if ($diferencia->days > 1) {
            return [
                "success" => false,
                "error" => "Solo puedes cancelar compras dentro de las primeras 24 horas"
            ];
        }

        return Compra::cancelar($id);
    }

    /**
     * Obtener estadísticas de compras del cliente
     */
    public function obtenerEstadisticas($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        return Compra::obtenerEstadisticasCliente($validacion["cliente"]["id"]);
    }

    /**
     * Obtener ingresos del creador
     */
    public function obtenerIngresos($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        return Compra::obtenerIngresosPorCreador($validacion["cliente"]["id"]);
    }

    /**
     * Verificar si un cliente puede comprar un curso
     */
    public function puedeComprar($id_curso, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        $clienteId = $validacion["cliente"]["id"];

        // Verificar que el curso existe
        $curso = Curso::obtenerPorId($id_curso);
        if (!$curso["success"]) {
            return [
                "success" => false,
                "puede_comprar" => false,
                "razon" => "El curso no existe"
            ];
        }

        // Verificar que no es el creador
        if ($curso["curso"]["id_creador"] == $clienteId) {
            return [
                "success" => true,
                "puede_comprar" => false,
                "razon" => "No puedes comprar tu propio curso"
            ];
        }

        // Verificar que no lo ha comprado ya
        if (Compra::yaCompro($clienteId, $id_curso)) {
            return [
                "success" => true,
                "puede_comprar" => false,
                "razon" => "Ya has comprado este curso"
            ];
        }

        return [
            "success" => true,
            "puede_comprar" => true,
            "curso" => $curso["curso"]
        ];
    }

    /**
     * Obtener compras en un rango de fechas
     */
    public function obtenerPorRangoFechas($fechaInicio, $fechaFin, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validar fechas
        if (empty($fechaInicio) || empty($fechaFin)) {
            return [
                "success" => false,
                "error" => "Fechas de inicio y fin son requeridas"
            ];
        }

        return Compra::obtenerPorRangoFechas($fechaInicio, $fechaFin);
    }

    /**
     * Procesar reembolso
     */
    public function procesarReembolso($id_compra, $motivo, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Obtener la compra
        $compra = Compra::obtenerPorId($id_compra);

        if (!$compra["success"]) {
            return $compra;
        }

        // Verificar permisos (comprador o creador del curso)
        $clienteId = $validacion["cliente"]["id"];
        $esComprador = $compra["compra"]["id_cliente"] == $clienteId;
        $esCreador = isset($compra["compra"]["cursos"]) && $compra["compra"]["cursos"]["id_creador"] == $clienteId;

        if (!$esComprador && !$esCreador) {
            return [
                "success" => false,
                "error" => "No tienes permisos para procesar este reembolso"
            ];
        }

        // Actualizar compra con información del reembolso
        $datosReembolso = [
            "estado" => "reembolsado",
            "motivo_reembolso" => $motivo,
            "fecha_reembolso" => date("c")
        ];

        $actualizacion = Compra::actualizar($id_compra, $datosReembolso);

        if ($actualizacion["success"]) {
            return [
                "success" => true,
                "mensaje" => "Reembolso procesado exitosamente",
                "compra_id" => $id_compra,
                "motivo" => $motivo
            ];
        } else {
            return $actualizacion;
        }
    }
}