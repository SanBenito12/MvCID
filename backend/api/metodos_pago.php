<?php
// backend/api/metodos_pago.php
require_once __DIR__ . '/../includes/db.php';

header("Content-Type: application/json");

$res = supabaseRequest("metodos_pago?select=*", "GET");
echo json_encode($res["body"]);
?>