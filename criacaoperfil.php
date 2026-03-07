<?php
declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();
$conn  = db();

$erro = "";

/**
 * POST: salva perfil no banco
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf_token'] ?? '')) {
    $erro = "Falha de segurança (CSRF). Recarregue a página e tente novamente.";
  } else {
    $bio         = trim((string)($_POST['bio'] ?? ''));
    $genero      = trim((string)($_POST['genero'] ?? ''));
    $sexualidade = trim((string)($_POST['sexualidade'] ?? ''));

    // Interesses vêm como array
    $interesses = $_POST['interesses'] ?? [];
    if (!is_array($interesses)) $interesses = [];

    // validações básicas
    if ($bio === '' || mb_strlen($bio) < 3) {
      $erro = "Preencha sua bio.";
    } elseif (count($interesses) < 1) {
      $erro = "Selecione pelo menos 1 interesse.";
    } else {

      // 1) Atualiza dados do usuário
      $sql = "UPDATE usuarios SET bio = ?, genero = ?, orientacao = ? WHERE id_usuarios = ? LIMIT 1";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssi", $bio, $genero, $sexualidade, $meuId);
      $stmt->execute();

      // 2) Salva interesses (tabela interesses + usuarios_interesses)
      //    - garantimos que existe em "interesses"
      //    - depois ligamos em "usuarios_interesses"
      $conn->begin_transaction();

      try {
        // apaga interesses antigos desse usuário (se tiver)
        $stmtDel = $conn->prepare("DELETE FROM usuarios_interesses WHERE usuario_id = ?");
        $stmtDel->bind_param("i", $meuId);
        $stmtDel->execute();

        // prepara statements
        $stmtInsInteresse = $conn->prepare("INSERT IGNORE INTO interesses (nome) VALUES (?)");
        $stmtSelInteresse = $conn->prepare("SELECT id FROM interesses WHERE nome = ? LIMIT 1");
        $stmtLink = $conn->prepare("INSERT IGNORE INTO usuarios_interesses (usuario_id, interesse_id) VALUES (?, ?)");

        foreach ($interesses as $nomeInteresse) {
          $nomeInteresse = trim((string)$nomeInteresse);
          if ($nomeInteresse === '' || mb_strlen($nomeInteresse) > 50) continue;

          $stmtInsInteresse->bind_param("s", $nomeInteresse);
          $stmtInsInteresse->execute();

          $stmtSelInteresse->bind_param("s", $nomeInteresse);
          $stmtSelInteresse->execute();
          $res = $stmtSelInteresse->get_result();
          $row = $res->fetch_assoc();
          if (!$row) continue;

          $iid = (int)$row['id'];
          $stmtLink->bind_param("ii", $meuId, $iid);
          $stmtLink->execute();
        }

        // 3) Salva fotos (foto_blob) em fotos_perfil (ordem 1..6)
        //    - apaga fotos antigas e insere novas
        $stmtDelFotos = $conn->prepare("DELETE FROM fotos_perfil WHERE usuario_id = ?");
        $stmtDelFotos->bind_param("i", $meuId);
        $stmtDelFotos->execute();

        $stmtInsFoto = $conn->prepare("
          INSERT INTO fotos_perfil (usuario_id, foto_blob, ordem)
          VALUES (?, ?, ?)
        ");

        $countFotos = 0;
        for ($i = 1; $i <= 6; $i++) {
          $key = "foto_$i";
          if (empty($_FILES[$key]) || ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
          }

          if (($_FILES[$key]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            continue;
          }

          // Limite 2MB por foto (igual seu JS)
          if (($_FILES[$key]['size'] ?? 0) > 2 * 1024 * 1024) {
            continue;
          }

          $tmp = (string)$_FILES[$key]['tmp_name'];
          $mime = mime_content_type($tmp) ?: '';

          // aceita só imagens
          if (strpos($mime, 'image/') !== 0) {
            continue;
          }

          $blob = file_get_contents($tmp);
          if ($blob === false || $blob === '') continue;

          $ordem = $i;
          // "b" para blob no bind_param (passa como string mesmo)
          $stmtInsFoto->bind_param("isi", $meuId, $blob, $ordem);
          $stmtInsFoto->send_long_data(1, $blob);
          $stmtInsFoto->execute();

          $countFotos++;
        }

        if ($countFotos < 2) {
          // volta tudo, pois você exige no mínimo 2 fotos
          $conn->rollback();
          $erro = "Selecione pelo menos 2 fotos (até 2MB cada).";
        } else {
          // 4) Define foto principal no usuarios.foto_perfil (ordem 1)
          //    OBS: por enquanto vamos guardar um marcador simples.
          //    Depois a gente cria um endpoint pra servir a foto_blob como imagem.
          $fotoPrincipal = "blob:ordem1";
          $stmtMain = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id_usuarios = ? LIMIT 1");
          $stmtMain->bind_param("si", $fotoPrincipal, $meuId);
          $stmtMain->execute();

          $conn->commit();

          // redireciona
          header("Location: index.php");
          exit;
        }

      } catch (Throwable $e) {
        $conn->rollback();
        $erro = "Erro ao salvar perfil. Tente novamente.";
      }
    }
  }
}

// CSRF para o formulário
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connect Friends | Criar Perfil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        :root {
            --brand-gradient: linear-gradient(135deg, #2dd4bf 0%, #3b82f6 50%, #a855f7 100%);
            --dark-bg: #0f172a;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--dark-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #f1f5f9;
        }

        .gradient-text { 
            background: var(--brand-gradient); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #334155;
            background: rgba(15, 23, 42, 0.6);
            color: white;
        }

        .input-field:focus { border-color: #3b82f6; outline: none; }

        .custom-check { display: none; }
        .custom-label {
            cursor: pointer;
            padding: 10px 16px;
            border-radius: 14px;
            background: rgba(51, 65, 85, 0.4);
            border: 1px solid #334155;
            font-size: 11px;
            font-weight: 700;
            transition: all 0.2s;
            text-transform: uppercase;
            text-align: center;
            display: block;
        }

        .custom-check:checked + .custom-label {
            background: var(--brand-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            transform: scale(1.05);
        }

        .photo-slot {
            aspect-ratio: 2/3;
            background: rgba(15, 23, 42, 0.6);
            border: 2px dashed #334155;
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-slot:hover { border-color: #3b82f6; background: rgba(15, 23, 42, 0.8); }
        
        .photo-slot img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            display: none; 
        }
        
        .photo-slot.has-image { border-style: solid; border-color: #3b82f6; }
        .photo-slot.has-image img { display: block; }
        .photo-slot.has-image .upload-icon { display: none; }

        .add-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--brand-gradient);
            width: 24px;
            height: 24px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            border: 2px solid #1e293b;
        }

        .btn-premium {
            background: var(--brand-gradient);
            background-size: 200% auto;
            transition: 0.5s;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-premium:hover { background-position: right center; transform: translateY(-2px); }
        .btn-premium:disabled { opacity: 0.5; cursor: not-allowed; }

        .fade-up { animation: fadeUp 0.6s ease-out forwards; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="fixed top-0 left-0 w-full h-full z-[-1] overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[400px] h-[400px] bg-blue-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] bg-purple-600/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-[540px] fade-up my-10">
        <div class="glass-card rounded-[3rem] p-8 lg:p-12">
            
            <div class="text-center mb-10">
                <h1 class="text-3xl font-black italic tracking-tighter gradient-text">Configure seu Perfil</h1>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.3em] mt-2">Escolha no mínimo 2 fotos</p>

                <?php if ($erro !== ''): ?>
                  <div class="mt-5 text-red-300 text-xs font-bold">
                    <?= htmlspecialchars($erro) ?>
                  </div>
                <?php endif; ?>
            </div>

            <form id="form-criar" method="POST" enctype="multipart/form-data" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div class="grid grid-cols-3 gap-3 mb-10">
                    <div class="photo-slot col-span-2 row-span-2" onclick="triggerInput(1)" id="slot-1">
                        <div class="upload-icon absolute inset-0 flex flex-col items-center justify-center text-slate-600">
                            <i class="fa-solid fa-plus text-2xl mb-2"></i>
                            <span class="text-[8px] font-bold uppercase tracking-widest">Principal</span>
                        </div>
                        <img id="img-1">
                        <div class="add-badge"><i class="fa-solid fa-camera"></i></div>
                    </div>

                    <div class="photo-slot" onclick="triggerInput(2)" id="slot-2">
                        <div class="upload-icon absolute inset-0 flex items-center justify-center text-slate-600"><i class="fa-solid fa-plus"></i></div>
                        <img id="img-2"><div class="add-badge"><i class="fa-solid fa-camera"></i></div>
                    </div>
                    <div class="photo-slot" onclick="triggerInput(3)" id="slot-3">
                        <div class="upload-icon absolute inset-0 flex items-center justify-center text-slate-600"><i class="fa-solid fa-plus"></i></div>
                        <img id="img-3"><div class="add-badge"><i class="fa-solid fa-camera"></i></div>
                    </div>
                    <div class="photo-slot" onclick="triggerInput(4)" id="slot-4">
                        <div class="upload-icon absolute inset-0 flex items-center justify-center text-slate-600"><i class="fa-solid fa-plus"></i></div>
                        <img id="img-4"><div class="add-badge"><i class="fa-solid fa-camera"></i></div>
                    </div>
                    <div class="photo-slot" onclick="triggerInput(5)" id="slot-5">
                        <div class="upload-icon absolute inset-0 flex items-center justify-center text-slate-600"><i class="fa-solid fa-plus"></i></div>
                        <img id="img-5"><div class="add-badge"><i class="fa-solid fa-camera"></i></div>
                    </div>
                    <div class="photo-slot" onclick="triggerInput(6)" id="slot-6">
                        <div class="upload-icon absolute inset-0 flex items-center justify-center text-slate-600"><i class="fa-solid fa-plus"></i></div>
                        <img id="img-6"><div class="add-badge"><i class="fa-solid fa-camera"></i></div>
                    </div>
                </div>

                <div class="hidden">
                    <input type="file" name="foto_1" id="file-1" accept="image/*" onchange="processImage(this, 1)">
                    <input type="file" name="foto_2" id="file-2" accept="image/*" onchange="processImage(this, 2)">
                    <input type="file" name="foto_3" id="file-3" accept="image/*" onchange="processImage(this, 3)">
                    <input type="file" name="foto_4" id="file-4" accept="image/*" onchange="processImage(this, 4)">
                    <input type="file" name="foto_5" id="file-5" accept="image/*" onchange="processImage(this, 5)">
                    <input type="file" name="foto_6" id="file-6" accept="image/*" onchange="processImage(this, 6)">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">Bio / O que você busca?</label>
                    <textarea name="bio" id="user-bio" required placeholder="Ex: Apaixonado por tecnologia e café. Busco novas amizades..." 
                              class="w-full px-6 py-4 rounded-[1.5rem] input-field font-medium text-sm h-32 resize-none leading-relaxed"></textarea>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">Seu Gênero</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <input type="radio" name="genero" id="gen-masc" value="Masculino" class="custom-check" checked>
                        <label for="gen-masc" class="custom-label">Masculino</label>
                        <input type="radio" name="genero" id="gen-fem" value="Feminino" class="custom-check">
                        <label for="gen-fem" class="custom-label">Feminino</label>
                        <input type="radio" name="genero" id="gen-nb" value="Não-Binário" class="custom-check">
                        <label for="gen-nb" class="custom-label">Não-Binário</label>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">Orientação Sexual</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <input type="radio" name="sexualidade" id="sex-hetero" value="Heterossexual" class="custom-check" checked>
                        <label for="sex-hetero" class="custom-label">Hetero</label>
                        <input type="radio" name="sexualidade" id="sex-gay" value="Gay" class="custom-check">
                        <label for="sex-gay" class="custom-label">Gay</label>
                        <input type="radio" name="sexualidade" id="sex-lesb" value="Lésbica" class="custom-check">
                        <label for="sex-lesb" class="custom-label">Lésbica</label>
                        <input type="radio" name="sexualidade" id="sex-bi" value="Bissexual" class="custom-check">
                        <label for="sex-bi" class="custom-label">Bi</label>
                        <input type="radio" name="sexualidade" id="sex-pan" value="Pansexual" class="custom-check">
                        <label for="sex-pan" class="custom-label">Pan</label>
                        <input type="radio" name="sexualidade" id="sex-outros" value="Outros" class="custom-check">
                        <label for="sex-outros" class="custom-label">Outros</label>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">Interesses (Selecione 1+)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="interesses-grid">
                        <input type="checkbox" name="interesses[]" id="int-1" value="Games" class="custom-check"><label for="int-1" class="custom-label">🎮 Games</label>
                        <input type="checkbox" name="interesses[]" id="int-2" value="Música" class="custom-check"><label for="int-2" class="custom-label">🎵 Música</label>
                        <input type="checkbox" name="interesses[]" id="int-3" value="Esportes" class="custom-check"><label for="int-3" class="custom-label">🏀 Esportes</label>
                        <input type="checkbox" name="interesses[]" id="int-4" value="Tech" class="custom-check"><label for="int-4" class="custom-label">💻 Tech</label>
                        <input type="checkbox" name="interesses[]" id="int-5" value="Filmes" class="custom-check"><label for="int-5" class="custom-label">🍿 Filmes</label>
                        <input type="checkbox" name="interesses[]" id="int-6" value="Leitura" class="custom-check"><label for="int-6" class="custom-label">📚 Leitura</label>
                    </div>
                </div>

                <button type="submit" id="btn-finalizar" class="btn-premium w-full py-6 text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.4em] mt-6">
                    Finalizar e Começar
                </button>
            </form>
        </div>
    </div>

    <script>
        let fotosArray = [null, null, null, null, null, null];

        function triggerInput(index) {
            document.getElementById(`file-${index}`).click();
        }

        function processImage(input, index) {
            const file = input.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert("A imagem é muito grande! Escolha uma imagem de até 2MB.");
                    input.value = "";
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(`img-${index}`);
                    const slot = document.getElementById(`slot-${index}`);
                    preview.src = e.target.result;
                    slot.classList.add('has-image');
                    fotosArray[index-1] = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        document.getElementById('form-criar').addEventListener('submit', function(e) {
            const validPhotos = fotosArray.filter(f => f !== null);
            if(validPhotos.length < 2) {
                e.preventDefault();
                alert("Por favor, selecione pelo menos 2 fotos!");
                return;
            }

            const interessesMarcados = document.querySelectorAll('#interesses-grid input[type="checkbox"]:checked').length;
            if(interessesMarcados < 1) {
                e.preventDefault();
                alert("Selecione pelo menos 1 interesse!");
                return;
            }

            const btn = document.getElementById('btn-finalizar');
            btn.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin text-lg"></i>';
            btn.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>