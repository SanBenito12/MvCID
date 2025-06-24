<?php
// backend/controladores/CarritoController.php
require_once __DIR__ . '/../modelos/Carrito.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../modelos/Curso.php';
require_once __DIR__ . '/../modelos/MetodoPago.php';

class CarritoController
{
    /**
     * Obtener carrito del cliente autenticado
     */
    public function obtenerCarrito($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        return Carrito::obtenerConItems($validacion["cliente"]["id"]);
    }

    /**
     * Obtener solo resumen del carrito (para header/contador)
     */
    public function obtenerResumen($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        return Carrito::obtenerResumen($validacion["cliente"]["id"]);
    }

    /**
     * Agregar curso al carrito
     */
    public function agregarCurso($id_curso, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validar que el curso existe
        if (!is_numeric($id_curso)) {
            return [
                "success" => false,
                "error" => "ID de curso inválido"
            ];
        }

        $curso = Curso::obtenerPorId($id_curso);
        if (!$curso["success"]) {
            return [
                "success" => false,
                "error" => "El curso especificado no existe"
            ];
        }

        $clienteId = $validacion["cliente"]["id"];

        // Verificar que no sea el creador del curso
        if ($curso["curso"]["id_creador"] == $clienteId) {
            return [
                "success" => false,
                "error" => "No puedes agregar tu propio curso al carrito"
            ];
        }

        // Verificar que no lo haya comprado ya
        require_once __DIR__ . '/../modelos/Compra.php';
        if (Compra::yaCompro($clienteId, $id_curso)) {
            return [
                "success" => false,
                "error" => "Ya has comprado este curso"
            ];
        }

        // Agregar al carrito
        return Carrito::agregarCurso($clienteId, $id_curso);
    }

    /**
     * Eliminar curso del carrito
     */
    public function eliminarCurso($id_curso, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        if (!is_numeric($id_curso)) {
            return [
                "success" => false,
                "error" => "ID de curso inválido"
            ];
        }

        return Carrito::eliminarCurso($validacion["cliente"]["id"], $id_curso);
    }

    /**
     * Vaciar carrito completamente
     */
    public function vaciarCarrito($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        return Carrito::vaciar($validacion["cliente"]["id"]);
    }

    /**
     * Procesar compra del carrito
     */
    public function procesarCompra($id_metodo_pago, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validar método de pago
        if (!is_numeric($id_metodo_pago)) {
            return [
                "success" => false,
                "error" => "Método de pago inválido"
            ];
        }

        $metodoPago = MetodoPago::obtenerPorId($id_metodo_pago);
        if (!$metodoPago["success"]) {
            return [
                "success" => false,
                "error" => "El método de pago especificado no existe"
            ];
        }

        return Carrito::procesarCompra($validacion["cliente"]["id"], $id_metodo_pago);
    }

    /**
     * Obtener estadísticas del carrito
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

        return Carrito::obtenerEstadisticas($validacion["cliente"]["id"]);
    }

    /**
     * Verificar si un curso específico está en el carrito
     */
    public function verificarCursoEnCarrito($id_curso, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        if (!is_numeric($id_curso)) {
            return [
                "success" => false,
                "error" => "ID de curso inválido"
            ];
        }

        $estaEnCarrito = Carrito::tieneCurso($validacion["cliente"]["id"], $id_curso);

        return [
            "success" => true,
            "en_carrito" => $estaEnCarrito
        ];
    }

    /**
     * Sincronizar carrito (limpiar cursos ya comprados o eliminados)
     */
    public function sincronizarCarrito($id_cliente, $llave_secreta)
    {
        try {
            // Validar credenciales
            $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

            if (!$validacion["success"]) {
                return [
                    "success" => false,
                    "error" => "Credenciales inválidas"
                ];
            }

            $clienteId = $validacion["cliente"]["id"];

            // Obtener carrito con items
            $carritoResult = Carrito::obtenerConItems($clienteId);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];
            $itemsEliminados = 0;

            foreach ($carrito["items"] as $item) {
                $id_curso = $item["id_curso"];
                $debeEliminar = false;

                // Verificar si el curso aún existe
                $curso = Curso::obtenerPorId($id_curso);
                if (!$curso["success"]) {
                    $debeEliminar = true;
                }

                // Verificar si ya lo compró
                require_once __DIR__ . '/../modelos/Compra.php';
                if (!$debeEliminar && Compra::yaCompro($clienteId, $id_curso)) {
                    $debeEliminar = true;
                }

                // Verificar si es el creador del curso
                if (!$debeEliminar && isset($curso["curso"]) && $curso["curso"]["id_creador"] == $clienteId) {
                    $debeEliminar = true;
                }

                if ($debeEliminar) {
                    Carrito::eliminarCurso($clienteId, $id_curso);
                    $itemsEliminados++;
                }
            }

            return [
                "success" => true,
                "mensaje" => "Carrito sincronizado",
                "items_eliminados" => $itemsEliminados
            ];

        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error al sincronizar carrito: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener cursos recomendados basados en el carrito
     */
    public function obtenerRecomendaciones($id_cliente, $llave_secreta, $limite = 5)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        try {
            $clienteId = $validacion["cliente"]["id"];

            // Obtener cursos disponibles (que no ha creado ni comprado)
            $cursosDisponibles = Curso::obtenerDisponiblesParaCliente($clienteId);
            
            if (!$cursosDisponibles["success"]) {
                return $cursosDisponibles;
            }

            // Filtrar cursos que ya están en el carrito
            $recomendaciones = [];
            foreach ($cursosDisponibles["cursos"] as $curso) {
                if (!Carrito::tieneCurso($clienteId, $curso["id"])) {
                    $recomendaciones[] = $curso;
                }

                if (count($recomendaciones) >= $limite) {
                    break;
                }
            }

            return [
                "success" => true,
                "recomendaciones" => $recomendaciones
            ];

        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error al obtener recomendaciones: " . $e->getMessage()
            ];
        }
    }

    /**
     * Aplicar descuento al carrito (funcionalidad futura)
     */
    public function aplicarDescuento($codigo_descuento, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Por ahora retornar que no está implementado
        return [
            "success" => false,
            "error" => "Funcionalidad de descuentos no implementada aún"
        ];
    }

    /**
     * Obtener historial de carritos procesados
     */
    public function obtenerHistorial($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        try {
            $endpoint = "carritos?select=*&id_cliente=eq." . $validacion["cliente"]["id"] . "&estado=eq.procesado&order=fecha_actualizacion.desc";
            $respuesta = supabaseRequest($endpoint, "GET");

            if ($respuesta["status"] === 200) {
                return [
                    "success" => true,
                    "historial" => $respuesta["body"]
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Error al obtener historial de carritos"
                ];
            }

        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error: " . $e->getMessage()
            ];
        }
    }
}