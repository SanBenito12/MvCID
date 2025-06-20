<?php
// backend/modelos/Curso.php
require_once __DIR__ . '/../includes/db.php';

class Curso
{
    /**
     * Crear un nuevo curso
     */
    public static function crear($datos)
    {
        $datos["created_at"] = date("c");
        $datos["updated_at"] = date("c");

        $respuesta = supabaseRequest("cursos", "POST", $datos);

        if ($respuesta["status"] === 201) {
            return [
                "success" => true,
                "mensaje" => "Curso creado exitosamente",
                "curso" => $respuesta["body"][0] ?? $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al crear curso",
                "detalle" => $respuesta["raw"]
            ];
        }
    }

    /**
     * Obtener todos los cursos
     */
    public static function obtenerTodos()
    {
        $respuesta = supabaseRequest("cursos?select=*", "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "cursos" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener cursos"
            ];
        }
    }

    /**
     * Obtener cursos por creador
     */
    public static function obtenerPorCreador($id_creador)
    {
        $endpoint = "cursos?select=*&id_creador=eq." . intval($id_creador);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "cursos" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener cursos del creador"
            ];
        }
    }

    /**
     * Obtener curso por ID
     */
    public static function obtenerPorId($id)
    {
        $endpoint = "cursos?select=*&id=eq." . intval($id);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            return [
                "success" => true,
                "curso" => $respuesta["body"][0]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Curso no encontrado"
            ];
        }
    }

    /**
     * Actualizar curso
     */
    public static function actualizar($id, $datos)
    {
        $datos["updated_at"] = date("c");
        $respuesta = supabaseRequest("cursos?id=eq." . intval($id), "PATCH", $datos);

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "mensaje" => "Curso actualizado exitosamente",
                "curso" => $respuesta["body"][0] ?? null
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al actualizar curso"
            ];
        }
    }

    /**
     * Eliminar curso
     */
    public static function eliminar($id)
    {
        $respuesta = supabaseRequest("cursos?id=eq." . intval($id), "DELETE");

        if ($respuesta["status"] === 200 || $respuesta["status"] === 204) {
            return [
                "success" => true,
                "mensaje" => "Curso eliminado exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al eliminar curso"
            ];
        }
    }

    /**
     * Verificar si el usuario es propietario del curso
     */
    public static function esPropiertario($id_curso, $id_creador)
    {
        $endpoint = "cursos?select=id&id=eq." . intval($id_curso) . "&id_creador=eq." . intval($id_creador);
        $respuesta = supabaseRequest($endpoint, "GET");

        return ($respuesta["status"] === 200 && count($respuesta["body"]) > 0);
    }

    /**
     * Buscar cursos por título
     */
    public static function buscarPorTitulo($titulo)
    {
        $endpoint = "cursos?select=*&titulo=ilike." . urlencode("%$titulo%");
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "cursos" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error en la búsqueda"
            ];
        }
    }

    /**
     * Obtener cursos por rango de precio
     */
    public static function obtenerPorRangoPrecio($precioMin, $precioMax)
    {
        $endpoint = "cursos?select=*&precio=gte." . floatval($precioMin) . "&precio=lte." . floatval($precioMax);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "cursos" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al filtrar por precio"
            ];
        }
    }

    /**
     * Obtener estadísticas de cursos por creador
     */
    public static function obtenerEstadisticasCreador($id_creador)
    {
        $endpoint = "cursos?select=id,precio&id_creador=eq." . intval($id_creador);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200) {
            $cursos = $respuesta["body"];
            $totalCursos = count($cursos);
            $totalIngresos = array_sum(array_column($cursos, 'precio'));
            $precioPromedio = $totalCursos > 0 ? $totalIngresos / $totalCursos : 0;

            return [
                "success" => true,
                "estadisticas" => [
                    "total_cursos" => $totalCursos,
                    "ingresos_potenciales" => $totalIngresos,
                    "precio_promedio" => round($precioPromedio, 2)
                ]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener estadísticas"
            ];
        }
    }

    /**
     * Obtener estadísticas de un curso específico (compras, ingresos)
     */
    public static function obtenerEstadisticasCurso($id_curso)
    {
        try {
            // Contar compras del curso
            $endpoint = "compras?select=count,precio_pagado&id_curso=eq." . intval($id_curso);
            $respuesta = supabaseRequest($endpoint, "GET");

            if ($respuesta["status"] === 200) {
                $compras = $respuesta["body"];
                $totalCompras = count($compras);
                $totalIngresos = array_sum(array_column($compras, 'precio_pagado'));

                return [
                    "success" => true,
                    "estadisticas" => [
                        "total_compras" => $totalCompras,
                        "total_ingresos" => round($totalIngresos, 2),
                        "tiene_compras" => $totalCompras > 0
                    ]
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Error al obtener estadísticas del curso"
                ];
            }
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener cursos disponibles para un cliente (que no ha creado ni comprado)
     */
    public static function obtenerDisponiblesParaCliente($id_cliente)
    {
        // Primero obtener todos los cursos
        $todosCursos = self::obtenerTodos();

        if (!$todosCursos["success"]) {
            return $todosCursos;
        }

        // Obtener cursos creados por el cliente
        $cursosCreados = self::obtenerPorCreador($id_cliente);
        $idsCreados = $cursosCreados["success"] ? array_column($cursosCreados["cursos"], 'id') : [];

        // Obtener cursos comprados por el cliente
        require_once __DIR__ . '/Compra.php';
        $comprasCliente = Compra::obtenerPorCliente($id_cliente);
        $idsComprados = $comprasCliente["success"] ? array_column($comprasCliente["compras"], 'id_curso') : [];

        // Filtrar cursos disponibles
        $cursosDisponibles = array_filter($todosCursos["cursos"], function($curso) use ($id_cliente, $idsCreados, $idsComprados) {
            return $curso['id_creador'] != $id_cliente &&
                !in_array($curso['id'], $idsCreados) &&
                !in_array($curso['id'], $idsComprados);
        });

        return [
            "success" => true,
            "cursos" => array_values($cursosDisponibles)
        ];
    }
}