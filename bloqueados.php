<?php
require_once __DIR__ . "/bootstrap.php";

$meuId = require_login(); // garante que está logado
$conn = db();

/**
 * Busca usuários que EU bloqueei
 */
$sql = "
    SELECT u.id_usuarios, u.username, u.foto_perfil, b.data_bloqueio
    FROM bloqueios b
    INNER JOIN usuarios u ON u.id_usuarios = b.bloqueado_id
    WHERE b.bloqueador_id = ?
    ORDER BY b.data_bloqueio DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meuId);
$stmt->execute();
$res = $stmt->get_result();

$bloqueados = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<style>
  .glass-card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.3s;
  }
  .custom-scroll::-webkit-scrollbar { width: 4px; }
  .custom-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
</style>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuários Bloqueados | Connect Friends</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="pb-10 custom-scroll bg-[#0f172a] text-white">

<div class="px-6 max-w-2xl mx-auto space-y-4 mt-10">

<?php if (empty($bloqueados)): ?>

    <div class="py-32 text-center space-y-6">
        <h3 class="text-slate-200 font-bold">Tudo limpo por aqui</h3>
        <p class="text-slate-500 text-xs mt-2 font-medium">Sua lista de bloqueados está vazia.</p>
    </div>

<?php else: ?>

    <?php foreach ($bloqueados as $user): ?>
        <div class="glass-card rounded-[2rem] p-4 flex items-center justify-between">

            <div class="flex items-center gap-4">

                <div class="w-14 h-14 rounded-2xl bg-slate-800 border border-white/10 flex items-center justify-center overflow-hidden shadow-inner">
                    <?php if (!empty($user['foto_perfil'])): ?>
                        <img src="<?= htmlspecialchars($user['foto_perfil']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fa-solid fa-user text-slate-600 text-xl"></i>
                    <?php endif; ?>
                </div>

                <div>
                    <h4 class="text-sm font-black text-white tracking-tight">
                        <?= htmlspecialchars($user['username']) ?>
                    </h4>
                    <span class="text-[9px] text-slate-500 uppercase">
                        Desde <?= date('d/m/Y', strtotime($user['data_bloqueio'])) ?>
                    </span>
                </div>
            </div>

            <form method="POST" action="desbloquear.php">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="usuario_id" value="<?= (int)$user['id_usuarios'] ?>">
                <button class="h-10 px-5 rounded-2xl bg-white/5 border border-white/10 text-[9px] font-black uppercase tracking-widest text-slate-300 hover:bg-blue-600 hover:text-white transition-all">
                    Desbloquear
                </button>
            </form>

        </div>
    <?php endforeach; ?>

<?php endif; ?>

</div>
</body>
</html>