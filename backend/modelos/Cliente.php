<?php
// backend/modelos/Cliente.php - Actualizado con contraseñas
require_once __DIR__ . '/../includes/db.php';

class Cliente
{
    /**
     * Crear un nuevo cliente con contraseña
     */
    public static function crear($nombre, $apellido, $email, $password)
    {
        // Generar credenciales únicas
        $id_cliente = bin2hex(random_bytes(32));
        $llave_secreta = bin2hex(random_bytes(32));
        
        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $data = [
            "nombre" => $nombre,
            "apellido" => $apellido,
            "email" => $email,
            "password" => $password_hash,
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
     * Autenticar cliente con email y contraseña
     */
    public static function autenticar($email, $password)
    {
        // Obtener cliente por email
        $endpoint = "clientes?select=*&email=eq." . urlencode($email);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] === 200 && count($respuesta["body"]) > 0) {
            $cliente = $respuesta["body"][0];
            
            // Verificar contraseña
            if (password_verify($password, $cliente["password"])) {
                // Remover contraseña de la respuesta por seguridad
                unset($cliente["password"]);
                
                return [
                    "success" => true,
                    "cliente" => $cliente
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Contraseña incorrecta"
                ];
            }
        } else {
            return [
                "success" => false,
                "error" => "Usuario no encontrado"
            ];
        }
    }

    /**
     * Buscar cliente por email (sin contraseña en respuesta)
     */
    public static function obtenerPorEmail($email)
    {
        $endpoint = "clientes?select=id,nombre,apellido,email,id_cliente,llave_secreta,created_at,updated_at&email=eq." . urlencode($email);
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
     * Validar credenciales de cliente (sin cambios)
     */
    public static function validarCredenciales($id_cliente, $llave_secreta)
    {
        $endpoint = "clientes?select=id,nombre,apellido,email,id_cliente,llave_secreta,created_at,updated_at&id_cliente=eq." . urlencode($id_cliente) . "&llave_secreta=eq." . urlencode($llave_secreta);
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
     * Cambiar contraseña de un cliente
     */
    public static function cambiarPassword($id_cliente, $password_actual, $password_nueva)
    {
        // Obtener cliente con contraseña para verificar
        $endpoint = "clientes?select=*&id_cliente=eq." . urlencode($id_cliente);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] !== 200 || count($respuesta["body"]) === 0) {
            return [
                "success" => false,
                "error" => "Cliente no encontrado"
            ];
        }

        $cliente = $respuesta["body"][0];

        // Verificar contraseña actual
        if (!password_verify($password_actual, $cliente["password"])) {
            return [
                "success" => false,
                "error" => "La contraseña actual es incorrecta"
            ];
        }

        // Hashear nueva contraseña
        $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

        // Actualizar contraseña
        $datos = [
            "password" => $nuevo_hash,
            "updated_at" => date("c")
        ];

        $respuestaUpdate = supabaseRequest("clientes?id=eq." . intval($cliente["id"]), "PATCH", $datos);

        if ($respuestaUpdate["status"] === 200) {
            return [
                "success" => true,
                "mensaje" => "Contraseña actualizada exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al actualizar contraseña"
            ];
        }
    }

    /**
     * Resetear contraseña (para futuras funcionalidades)
     */
    public static function resetearPassword($email, $nueva_password)
    {
        $endpoint = "clientes?select=id&email=eq." . urlencode($email);
        $respuesta = supabaseRequest($endpoint, "GET");

        if ($respuesta["status"] !== 200 || count($respuesta["body"]) === 0) {
            return [
                "success" => false,
                "error" => "Email no encontrado"
            ];
        }

        $cliente = $respuesta["body"][0];
        $nuevo_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

        $datos = [
            "password" => $nuevo_hash,
            "updated_at" => date("c")
        ];

        $respuestaUpdate = supabaseRequest("clientes?id=eq." . intval($cliente["id"]), "PATCH", $datos);

        if ($respuestaUpdate["status"] === 200) {
            return [
                "success" => true,
                "mensaje" => "Contraseña reseteada exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "error" => "Error al resetear contraseña"
            ];
        }
    }

    /**
     * Obtener todos los clientes (sin contraseñas)
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
     * Obtener cliente por ID interno (sin contraseña)
     */
    public static function obtenerPorId($id)
    {
        $endpoint = "clientes?select=id,nombre,apellido,email,id_cliente,llave_secreta,created_at,updated_at&id=eq." . intval($id);
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
     * Actualizar información del cliente (sin incluir contraseña)
     */
    public static function actualizar($id, $datos)
    {
        // Remover campos sensibles que no se deben actualizar por este método
        unset($datos["password"], $datos["id_cliente"], $datos["llave_secreta"]);
        
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

    /**
     * Validar fuerza de contraseña
     */
    public static function validarPassword($password)
    {
        $errores = [];

        if (strlen($password) < 8) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errores[] = "La contraseña debe contener al menos una letra mayúscula";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errores[] = "La contraseña debe contener al menos una letra minúscula";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errores[] = "La contraseña debe contener al menos un número";
        }

        return [
            "valida" => empty($errores),
            "errores" => $errores
        ];
    }
}