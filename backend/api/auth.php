<?php
require_once '../includes/db.php';

header("Content-Type: application/json");

$id_cliente = $_GET['id_cliente'] ?? null;
$llave_secreta = $_GET['llave_secreta'] ?? null;

if (!$id_cliente || !$llave_secreta) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos"]);
    exit;
}

$res = supabaseRequest("clientes?select=id&id_cliente=eq.$id_cliente&llave_secreta=eq.$llave_secreta", "GET");

if ($res["status"] === 200 && count($res["body"]) > 0) {
    echo json_encode(["valido" => true, "id_creador" => $res["body"][0]["id"]]);
} else {
    echo json_encode(["valido" => false]);
}
