<?php
declare(strict_types=1);

require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (!csrf_validate()) json_err("CSRF inválido", 403);

$con = db();
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$matchId = (int)($data["match_id"] ?? 0);
$bloqueadoId = (int)($data["bloqueado_id"] ?? 0);
if ($matchId <= 0 || $bloqueadoId <= 0) json_err("Dados inválidos", 422);

$chk = $con->prepare("SELECT id FROM matches WHERE id=? AND (user1_id=? OR user2_id=?) LIMIT 1");
$chk->bind_param("iii", $matchId, $meuId, $meuId);
$chk->execute();
if ($chk->get_result()->num_rows === 0) json_err("Acesso negado", 403);

$ins = $con->prepare("INSERT IGNORE INTO bloqueios (bloqueador_id, bloqueado_id) VALUES (?, ?)");
$ins->bind_param("ii", $meuId, $bloqueadoId);
$ins->execute();

$up = $con->prepare("UPDATE matches SET status='bloqueado' WHERE id=?");
$up->bind_param("i", $matchId);
$up->execute();

json_ok();