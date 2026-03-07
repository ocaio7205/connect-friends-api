<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_err("Método não permitido", 405);
if (!csrf_validate()) json_err("CSRF inválido", 403);

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$foto = trim((string)($data['foto_perfil'] ?? ''));
if ($foto === '') json_err("Foto inválida", 422);

$conn = db();
$sql = "UPDATE usuarios SET foto_perfil = ? WHERE id_usuarios = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $foto, $meuId);
$stmt->execute();

json_ok();