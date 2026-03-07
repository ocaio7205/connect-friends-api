<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_err("Método não permitido", 405);
if (!csrf_validate()) json_err("CSRF inválido", 403);

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) json_err("JSON inválido", 422);

$postId = (int)($data['post_id'] ?? 0);
$comentario = trim((string)($data['comentario'] ?? ''));

if ($postId <= 0) json_err("Post inválido", 422);
if ($comentario === '') json_err("Comentário vazio", 422);

$conn = db();

// garante que o post é seu (seguindo seu comportamento atual: perfil só mostra seus posts)
$sql = "SELECT id FROM posts WHERE id = ? AND user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $postId, $meuId);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) json_err("Post não encontrado", 404);

$sql2 = "INSERT INTO comentarios (post_id, user_id, comentario) VALUES (?, ?, ?)";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("iis", $postId, $meuId, $comentario);
$stmt2->execute();

json_ok();