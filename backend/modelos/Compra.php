<?php
// backend/modelos/Compra.php
require_once __DIR__ . '/../includes/db.php';

class Compra
{
    /**
     * Crear una nueva compra
     */
    public static function crear($datos)
    {
        $datos["fecha_compra"] = $datos["fecha_compra"] ?? date("c");

        $respuesta = supabaseRequest("compras", "POST", $datos);

        if ($respuesta["status"] === 201) {
            return [
                "success" => true,
                "mensaje" => "Compra realizada exitosamente",
                "compra" => $respuesta["body"][0] ?? $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al procesar la compra",
                "detalle" => $respuesta["raw"]
            ];
        }
    }

    /**
     * Obtener compras por cliente con información completa
     */
    public static function obtenerPorCliente($id_cliente)
    {
        $endpoint = "compras?select=*,cursos(*),metodos_pago(nombre)&id_cliente=eq." . intval($id_cliente);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "compras" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener compras del cliente"
            ];
        }
    }

    /**
     * Obtener todas las compras
     */
    public static function obtenerTodas()
    {
        $endpoint = "compras?select=*,cursos(titulo,instructor),metodos_pago(nombre)";
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "compras" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener todas las compras"
            ];
        }
    }

    /**
     * Obtener compras por curso
     */
    public static function obtenerPorCurso($id_curso)
    {
        $endpoint = "compras?select=*,metodos_pago(nombre)&id_curso=eq." . intval($id_curso);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "compras" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener compras del curso"
            ];
        }
    }

    /**
     * Verificar si un cliente ya compró un curso
     */
    public static function yaCompro($id_cliente, $id_curso)
    {
        $endpoint = "compras?select=id&id_cliente=eq." . intval($id_cliente) . "&id_curso=eq." . intval($id_curso);
        $respuesta = supabaseRequest($endpoint, "GET");

        return ($respuesta["status"] === 200 && count($respuesta["body"]) > 0);
    }

    /**
     * Obtener compra por ID
     */
    public static function obtenerPorId($id)
    {
        $endpoint = "compras?select=*,cursos(*),metodos_pago(nombre)&id=eq." . intval($id);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            return [
                "success" => true,
                "compra" => $respuesta["body"][0]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Compra no encontrada"
            ];
        }
    }

    /**
     * Obtener estadísticas de compras por cliente
     */
    public static function obtenerEstadisticasCliente($id_cliente)
    {
        $compras = self::obtenerPorCliente($id_cliente);

        if (!$compras["success"]) {
            return $compras;
        }

        $totalCompras = count($compras["compras"]);
        $totalGastado = array_sum(array_column($compras["compras"], 'precio_pagado'));
        $compraPromedio = $totalCompras > 0 ? $totalGastado / $totalCompras : 0;

        // Método de pago más usado
        $metodos = array_column($compras["compras"], 'metodos_pago');
        $metodosConteo = array_count_values(array_column($metodos, 'nombre'));
        $metodoFavorito = $metodosConteo ? array_keys($metodosConteo, max($metodosConteo))[0] : null;

        return [
            "success" => true,
            "estadisticas" => [
                "total_compras" => $totalCompras,
                "total_gastado" => round($totalGastado, 2),
                "compra_promedio" => round($compraPromedio, 2),
                "metodo_favorito" => $metodoFavorito
            ]
        ];
    }

    /**
     * Obtener ingresos de un creador de cursos
     */
    public static function obtenerIngresosPorCreador($id_creador)
    {
        // Obtener compras de cursos creados por este usuario
        $endpoint = "compras?select=precio_pagado,cursos!inner(id_creador)&cursos.id_creador=eq." . intval($id_creador);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            $ingresos = array_sum(array_column($respuesta["body"], 'precio_pagado'));
            $totalVentas = count($respuesta["body"]);

            return [
                "success" => true,
                "ingresos" => [
                    "total_ingresos" => round($ingresos, 2),
                    "total_ventas" => $totalVentas,
                    "ingreso_promedio" => $totalVentas > 0 ? round($ingresos / $totalVentas, 2) : 0
                ]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener ingresos"
            ];
        }
    }

    /**
     * Obtener compras en un rango de fechas
     */
    public static function obtenerPorRangoFechas($fechaInicio, $fechaFin)
    {
        $endpoint = "compras?select=*,cursos(titulo),metodos_pago(nombre)&fecha_compra=gte." . urlencode($fechaInicio) . "&fecha_compra=lte." . urlencode($fechaFin);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "compras" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener compras por fechas"
            ];
        }
    }

    /**
     * Cancelar/eliminar compra (reembolso)
     */
    public static function cancelar($id)
    {
        $respuesta = supabaseRequest("compras?id=eq." . intval($id), "DELETE");

        if ($respuesta["status"] === 200 || $respuesta["status"] === 204) {
            return [
                "success" => true,
                "mensaje" => "Compra cancelada exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al cancelar la compra"
            ];
        }
    }

    /**
     * Actualizar información de compra
     */
    public static function actualizar($id, $datos)
    {
        $respuesta = supabaseRequest("compras?id=eq." . intval($id), "PATCH", $datos);

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "mensaje" => "Compra actualizada exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al actualizar compra"
            ];
        }
    }
}