<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

$conn = db();
$sql = "SELECT id, imagem, legenda, data_post
        FROM posts
        WHERE user_id = ?
        ORDER BY data_post DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meuId);
$stmt->execute();
$res = $stmt->get_result();

$posts = [];
while ($p = $res->fetch_assoc()) {
  $posts[] = [
    "id" => (int)$p["id"],
    "imagem" => (string)$p["imagem"],
    "legenda" => $p["legenda"],
    "data_post" => $p["data_post"],
  ];
}

json_ok(["posts" => $posts]);