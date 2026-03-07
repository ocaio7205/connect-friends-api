<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_err("Método não permitido", 405);
if (!csrf_validate()) json_err("CSRF inválido", 403);

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$postId = (int)($data['post_id'] ?? 0);
if ($postId <= 0) json_err("Post inválido", 422);

$conn = db();

// toggle: se existe, remove; se não, insere
$sql = "SELECT id FROM curtidas WHERE post_id = ? AND user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $postId, $meuId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row) {
  $sql2 = "DELETE FROM curtidas WHERE post_id = ? AND user_id = ? LIMIT 1";
  $stmt2 = $conn->prepare($sql2);
  $stmt2->bind_param("ii", $postId, $meuId);
  $stmt2->execute();
} else {
  $sql2 = "INSERT INTO curtidas (post_id, user_id) VALUES (?, ?)";
  $stmt2 = $conn->prepare($sql2);
  $stmt2->bind_param("ii", $postId, $meuId);
  $stmt2->execute();
}

json_ok();