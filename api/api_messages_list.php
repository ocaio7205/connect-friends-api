<?php
declare(strict_types=1);

require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

$con = db();
$matchId = (int)($_GET["match_id"] ?? 0);
if ($matchId <= 0) json_err("match_id inválido", 422);

$chk = $con->prepare("SELECT id FROM matches WHERE id=? AND (user1_id=? OR user2_id=?) LIMIT 1");
$chk->bind_param("iii", $matchId, $meuId, $meuId);
$chk->execute();
if ($chk->get_result()->num_rows === 0) json_err("Acesso negado", 403);

$sql = "SELECT id, remetente_id, tipo, mensagem, imagem, visualizacao_unica, data_envio
        FROM mensagens
        WHERE match_id=?
        ORDER BY data_envio ASC, id ASC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $matchId);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($m = $res->fetch_assoc()) {
  $out[] = [
    "id" => (int)$m["id"],
    "is_me" => ((int)$m["remetente_id"] === $meuId),
    "tipo" => (string)$m["tipo"],
    "mensagem" => (string)($m["mensagem"] ?? ""),
    "imagem" => (string)($m["imagem"] ?? ""),
    "visualizacao_unica" => (int)$m["visualizacao_unica"],
    "data_envio" => (string)$m["data_envio"],
  ];
}

json_ok(["messages" => $out]);