<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_err("Método não permitido", 405);
if (!csrf_validate()) json_err("CSRF inválido", 403);

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$bio = isset($data['bio']) ? trim((string)$data['bio']) : '';
$fotos = isset($data['fotos']) && is_array($data['fotos']) ? $data['fotos'] : [];

$conn = db();

// atualiza bio
$sql = "UPDATE usuarios SET bio = ? WHERE id_usuarios = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $bio, $meuId);
$stmt->execute();

// fotos: limpa e recria (simples e seguro)
$conn->query("DELETE FROM fotos_perfil WHERE usuario_id = " . (int)$meuId);

$ordem = 1;
foreach ($fotos as $f) {
  if ($ordem > 6) break;
  $f = (string)$f;
  if ($f === '') continue;

  $sql2 = "INSERT INTO fotos_perfil (usuario_id, foto_url, ordem) VALUES (?, ?, ?)";
  $stmt2 = $conn->prepare($sql2);
  $stmt2->bind_param("isi", $meuId, $f, $ordem);
  $stmt2->execute();
  $ordem++;
}

json_ok();