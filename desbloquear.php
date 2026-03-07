<?php
require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();

// Só aceitamos POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  http_response_code(405);
  exit('Método não permitido');
}

// CSRF
if (!csrf_validate()) {
  http_response_code(403);
  exit('CSRF inválido');
}

// valida o id do usuário que será desbloqueado
$usuarioId = (int)($_POST['usuario_id'] ?? 0);
if ($usuarioId <= 0) {
  http_response_code(422);
  exit('Usuário inválido');
}

$conn = db();

// Remove o bloqueio SOMENTE se ele foi criado por você (bloqueador_id = seu id)
$sql = "DELETE FROM bloqueios WHERE bloqueador_id = ? AND bloqueado_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $meuId, $usuarioId);
$stmt->execute();

// Volta pra tela de bloqueados
header("Location: bloqueados.php");
exit;