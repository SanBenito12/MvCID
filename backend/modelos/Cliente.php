<?php
// backend/modelos/Cliente.php
require_once __DIR__ . '/../includes/db.php';

class Cliente
{
    /**
     * Crear un nuevo cliente
     */
    public static function crear($nombre, $apellido, $email)
    {
        // Generar credenciales únicas
        $id_cliente = bin2hex(random_bytes(32));
        $llave_secreta = bin2hex(random_bytes(32));

        $data = [
            "nombre" => $nombre,
            "apellido" => $apellido,
            "email" => $email,
            "id_cliente" => $id_cliente,
            "llave_secreta" => $llave_secreta,
            "created_at" => date("c"),
            "updated_at" => date("c")
        ];

        $respuesta = supabaseRequest("clientes", "POST", $data);

        if ($respuesta["status"] === 201) {
            return [
                "success" => true,
                "mensaje" => "Cliente creado con éxito",
                "id_cliente" => $id_cliente,
                "llave_secreta" => $llave_secreta,
                "cliente" => $respuesta["body"][0] ?? $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al registrar cliente",
                "detalle" => $respuesta["raw"]
            ];
        }
    }

    /**
     * Buscar cliente por email
     */
    public static function obtenerPorEmail($email)
    {
        $endpoint = "clientes?select=*&email=eq." . urlencode($email);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            return [
                "success" => true,
                "cliente" => $respuesta["body"][0]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Cliente no encontrado"
            ];
        }
    }

    /**
     * Validar credenciales de cliente
     */
    public static function validarCredenciales($id_cliente, $llave_secreta)
    {
        $endpoint = "clientes?select=*&id_cliente=eq." . urlencode($id_cliente) . "&llave_secreta=eq." . urlencode($llave_secreta);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            return [
                "success" => true,
                "cliente" => $respuesta["body"][0]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Credenciales inválidas"
            ];
        }
    }

    /**
     * Verificar si un email ya existe
     */
    public static function emailExiste($email)
    {
        $endpoint = "clientes?select=id&email=eq." . urlencode($email);
        $respuesta = supabaseRequest($endpoint, "GET");

        return ($respuesta["status"] === 200 && count($respuesta["body"]) > 0);
    }

    /**
     * Obtener todos los clientes
     */
    public static function obtenerTodos()
    {
        $respuesta = supabaseRequest("clientes?select=id,email,nombre,apellido,created_at", "GET");

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "clientes" => $respuesta["body"]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al obtener clientes"
            ];
        }
    }

    /**
     * Obtener cliente por ID interno
     */
    public static function obtenerPorId($id)
    {
        $endpoint = "clientes?select=*&id=eq." . intval($id);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            return [
                "success" => true,
                "cliente" => $respuesta["body"][0]
            ];
        } else {
            return [
                "success" => false,
                "error" => "Cliente no encontrado"
            ];
        }
    }

    /**
     * Actualizar información del cliente
     */
    public static function actualizar($id, $datos)
    {
        $datos["updated_at"] = date("c");
        $respuesta = supabaseRequest("clientes?id=eq." . intval($id), "PATCH", $datos);

        if ($respuesta["status"] === 200) {
            return [
                "success" => true,
                "mensaje" => "Cliente actualizado exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al actualizar cliente"
            ];
        }
    }

    /**
     * Eliminar cliente (soft delete sería mejor en producción)
     */
    public static function eliminar($id)
    {
        $respuesta = supabaseRequest("clientes?id=eq." . intval($id), "DELETE");

        if ($respuesta["status"] === 200 || $respuesta["status"] === 204) {
            return [
                "success" => true,
                "mensaje" => "Cliente eliminado exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al eliminar cliente"
            ];
        }
    }
}