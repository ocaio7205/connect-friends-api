<?php


require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

$con = db();
$stmt = $con->prepare("SELECT COUNT(*) AS total FROM likes_usuario WHERE usuario_destino=?");
$stmt->bind_param("i", $meuId);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()["total"];

json_ok(["total" => $total]);