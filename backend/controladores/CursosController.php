<?php
// backend/controladores/CursosController.php
require_once __DIR__ . '/../modelos/Curso.php';
require_once __DIR__ . '/../modelos/Cliente.php';

class CursosController
{
    /**
     * Crear un nuevo curso
     */
    public function crear($datos, $id_cliente, $llave_secreta)
    {
        // Validar credenciales del usuario
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validaciones de datos
        $camposRequeridos = ['titulo', 'instructor', 'descripcion', 'precio'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                return [
                    "success" => false,
                    "error" => "El campo '$campo' es requerido"
                ];
            }
        }

        // Validar precio
        if (!is_numeric($datos['precio']) || $datos['precio'] < 0) {
            return [
                "success" => false,
                "error" => "El precio debe ser un número válido mayor o igual a 0"
            ];
        }

        // Asignar el creador
        $datos['id_creador'] = $validacion["cliente"]["id"];

        // Crear el curso usando el modelo
        return Curso::crear($datos);
    }

    /**
     * Obtener cursos por cliente autenticado
     */
    public function obtenerCursosPorCliente($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Obtener cursos del creador
        return Curso::obtenerPorCreador($validacion["cliente"]["id"]);
    }

    /**
     * Obtener todos los cursos
     */
    public function obtenerTodos()
    {
        return Curso::obtenerTodos();
    }

    /**
     * Obtener curso por ID
     */
    public function obtenerPorId($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID de curso inválido"
            ];
        }

        return Curso::obtenerPorId($id);
    }

    /**
     * Actualizar curso
     */
    public function actualizar($id, $datos, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Validar ID del curso
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID de curso inválido"
            ];
        }

        // Verificar que el usuario es propietario del curso
        if (!Curso::esPropiertario($id, $validacion["cliente"]["id"])) {
            return [
                "success" => false,
                "error" => "No tienes permisos para actualizar este curso"
            ];
        }

        // Validar precio si se está actualizando
        if (isset($datos['precio'])) {
            if (!is_numeric($datos['precio']) || $datos['precio'] < 0) {
                return [
                    "success" => false,
                    "error" => "El precio debe ser un número válido mayor o igual a 0"
                ];
            }
        }

        return Curso::actualizar($id, $datos);
    }

    /**
     * Eliminar curso con cascade (elimina compras asociadas)
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

        // Validar ID del curso
        if (empty($id) || !is_numeric($id)) {
            return [
                "success" => false,
                "error" => "ID de curso inválido"
            ];
        }

        // Verificar que el usuario es propietario del curso
        if (!Curso::esPropiertario($id, $validacion["cliente"]["id"])) {
            return [
                "success" => false,
                "error" => "No tienes permisos para eliminar este curso"
            ];
        }

        try {
            // 🔍 VERIFICAR CUÁNTAS COMPRAS HAY ANTES DE ELIMINAR
            $estadisticas = Curso::obtenerEstadisticasCurso($id);
            $comprasCount = 0;
            $ingresos = 0;

            if ($estadisticas["success"]) {
                $comprasCount = $estadisticas["estadisticas"]["total_compras"];
                $ingresos = $estadisticas["estadisticas"]["total_ingresos"];
            }

            // 1. ELIMINAR COMPRAS ASOCIADAS PRIMERO (CASCADE DELETE)
            $eliminarCompras = supabaseRequest("compras?id_curso=eq.$id", "DELETE");

            if ($eliminarCompras["status"] >= 200 && $eliminarCompras["status"] < 300) {
                // Compras eliminadas exitosamente (o no había ninguna)
                error_log("Compras asociadas al curso $id eliminadas. Total: $comprasCount");
            } else {
                // Log pero no fallar - puede que no haya compras
                error_log("Advertencia: No se pudieron eliminar compras del curso $id. Status: " . $eliminarCompras["status"]);
            }

            // 2. ELIMINAR EL CURSO
            $eliminarCurso = Curso::eliminar($id);

            if ($eliminarCurso["success"]) {
                $mensaje = "Curso eliminado exitosamente";

                if ($comprasCount > 0) {
                    $mensaje .= " junto con $comprasCount compra(s) asociada(s)";
                    if ($ingresos > 0) {
                        $mensaje .= " (total: $ingresos)";
                    }
                }

                return [
                    "success" => true,
                    "mensaje" => $mensaje,
                    "compras_eliminadas" => $comprasCount,
                    "ingresos_perdidos" => $ingresos
                ];
            } else {
                return $eliminarCurso;
            }

        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error al eliminar curso y compras asociadas: " . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar cursos por título
     */
    public function buscarPorTitulo($titulo)
    {
        if (empty($titulo)) {
            return [
                "success" => false,
                "error" => "Título requerido para búsqueda"
            ];
        }

        return Curso::buscarPorTitulo($titulo);
    }

    /**
     * Obtener cursos por rango de precio
     */
    public function obtenerPorRangoPrecio($precioMin, $precioMax)
    {
        // Validaciones
        if (!is_numeric($precioMin) || !is_numeric($precioMax)) {
            return [
                "success" => false,
                "error" => "Los precios deben ser números válidos"
            ];
        }

        if ($precioMin < 0 || $precioMax < 0) {
            return [
                "success" => false,
                "error" => "Los precios no pueden ser negativos"
            ];
        }

        if ($precioMin > $precioMax) {
            return [
                "success" => false,
                "error" => "El precio mínimo no puede ser mayor al precio máximo"
            ];
        }

        return Curso::obtenerPorRangoPrecio($precioMin, $precioMax);
    }

    /**
     * Obtener cursos disponibles para comprar (cliente autenticado)
     */
    public function obtenerDisponiblesParaCliente($id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        return Curso::obtenerDisponiblesParaCliente($validacion["cliente"]["id"]);
    }

    /**
     * Obtener estadísticas de cursos del creador
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

        $estadisticasCursos = Curso::obtenerEstadisticasCreador($validacion["cliente"]["id"]);

        // Obtener también estadísticas de ingresos
        require_once __DIR__ . '/../modelos/Compra.php';
        $ingresos = Compra::obtenerIngresosPorCreador($validacion["cliente"]["id"]);

        return [
            "success" => true,
            "estadisticas" => [
                "cursos" => $estadisticasCursos["success"] ? $estadisticasCursos["estadisticas"] : null,
                "ingresos" => $ingresos["success"] ? $ingresos["ingresos"] : null
            ]
        ];
    }

    /**
     * Verificar si un cliente puede editar un curso
     */
    public function puedeEditar($id_curso, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        $esPropiertario = Curso::esPropiertario($id_curso, $validacion["cliente"]["id"]);

        return [
            "success" => true,
            "puede_editar" => $esPropiertario
        ];
    }

    /**
     * Obtener cursos por creador usando ID directo
     */
    public function obtenerCursosPorCreador($id_creador)
    {
        if (empty($id_creador) || !is_numeric($id_creador)) {
            return [
                "success" => false,
                "error" => "ID de creador inválido"
            ];
        }

        return Curso::obtenerPorCreador($id_creador);
    }

    /**
     * Duplicar curso (crear copia)
     */
    public function duplicar($id_curso, $id_cliente, $llave_secreta)
    {
        // Validar credenciales
        $validacion = Cliente::validarCredenciales($id_cliente, $llave_secreta);

        if (!$validacion["success"]) {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }

        // Obtener curso original
        $cursoOriginal = Curso::obtenerPorId($id_curso);

        if (!$cursoOriginal["success"]) {
            return $cursoOriginal;
        }

        // Crear copia con modificaciones
        $datosCopia = $cursoOriginal["curso"];
        unset($datosCopia["id"], $datosCopia["created_at"], $datosCopia["updated_at"]);

        $datosCopia["titulo"] = "Copia de " . $datosCopia["titulo"];
        $datosCopia["id_creador"] = $validacion["cliente"]["id"];

        return Curso::crear($datosCopia);
    }
}