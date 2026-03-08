<?php


require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (!csrf_validate()) json_err("CSRF inválido", 403);

$con = db();
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$mensagemId = (int)($data["mensagem_id"] ?? 0);
$motivo = (string)($data["motivo"] ?? "mensagem_inapropriada");
if ($mensagemId <= 0) json_err("mensagem_id inválido", 422);

$stmt = $con->prepare("INSERT IGNORE INTO denuncias_mensagens (mensagem_id, denunciante_id, motivo) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $mensagemId, $meuId, $motivo);
$stmt->execute();

json_ok();