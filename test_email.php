<?php


require_once __DIR__ . '/bootstrap.php';

// Troque pelo seu e-mail para testar
$to = $_GET['to'] ?? '';
if ($to === '') {
  echo "Passe ?to=seuemail@dominio.com na URL";
  exit;
}

$r = send_email((string)$to, 'Teste SMTP - Connect Friends', '<b>Funcionou!</b>');

echo "Resultado: ";
var_dump($r);
