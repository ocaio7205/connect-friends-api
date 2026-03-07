<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

$postId = (int)($_GET['post_id'] ?? 0);
if ($postId <= 0) json_err("Post inválido", 422);

$conn = db();

// post (somente do usuário)
$sql = "SELECT id, imagem, legenda, data_post
        FROM posts
        WHERE id = ? AND user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $postId, $meuId);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) json_err("Post não encontrado", 404);

// curtidas
$sqlL = "SELECT COUNT(*) AS total FROM curtidas WHERE post_id = ?";
$stmt2 = $conn->prepare($sqlL);
$stmt2->bind_param("i", $postId);
$stmt2->execute();
$curtidas = (int)($stmt2->get_result()->fetch_assoc()['total'] ?? 0);

// comentarios
$comentarios = [];
$sqlC = "SELECT c.comentario, c.data_comentario, u.username
         FROM comentarios c
         INNER JOIN usuarios u ON u.id_usuarios = c.user_id
         WHERE c.post_id = ?
         ORDER BY c.data_comentario ASC";
$stmt3 = $conn->prepare($sqlC);
$stmt3->bind_param("i", $postId);
$stmt3->execute();
$resC = $stmt3->get_result();
while ($c = $resC->fetch_assoc()) {
  $comentarios[] = [
    "username" => (string)$c["username"],
    "comentario" => (string)$c["comentario"],
    "data" => $c["data_comentario"],
  ];
}

json_ok([
  "post" => [
    "id" => (int)$post["id"],
    "imagem" => (string)$post["imagem"],
    "legenda" => $post["legenda"],
    "data_post" => $post["data_post"],
    "curtidas" => $curtidas,
    "comentarios" => $comentarios,
  ]
]);