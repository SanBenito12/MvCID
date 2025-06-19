<?php
require_once '../includes/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $res = supabaseRequest("compras", "POST", $data);
        http_response_code($res["status"]);
        echo $res["raw"];
        break;

    case 'GET':
        $id_cliente = $_GET['id_cliente'] ?? null;
        if (!$id_cliente) {
            http_response_code(400);
            echo json_encode(["error" => "Falta id_cliente"]);
            exit;
        }
        $res = supabaseRequest("compras?select=*,cursos(*),metodos_pago(nombre)&id_cliente=eq.$id_cliente", "GET");
        echo json_encode($res["body"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
