<<<<<<< HEAD
<?php
require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();
$conn = db();

// Carrega preferências do usuário (preferencias_usuario)
$sqlPref = "SELECT min_fotos, apenas_com_bio
            FROM preferencias_usuario
            WHERE usuario_id = ? LIMIT 1";
$stmt = $conn->prepare($sqlPref);
$stmt->bind_param("i", $meuId);
$stmt->execute();
$pref = $stmt->get_result()->fetch_assoc() ?: [
  'min_fotos' => 1,
  'apenas_com_bio' => 0,
];

// Carrega configurações (configuracoes_usuario) - se você usar
$sqlCfg = "SELECT notificacoes, perfil_publico
           FROM configuracoes_usuario
           WHERE id_usuario = ? LIMIT 1";
$stmt2 = $conn->prepare($sqlCfg);
$stmt2->bind_param("i", $meuId);
$stmt2->execute();
$cfg = $stmt2->get_result()->fetch_assoc() ?: [
  'notificacoes' => 1,
  'perfil_publico' => 1,
];

$csrf = csrf_token();

// valores iniciais
$minFotos = (int)($pref['min_fotos'] ?? 1);
$comBio  = (int)($pref['apenas_com_bio'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Connect Friends</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #0f172a; 
            color: white;
            overflow-x: hidden;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .range-input {
            -webkit-appearance: none;
            width: 100%;
            height: 4px;
            background: #1e293b;
            border-radius: 10px;
            outline: none;
        }

        .range-input::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: #3b82f6;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }

        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

        #modal-opcoes { backdrop-filter: blur(20px); transition: all 0.3s ease; }
    </style>
</head>
<body class="pb-10 custom-scroll">

    <div class="flex items-center justify-between px-6 py-6 sticky top-0 bg-[#0f172a]/90 backdrop-blur-lg z-50">
        <button onclick="window.location.href='index.php'" class="text-slate-400 hover:text-white transition active:scale-90">
            <i class="fa-solid fa-chevron-left text-xl"></i>
        </button>
        <h2 class="text-lg font-black tracking-tight">Configurações</h2>
        <button onclick="window.location.href='index.php'" class="text-blue-500 font-black text-sm uppercase tracking-widest hover:text-blue-400 active:scale-90">OK</button>
    </div>

    <div class="px-6 space-y-6 max-w-2xl mx-auto">
        
        <div class="glass-card rounded-[2.5rem] p-8 space-y-8">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-300">Número mínimo de fotos</span>
                    <span id="val-fotos" class="text-blue-400 font-black text-lg"><?= (int)$minFotos ?></span>
                </div>
                <input id="range-fotos" type="range" min="1" max="6" value="<?= (int)$minFotos ?>" class="range-input"
                       oninput="document.getElementById('val-fotos').innerText = this.value; salvarMinFotos(this.value)">
            </div>

            <div class="flex justify-between items-center pt-2">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-300">Apenas perfis com bio</span>
                    <span class="text-[10px] text-slate-500 uppercase font-black">Melhora a conexão</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="check-bio" class="sr-only peer" onchange="salvarComBio(this.checked)"
                           <?= $comBio ? 'checked' : '' ?>>
                    <div class="w-12 h-6 bg-slate-800 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6 shadow-inner"></div>
                </label>
            </div>
        </div>

        <div class="glass-card rounded-[2.5rem] overflow-hidden divide-y divide-white/5">
            <div id="lista-config"></div>
        </div>

        <div class="space-y-3 pt-4">
            <button onclick="logout()" class="w-full py-4 rounded-2xl bg-slate-900/50 border border-white/5 text-orange-500 font-bold text-sm hover:bg-orange-500/10 transition">
                Sair da Conta
            </button>
            <button onclick="deletar()" class="w-full py-4 text-red-500/50 font-bold text-xs uppercase tracking-widest hover:text-red-500 transition">
                Apagar minha conta permanentemente
            </button>
        </div>
    </div>

    <div id="modal-opcoes" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center p-4 bg-black/60">
        <div class="bg-[#1e293b] w-full max-w-md rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10">
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
                <h3 id="modal-titulo" class="font-black text-blue-400 uppercase text-xs tracking-widest">Opções</h3>
                <button onclick="fecharModal()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div id="modal-lista" class="max-h-[60vh] overflow-y-auto p-4 custom-scroll space-y-2"></div>
        </div>
    </div>

    <script>
        // CSRF vindo do PHP (sem alterar layout)
        const CSRF_TOKEN = <?= json_encode($csrf) ?>;

        async function postConfig(payload) {
            const r = await fetch('salvar_configuracoes.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const j = await r.json().catch(() => null);
            if (!j || !j.ok) {
                alert((j && j.error) ? j.error : 'Erro ao salvar configurações');
            }
        }

        function salvarMinFotos(v) {
            postConfig({ min_fotos: parseInt(v, 10) });
        }

        function salvarComBio(checked) {
            postConfig({ apenas_com_bio: checked ? 1 : 0 });
        }

        const opcoesDados = {
            'Signo': ['Áries', 'Touro', 'Gêmeos', 'Câncer', 'Leão', 'Virgem', 'Libra', 'Escorpião', 'Sagitário', 'Capricórnio', 'Aquário', 'Peixes'],
            'Bebida': ['Não bebo', 'Bebo socialmente', 'Bebo raramente', 'Bebo bastante'],
            'Pets': ['Cachorro', 'Gato', 'Pássaro', 'Réptil', 'Não tenho', 'Tenho vários'],
            'Você fuma?': ['Não fumo', 'Fumo socialmente', 'Fumo bastante', 'Estou tentando parar'],
            'Me interesso por...': ['Homens', 'Mulheres', 'Todos'],
            'Tô procurando': ['Algo sério', 'Amizade', 'Apenas conversar', 'Não sei ainda'],
            'Família': ['Quero filhos', 'Não quero filhos', 'Já tenho filhos', 'Ainda não sei'],
            'Atividade física': ['Todo dia', 'Algumas vezes na semana', 'Raramente', 'Sedentário'],
            'Formação': ['Ensino Médio', 'Graduação', 'Pós-graduação', 'Mestrado/Doutorado'],
            'Linguagem do amor': ['Atos de serviço', 'Palavras de afirmação', 'Toque físico', 'Tempo de qualidade', 'Presentes'],
            'Estilo de comunicação': ['Muitas mensagens', 'Ligações', 'Pessoalmente', 'Vídeo chamadas'],
            'Interesses': ['Música', 'Esportes', 'Viagens', 'Tecnologia', 'Culinária', 'Arte', 'Games', 'Cinema']
        };

        const itens = [
            { nome: 'Interesses', icone: 'fa-star' },
            { nome: 'Tô procurando', icone: 'fa-eye' },
            { nome: 'Me interesso por...', icone: 'fa-heart' },
            { nome: 'Adicionar idiomas', icone: 'fa-language' },
            { nome: 'Signo', icone: 'fa-moon' },
            { nome: 'Formação', icone: 'fa-graduation-cap' },
            { nome: 'Família', icone: 'fa-baby-carriage' },
            { nome: 'Estilo de comunicação', icone: 'fa-comment-dots' },
            { nome: 'Linguagem do amor', icone: 'fa-heart-pulse' },
            { nome: 'Pets', icone: 'fa-paw' },
            { nome: 'Bebida', icone: 'fa-glass-martini-alt' },
            { nome: 'Você fuma?', icone: 'fa-smoking' },
            { nome: 'Atividade física', icone: 'fa-dumbbell' },
            { nome: 'Bloqueados', icone: 'fa-user-slash' }
        ];

        const container = document.getElementById('lista-config');

        itens.forEach(item => {
            const btn = document.createElement('button');
            btn.className = "w-full p-6 flex items-center justify-between hover:bg-white/5 active:bg-white/10 transition-all text-left";
            btn.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fa-solid ${item.icone} text-blue-400 text-sm"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-200">${item.nome}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span id="label-${item.nome.replace(/\s/g, '').replace(/[^\w]/g, '')}" class="text-[10px] text-slate-500 font-black uppercase tracking-tighter">
                        ${item.nome === 'Bloqueados' ? 'Ver lista' : 'Selecionar'}
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-700"></i>
                </div>
            `;
            
            btn.onclick = () => {
                if (item.nome === 'Bloqueados') {
                    window.location.href = 'bloqueados.php';
                } else {
                    abrirOpcoes(item.nome);
                }
            };
            container.appendChild(btn);
        });

        function abrirOpcoes(titulo) {
            const modal = document.getElementById('modal-opcoes');
            const lista = document.getElementById('modal-lista');
            const tituloModal = document.getElementById('modal-titulo');
            tituloModal.innerText = titulo;
            lista.innerHTML = '';
            
            const opcoes = opcoesDados[titulo] || ['Opção 1', 'Opção 2', 'Opção 3'];
            
            opcoes.forEach(opc => {
                const b = document.createElement('button');
                b.className = "w-full p-4 rounded-2xl bg-slate-700/30 text-left text-sm font-bold text-slate-300 hover:bg-blue-600 hover:text-white transition-all";
                b.innerText = opc;
                b.onclick = () => {
                    const labelId = `label-${titulo.replace(/\s/g, '').replace(/[^\w]/g, '')}`;
                    if(document.getElementById(labelId)) document.getElementById(labelId).innerText = opc;

                    // Agora salva no BANCO (nada de localStorage)
                    postConfig({ campo: titulo, valor: opc });

                    fecharModal();
                };
                lista.appendChild(b);
            });
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function fecharModal() {
            document.getElementById('modal-opcoes').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function logout() { 
            if(confirm("Tem certeza que deseja sair?")) {
                window.location.href = 'logout.php';
            }
        }

        function deletar() { 
            alert("Ainda não ativei o delete permanente no banco pra não correr risco. Quando você quiser, eu faço com segurança (confirmando senha + CSRF).");
        }
    </script>
</body>
=======
<?php
require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();
$conn = db();

// Carrega preferências do usuário (preferencias_usuario)
$sqlPref = "SELECT min_fotos, apenas_com_bio
            FROM preferencias_usuario
            WHERE usuario_id = ? LIMIT 1";
$stmt = $conn->prepare($sqlPref);
$stmt->bind_param("i", $meuId);
$stmt->execute();
$pref = $stmt->get_result()->fetch_assoc() ?: [
  'min_fotos' => 1,
  'apenas_com_bio' => 0,
];

// Carrega configurações (configuracoes_usuario) - se você usar
$sqlCfg = "SELECT notificacoes, perfil_publico
           FROM configuracoes_usuario
           WHERE id_usuario = ? LIMIT 1";
$stmt2 = $conn->prepare($sqlCfg);
$stmt2->bind_param("i", $meuId);
$stmt2->execute();
$cfg = $stmt2->get_result()->fetch_assoc() ?: [
  'notificacoes' => 1,
  'perfil_publico' => 1,
];

$csrf = csrf_token();

// valores iniciais
$minFotos = (int)($pref['min_fotos'] ?? 1);
$comBio  = (int)($pref['apenas_com_bio'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Connect Friends</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #0f172a; 
            color: white;
            overflow-x: hidden;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .range-input {
            -webkit-appearance: none;
            width: 100%;
            height: 4px;
            background: #1e293b;
            border-radius: 10px;
            outline: none;
        }

        .range-input::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: #3b82f6;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }

        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

        #modal-opcoes { backdrop-filter: blur(20px); transition: all 0.3s ease; }
    </style>
</head>
<body class="pb-10 custom-scroll">

    <div class="flex items-center justify-between px-6 py-6 sticky top-0 bg-[#0f172a]/90 backdrop-blur-lg z-50">
        <button onclick="window.location.href='index.php'" class="text-slate-400 hover:text-white transition active:scale-90">
            <i class="fa-solid fa-chevron-left text-xl"></i>
        </button>
        <h2 class="text-lg font-black tracking-tight">Configurações</h2>
        <button onclick="window.location.href='index.php'" class="text-blue-500 font-black text-sm uppercase tracking-widest hover:text-blue-400 active:scale-90">OK</button>
    </div>

    <div class="px-6 space-y-6 max-w-2xl mx-auto">
        
        <div class="glass-card rounded-[2.5rem] p-8 space-y-8">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-300">Número mínimo de fotos</span>
                    <span id="val-fotos" class="text-blue-400 font-black text-lg"><?= (int)$minFotos ?></span>
                </div>
                <input id="range-fotos" type="range" min="1" max="6" value="<?= (int)$minFotos ?>" class="range-input"
                       oninput="document.getElementById('val-fotos').innerText = this.value; salvarMinFotos(this.value)">
            </div>

            <div class="flex justify-between items-center pt-2">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-300">Apenas perfis com bio</span>
                    <span class="text-[10px] text-slate-500 uppercase font-black">Melhora a conexão</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="check-bio" class="sr-only peer" onchange="salvarComBio(this.checked)"
                           <?= $comBio ? 'checked' : '' ?>>
                    <div class="w-12 h-6 bg-slate-800 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6 shadow-inner"></div>
                </label>
            </div>
        </div>

        <div class="glass-card rounded-[2.5rem] overflow-hidden divide-y divide-white/5">
            <div id="lista-config"></div>
        </div>

        <div class="space-y-3 pt-4">
            <button onclick="logout()" class="w-full py-4 rounded-2xl bg-slate-900/50 border border-white/5 text-orange-500 font-bold text-sm hover:bg-orange-500/10 transition">
                Sair da Conta
            </button>
            <button onclick="deletar()" class="w-full py-4 text-red-500/50 font-bold text-xs uppercase tracking-widest hover:text-red-500 transition">
                Apagar minha conta permanentemente
            </button>
        </div>
    </div>

    <div id="modal-opcoes" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center p-4 bg-black/60">
        <div class="bg-[#1e293b] w-full max-w-md rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10">
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
                <h3 id="modal-titulo" class="font-black text-blue-400 uppercase text-xs tracking-widest">Opções</h3>
                <button onclick="fecharModal()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div id="modal-lista" class="max-h-[60vh] overflow-y-auto p-4 custom-scroll space-y-2"></div>
        </div>
    </div>

    <script>
        // CSRF vindo do PHP (sem alterar layout)
        const CSRF_TOKEN = <?= json_encode($csrf) ?>;

        async function postConfig(payload) {
            const r = await fetch('salvar_configuracoes.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const j = await r.json().catch(() => null);
            if (!j || !j.ok) {
                alert((j && j.error) ? j.error : 'Erro ao salvar configurações');
            }
        }

        function salvarMinFotos(v) {
            postConfig({ min_fotos: parseInt(v, 10) });
        }

        function salvarComBio(checked) {
            postConfig({ apenas_com_bio: checked ? 1 : 0 });
        }

        const opcoesDados = {
            'Signo': ['Áries', 'Touro', 'Gêmeos', 'Câncer', 'Leão', 'Virgem', 'Libra', 'Escorpião', 'Sagitário', 'Capricórnio', 'Aquário', 'Peixes'],
            'Bebida': ['Não bebo', 'Bebo socialmente', 'Bebo raramente', 'Bebo bastante'],
            'Pets': ['Cachorro', 'Gato', 'Pássaro', 'Réptil', 'Não tenho', 'Tenho vários'],
            'Você fuma?': ['Não fumo', 'Fumo socialmente', 'Fumo bastante', 'Estou tentando parar'],
            'Me interesso por...': ['Homens', 'Mulheres', 'Todos'],
            'Tô procurando': ['Algo sério', 'Amizade', 'Apenas conversar', 'Não sei ainda'],
            'Família': ['Quero filhos', 'Não quero filhos', 'Já tenho filhos', 'Ainda não sei'],
            'Atividade física': ['Todo dia', 'Algumas vezes na semana', 'Raramente', 'Sedentário'],
            'Formação': ['Ensino Médio', 'Graduação', 'Pós-graduação', 'Mestrado/Doutorado'],
            'Linguagem do amor': ['Atos de serviço', 'Palavras de afirmação', 'Toque físico', 'Tempo de qualidade', 'Presentes'],
            'Estilo de comunicação': ['Muitas mensagens', 'Ligações', 'Pessoalmente', 'Vídeo chamadas'],
            'Interesses': ['Música', 'Esportes', 'Viagens', 'Tecnologia', 'Culinária', 'Arte', 'Games', 'Cinema']
        };

        const itens = [
            { nome: 'Interesses', icone: 'fa-star' },
            { nome: 'Tô procurando', icone: 'fa-eye' },
            { nome: 'Me interesso por...', icone: 'fa-heart' },
            { nome: 'Adicionar idiomas', icone: 'fa-language' },
            { nome: 'Signo', icone: 'fa-moon' },
            { nome: 'Formação', icone: 'fa-graduation-cap' },
            { nome: 'Família', icone: 'fa-baby-carriage' },
            { nome: 'Estilo de comunicação', icone: 'fa-comment-dots' },
            { nome: 'Linguagem do amor', icone: 'fa-heart-pulse' },
            { nome: 'Pets', icone: 'fa-paw' },
            { nome: 'Bebida', icone: 'fa-glass-martini-alt' },
            { nome: 'Você fuma?', icone: 'fa-smoking' },
            { nome: 'Atividade física', icone: 'fa-dumbbell' },
            { nome: 'Bloqueados', icone: 'fa-user-slash' }
        ];

        const container = document.getElementById('lista-config');

        itens.forEach(item => {
            const btn = document.createElement('button');
            btn.className = "w-full p-6 flex items-center justify-between hover:bg-white/5 active:bg-white/10 transition-all text-left";
            btn.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fa-solid ${item.icone} text-blue-400 text-sm"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-200">${item.nome}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span id="label-${item.nome.replace(/\s/g, '').replace(/[^\w]/g, '')}" class="text-[10px] text-slate-500 font-black uppercase tracking-tighter">
                        ${item.nome === 'Bloqueados' ? 'Ver lista' : 'Selecionar'}
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-700"></i>
                </div>
            `;
            
            btn.onclick = () => {
                if (item.nome === 'Bloqueados') {
                    window.location.href = 'bloqueados.php';
                } else {
                    abrirOpcoes(item.nome);
                }
            };
            container.appendChild(btn);
        });

        function abrirOpcoes(titulo) {
            const modal = document.getElementById('modal-opcoes');
            const lista = document.getElementById('modal-lista');
            const tituloModal = document.getElementById('modal-titulo');
            tituloModal.innerText = titulo;
            lista.innerHTML = '';
            
            const opcoes = opcoesDados[titulo] || ['Opção 1', 'Opção 2', 'Opção 3'];
            
            opcoes.forEach(opc => {
                const b = document.createElement('button');
                b.className = "w-full p-4 rounded-2xl bg-slate-700/30 text-left text-sm font-bold text-slate-300 hover:bg-blue-600 hover:text-white transition-all";
                b.innerText = opc;
                b.onclick = () => {
                    const labelId = `label-${titulo.replace(/\s/g, '').replace(/[^\w]/g, '')}`;
                    if(document.getElementById(labelId)) document.getElementById(labelId).innerText = opc;

                    // Agora salva no BANCO (nada de localStorage)
                    postConfig({ campo: titulo, valor: opc });

                    fecharModal();
                };
                lista.appendChild(b);
            });
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function fecharModal() {
            document.getElementById('modal-opcoes').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function logout() { 
            if(confirm("Tem certeza que deseja sair?")) {
                window.location.href = 'logout.php';
            }
        }

        function deletar() { 
            alert("Ainda não ativei o delete permanente no banco pra não correr risco. Quando você quiser, eu faço com segurança (confirmando senha + CSRF).");
        }
    </script>
</body>
>>>>>>> 665cc278062e96d09826f42e686cf449116b9ab9
</html>