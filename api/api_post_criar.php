<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_err("Método não permitido", 405);
if (!csrf_validate()) json_err("CSRF inválido", 403);

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$imagem = trim((string)($data['imagem'] ?? ''));
$legenda = isset($data['legenda']) ? trim((string)$data['legenda']) : null;

if ($imagem === '') json_err("Imagem obrigatória", 422);

$conn = db();
$sql = "INSERT INTO posts (user_id, imagem, legenda) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $meuId, $imagem, $legenda);
$stmt->execute();

json_ok();