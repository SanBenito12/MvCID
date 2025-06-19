<?php
require_once '../includes/db.php';

header("Content-Type: application/json");

$res = supabaseRequest("metodos_pago?select=*", "GET");
echo json_encode($res["body"]);
