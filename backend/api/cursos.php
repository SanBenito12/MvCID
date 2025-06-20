<?php
// backend/api/cursos.php
require_once __DIR__ . '/../includes/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id_creador = $_GET['id_creador'] ?? null;
        $url = $id_creador 
            ? "cursos?select=*&id_creador=eq.$id_creador" 
            : "cursos?select=*";
        $res = supabaseRequest($url, "GET");
        echo json_encode($res["body"]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $res = supabaseRequest("cursos", "POST", $data);
        http_response_code($res["status"]);
        echo $res["raw"];
        break;

    case 'PATCH':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Falta el ID"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $res = supabaseRequest("cursos?id=eq.$id", "PATCH", $data);
        http_response_code($res["status"]);
        echo $res["raw"];
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Falta el ID"]);
            exit;
        }
        $res = supabaseRequest("cursos?id=eq.$id", "DELETE");
        http_response_code($res["status"]);
        echo json_encode(["message" => "Curso eliminado"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
?>