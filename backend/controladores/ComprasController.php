<?php
require_once __DIR__ . '/../includes/db.php';

class ComprasController {
    public function obtenerComprasPorCliente($id_cliente, $llave_secreta) {
        // Validar cliente
        $clienteRes = supabaseRequest("clientes?select=id&id_cliente=eq.$id_cliente&llave_secreta=eq.$llave_secreta", "GET");

        if ($clienteRes["status"] === 200 && count($clienteRes["body"]) > 0) {
            $id = $clienteRes["body"][0]["id"];

            // Traer compras del cliente, incluyendo info del curso y método
            $comprasRes = supabaseRequest(
                "compras?select=*,cursos(*),metodos_pago(nombre)&id_cliente=eq.$id",
                "GET"
            );

            if ($comprasRes["status"] === 200) {
                return $comprasRes["body"];
            } else {
                return ["error" => "No se pudieron obtener las compras"];
            }
        }

        return ["error" => "Credenciales inválidas"];
    }
}
