<?php
declare(strict_types=1);

require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (!csrf_validate()) json_err("CSRF inválido", 403);

$con = db();
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$matchId = (int)($data["match_id"] ?? 0);
$tipo = (string)($data["tipo"] ?? "texto");
$mensagem = (string)($data["mensagem"] ?? "");
$imagem = (string)($data["imagem"] ?? "");
$vu = (int)($data["visualizacao_unica"] ?? 0);

if ($matchId <= 0) json_err("match_id inválido", 422);
if ($tipo !== "texto" && $tipo !== "imagem") json_err("tipo inválido", 422);

$chk = $con->prepare("SELECT id FROM matches WHERE id=? AND (user1_id=? OR user2_id=?) AND status='ativo' LIMIT 1");
$chk->bind_param("iii", $matchId, $meuId, $meuId);
$chk->execute();
if ($chk->get_result()->num_rows === 0) json_err("Acesso negado", 403);

if ($tipo === "texto" && trim($mensagem) === "") json_err("Mensagem vazia", 422);
if ($tipo === "imagem" && trim($imagem) === "") json_err("Imagem vazia", 422);

$sql = "INSERT INTO mensagens (match_id, remetente_id, tipo, mensagem, imagem, visualizacao_unica)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param("iisssi", $matchId, $meuId, $tipo, $mensagem, $imagem, $vu);
$stmt->execute();

json_ok(["id" => (int)$con->insert_id]);