<?php
// backend/modelos/MetodoPago.php
require_once __DIR__ . '/../includes/db.php';

class MetodoPago
{
    /**
     * Obtener todos los métodos de pago
     */
    public static function obtenerTodos()
    {
        $respuesta = supabaseRequest("metodos_pago?select=*", "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "metodos" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener métodos de pago"
            ];
        }
    }

    /**
     * Obtener método de pago por ID
     */
    public static function obtenerPorId($id)
    {
        $endpoint = "metodos_pago?select=*&id=eq." . intval($id);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            return [
                "success" => true,
                "metodo" => $respuesta["body"][0]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Método de pago no encontrado"
            ];
        }
    }

    /**
     * Crear nuevo método de pago
     */
    public static function crear($nombre)
    {
        $data = [
            "nombre" => $nombre
        ];

        $respuesta = supabaseRequest("metodos_pago", "POST", $data);

        if ($respuesta["status"] === 201) {
            return [
                "success" => true,
                "mensaje" => "Método de pago creado exitosamente",
                "metodo" => $respuesta["body"][0] ?? $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al crear método de pago"
            ];
        }
    }

    /**
     * Actualizar método de pago
     */
    public static function actualizar($id, $nombre)
    {
        $data = [
            "nombre" => $nombre
        ];

        $respuesta = supabaseRequest("metodos_pago?id=eq." . intval($id), "PATCH", $data);

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "mensaje" => "Método de pago actualizado exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al actualizar método de pago"
            ];
        }
    }

    /**
     * Eliminar método de pago
     */
    public static function eliminar($id)
    {
        $respuesta = supabaseRequest("metodos_pago?id=eq." . intval($id), "DELETE");

        if ($respuesta["status"] === 200 || $respuesta["status"] === 204) {
            return [
                "success" => true,
                "mensaje" => "Método de pago eliminado exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al eliminar método de pago"
            ];
        }
    }

    /**
     * Verificar si existe un método de pago por nombre
     */
    public static function existePorNombre($nombre)
    {
        $endpoint = "metodos_pago?select=id&nombre=eq." . urlencode($nombre);
        $respuesta = supabaseRequest($endpoint, "GET");

        return ($respuesta["status"] === 200 && count($respuesta["body"]) > 0);
    }

    /**
     * Obtener estadísticas de uso de métodos de pago
     */
    public static function obtenerEstadisticasUso()
    {
        // Obtener todas las compras con métodos de pago
        $endpoint = "compras?select=id_metodo_pago,metodos_pago(nombre)";
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            $compras = $respuesta["body"];
            $estadisticas = [];

            // Contar uso por método
            foreach ($compras as $compra) {
                $metodo = $compra["metodos_pago"]["nombre"];
                if (!isset($estadisticas[$metodo])) {
                    $estadisticas[$metodo] = 0;
                }
                $estadisticas[$metodo]++;
            }

            // Ordenar por uso descendente
            arsort($estadisticas);

            return [
                "success" => true,
                "estadisticas" => $estadisticas,
                "total_compras" => count($compras)
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener estadísticas"
            ];
        }
    }

    /**
     * Inicializar métodos de pago por defecto
     */
    public static function inicializarDefecto()
    {
        $metodosDefecto = [
            "Tarjeta de Crédito",
            "PayPal",
            "Transferencia Bancaria",
            "Efectivo",
            "Criptomonedas"
        ];

        $resultados = [];
        foreach ($metodosDefecto as $metodo) {
            if (!self::existePorNombre($metodo)) {
                $resultado = self::crear($metodo);
                $resultados[] = $resultado;
            }
        }

        return [
            "success" => true,
            "mensaje" => "Métodos de pago inicializados",
            "resultados" => $resultados
        ];
    }
}