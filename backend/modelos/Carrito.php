<?php
// backend/modelos/Carrito.php
require_once __DIR__ . '/../includes/db.php';

class Carrito
{
    /**
     * Obtener o crear carrito para un cliente
     */
    public static function obtenerOCrear($id_cliente)
    {
        try {
            // Limpiar carritos duplicados primero
            self::limpiarCarritosDuplicados($id_cliente);

            // Ahora intentar obtener carrito existente (cualquier estado)
            $endpoint = "carritos?select=*&id_cliente=eq." . intval($id_cliente);
            $respuesta = supabaseRequest($endpoint, "GET");

            if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
                $carrito = $respuesta["body"][0];

                // Si el carrito está procesado o abandonado, reactivarlo
                if ($carrito["estado"] !== "activo") {
                    $actualizarCarrito = [
                        "estado" => "activo",
                        "fecha_actualizacion" => date("c")
                    ];

                    $respuestaUpdate = supabaseRequest("carritos?id=eq." . $carrito["id"], "PATCH", $actualizarCarrito);

                    if ($respuestaUpdate["status"] === 200) {
                        $carrito["estado"] = "activo";
                        $carrito["fecha_actualizacion"] = date("c");
                    }
                }

                return [
                    "success" => true,
                    "carrito" => $carrito,
                    "es_nuevo" => false
                ];
            }

            // Si no existe ningún carrito, crear uno nuevo
            $nuevoCarrito = [
                "id_cliente" => intval($id_cliente),
                "estado" => "activo",
                "total" => 0.00,
                "fecha_creacion" => date("c"),
                "fecha_actualizacion" => date("c")
            ];

            $respuestaCrear = supabaseRequest("carritos", "POST", $nuevoCarrito);

            if ($respuestaCrear["status"] === 201) {
                return [
                    "success" => true,
                    "carrito" => $respuestaCrear["body"][0] ?? $respuestaCrear["body"],
                    "es_nuevo" => true
                ];
            } else {
                // Si aún hay error de duplicado, significa que se creó entre medio
                if (strpos($respuestaCrear["raw"], "already exists") !== false ||
                    strpos($respuestaCrear["raw"], "23505") !== false) {

                    error_log("Carrito creado concurrentemente para cliente $id_cliente, reintentando obtención...");

                    // Esperar un poco y reintentar
                    usleep(100000); // 100ms

                    // Limpiar duplicados nuevamente
                    self::limpiarCarritosDuplicados($id_cliente);

                    // Reintento de obtención
                    $respuestaReintento = supabaseRequest($endpoint, "GET");

                    if ($respuestaReintento["status"] === 200 && count($respuestaReintento["body"]) > 0) {
                        $carrito = $respuestaReintento["body"][0];

                        // Asegurar que esté activo
                        if ($carrito["estado"] !== "activo") {
                            $actualizarCarrito = [
                                "estado" => "activo",
                                "fecha_actualizacion" => date("c")
                            ];

                            supabaseRequest("carritos?id=eq." . $carrito["id"], "PATCH", $actualizarCarrito);
                            $carrito["estado"] = "activo";
                        }

                        return [
                            "success" => true,
                            "carrito" => $carrito,
                            "es_nuevo" => false
                        ];
                    }
                }

                return [
                    "success" => false,
                    "error" => "Error al crear carrito",
                    "detalle" => $respuestaCrear["raw"]
                ];
            }
        } catch (Exception $e) {
            error_log("Error en obtenerOCrear carrito: " . $e->getMessage());
            return [
                "success" => false,
                "error" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener resumen del carrito (solo totales)
     */
    public static function obtenerResumen($id_cliente)
    {
        try {
            $carritoResult = self::obtenerOCrear($id_cliente);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];

            // Contar items
            $endpoint = "carrito_items?select=id&id_carrito=eq." . $carrito["id"];
            $respuesta = supabaseRequest($endpoint, "GET");

            $cantidad = ($respuesta["status"] === 200) ? count($respuesta["body"]) : 0;

            return [
                "success" => true,
                "resumen" => [
                    "cantidad_items" => $cantidad,
                    "total" => floatval($carrito["total"]),
                    "tiene_items" => $cantidad > 0
                ]
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener carrito con sus items
     */
    public static function obtenerConItems($id_cliente)
    {
        try {
            // Obtener carrito
            $carritoResult = self::obtenerOCrear($id_cliente);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];

            // Obtener items del carrito con información de cursos
            $endpoint = "carrito_items?select=*,cursos(id,titulo,instructor,descripcion,imagen,precio)&id_carrito=eq." . $carrito["id"];
            $respuesta = supabaseRequest($endpoint, "GET");

            if ($respuesta["status"] === 200) {
                $carrito["items"] = $respuesta["body"];
                $carrito["cantidad_items"] = count($respuesta["body"]);

                return [
                    "success" => true,
                    "carrito" => $carrito
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Error al obtener items del carrito"
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
     * Agregar curso al carrito
     */
    public static function agregarCurso($id_cliente, $id_curso)
    {
        try {
            // Obtener o crear carrito
            $carritoResult = self::obtenerOCrear($id_cliente);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];

            // Verificar si el curso ya está en el carrito
            $endpoint = "carrito_items?select=id&id_carrito=eq." . $carrito["id"] . "&id_curso=eq." . intval($id_curso);
            $verificar = supabaseRequest($endpoint, "GET");

            if ($verificar["status"] === 200 && count($verificar["body"]) > 0) {
                return [
                    "success" => false,
                    "error" => "El curso ya está en el carrito"
                ];
            }

            // Obtener información del curso para el precio
            $cursoEndpoint = "cursos?select=precio&id=eq." . intval($id_curso);
            $cursoResp = supabaseRequest($cursoEndpoint, "GET");

            if ($cursoResp["status"] !== 200 || count($cursoResp["body"]) === 0) {
                return [
                    "success" => false,
                    "error" => "Curso no encontrado"
                ];
            }

            $curso = $cursoResp["body"][0];

            // Agregar item al carrito
            $nuevoItem = [
                "id_carrito" => $carrito["id"],
                "id_curso" => intval($id_curso),
                "precio_curso" => floatval($curso["precio"])
            ];

            $respuesta = supabaseRequest("carrito_items", "POST", $nuevoItem);

            if ($respuesta["status"] === 201) {
                return [
                    "success" => true,
                    "mensaje" => "Curso agregado al carrito",
                    "item" => $respuesta["body"][0] ?? $respuesta["body"]
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Error al agregar curso al carrito",
                    "detalle" => $respuesta["raw"]
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
     * Eliminar curso del carrito
     */
    public static function eliminarCurso($id_cliente, $id_curso)
    {
        try {
            // Obtener carrito del cliente
            $carritoResult = self::obtenerOCrear($id_cliente);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];

            // Eliminar item específico
            $endpoint = "carrito_items?id_carrito=eq." . $carrito["id"] . "&id_curso=eq." . intval($id_curso);
            $respuesta = supabaseRequest($endpoint, "DELETE");

            if ($respuesta["status"] === 200 || $respuesta["status"] === 204) {
                return [
                    "success" => true,
                    "mensaje" => "Curso eliminado del carrito"
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Error al eliminar curso del carrito"
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
     * Vaciar carrito completamente
     */
    public static function vaciar($id_cliente)
    {
        try {
            // Obtener carrito del cliente
            $carritoResult = self::obtenerOCrear($id_cliente);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];

            // Eliminar todos los items
            $endpoint = "carrito_items?id_carrito=eq." . $carrito["id"];
            $respuesta = supabaseRequest($endpoint, "DELETE");

            if ($respuesta["status"] === 200 || $respuesta["status"] === 204) {
                return [
                    "success" => true,
                    "mensaje" => "Carrito vaciado"
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Error al vaciar carrito"
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
     * Procesar compra del carrito (convertir a compras individuales)
     */
    public static function procesarCompra($id_cliente, $id_metodo_pago)
    {
        try {
            // Obtener carrito con items
            $carritoResult = self::obtenerConItems($id_cliente);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];

            if (empty($carrito["items"])) {
                return [
                    "success" => false,
                    "error" => "El carrito está vacío"
                ];
            }

            $comprasCreadas = [];
            $errores = [];

            // Crear compra individual para cada item
            foreach ($carrito["items"] as $item) {
                $compraData = [
                    "id_cliente" => intval($id_cliente),
                    "id_curso" => $item["id_curso"],
                    "id_metodo_pago" => intval($id_metodo_pago),
                    "precio_pagado" => floatval($item["precio_curso"]),
                    "fecha_compra" => date("c")
                ];

                $respuestaCompra = supabaseRequest("compras", "POST", $compraData);

                if ($respuestaCompra["status"] === 201) {
                    $comprasCreadas[] = $respuestaCompra["body"][0] ?? $respuestaCompra["body"];
                } else {
                    $errores[] = "Error al procesar curso ID: " . $item["id_curso"];
                }
            }

            if (count($comprasCreadas) > 0) {
                // Marcar carrito como procesado
                $actualizarCarrito = [
                    "estado" => "procesado",
                    "fecha_actualizacion" => date("c")
                ];

                supabaseRequest("carritos?id=eq." . $carrito["id"], "PATCH", $actualizarCarrito);

                // Eliminar items del carrito
                supabaseRequest("carrito_items?id_carrito=eq." . $carrito["id"], "DELETE");

                return [
                    "success" => true,
                    "mensaje" => "Compra procesada exitosamente",
                    "compras_creadas" => count($comprasCreadas),
                    "total_pagado" => array_sum(array_column($comprasCreadas, 'precio_pagado')),
                    "errores" => $errores
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "No se pudo procesar ninguna compra",
                    "errores" => $errores
                ];
            }
        } catch (Exception $e) {
            error_log("Error en procesarCompra: " . $e->getMessage());
            return [
                "success" => false,
                "error" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas del carrito
     */
    public static function obtenerEstadisticas($id_cliente)
    {
        try {
            $carritoResult = self::obtenerConItems($id_cliente);
            if (!$carritoResult["success"]) {
                return $carritoResult;
            }

            $carrito = $carritoResult["carrito"];

            $estadisticas = [
                "cantidad_items" => $carrito["cantidad_items"],
                "total" => floatval($carrito["total"]),
                "precio_promedio" => $carrito["cantidad_items"] > 0 ?
                    floatval($carrito["total"]) / $carrito["cantidad_items"] : 0,
                "estado" => $carrito["estado"],
                "fecha_creacion" => $carrito["fecha_creacion"],
                "fecha_actualizacion" => $carrito["fecha_actualizacion"]
            ];

            return [
                "success" => true,
                "estadisticas" => $estadisticas
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si un curso está en el carrito
     */
    public static function tieneCurso($id_cliente, $id_curso)
    {
        try {
            $carritoResult = self::obtenerOCrear($id_cliente);
            if (!$carritoResult["success"]) {
                return false;
            }

            $carrito = $carritoResult["carrito"];

            $endpoint = "carrito_items?select=id&id_carrito=eq." . $carrito["id"] . "&id_curso=eq." . intval($id_curso);
            $respuesta = supabaseRequest($endpoint, "GET");

            return ($respuesta["status"] === 200 && count($respuesta["body"]) > 0);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Limpiar carritos duplicados para un cliente específico
     */
    public static function limpiarCarritosDuplicados($id_cliente)
    {
        try {
            // Obtener todos los carritos del cliente
            $endpoint = "carritos?select=*&id_cliente=eq." . intval($id_cliente) . "&order=id.asc";
            $respuesta = supabaseRequest($endpoint, "GET");

            if ($respuesta["status"] !== 200) {
                return [
                    "success" => false,
                    "error" => "Error al obtener carritos"
                ];
            }

            $carritos = $respuesta["body"];

            if (count($carritos) <= 1) {
                // No hay duplicados
                return [
                    "success" => true,
                    "mensaje" => "No hay carritos duplicados",
                    "eliminados" => 0
                ];
            }

            // Mantener el primer carrito (más antiguo) y eliminar los demás
            $carritoAMantener = $carritos[0];
            $carritosAEliminar = array_slice($carritos, 1);

            $eliminados = 0;
            foreach ($carritosAEliminar as $carrito) {
                // Eliminar items del carrito duplicado
                supabaseRequest("carrito_items?id_carrito=eq." . $carrito["id"], "DELETE");

                // Eliminar el carrito duplicado
                $deleteResponse = supabaseRequest("carritos?id=eq." . $carrito["id"], "DELETE");

                if ($deleteResponse["status"] >= 200 && $deleteResponse["status"] < 300) {
                    $eliminados++;
                }
            }

            return [
                "success" => true,
                "mensaje" => "Carritos duplicados limpiados",
                "eliminados" => $eliminados,
                "carrito_activo" => $carritoAMantener["id"]
            ];

        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Error limpiando duplicados: " . $e->getMessage()
            ];
        }
    }
}