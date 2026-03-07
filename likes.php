<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_login();
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Curtidas | Connect Friends</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- CSRF para fetch -->
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
</head>

<body class="bg-slate-50 dark:bg-slate-900">

<section class="p-6 lg:p-10 animate-in fade-in duration-500 max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-800 dark:text-white">Curtidas</h2>
                <div id="badge-contagem" class="bg-gradient-to-tr from-amber-400 to-orange-500 text-white text-[11px] font-black px-2.5 py-1 rounded-xl shadow-lg hidden">0</div>
            </div>
            <p class="text-slate-400 font-medium">Descubra quem demonstrou interesse em você</p>
        </div>
        <button onclick="openPremium('Ver Curtidas')" class="hidden lg:flex items-center gap-2 bg-amber-100 text-amber-700 px-4 py-2 rounded-2xl font-bold text-sm border border-amber-200 shadow-sm hover:bg-amber-200 transition-all">
            <i class="fa-solid fa-crown"></i>
            Assinante PRO
        </button>
    </div>

    <div id="grid-fotos" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
    </div>

    <div id="banner-azul" class="mt-12 p-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-[3rem] text-center text-white shadow-2xl">
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
            <i class="fa-solid fa-crown text-yellow-300"></i>
        </div>
        <h3 class="text-2xl font-black mb-2 italic">Quer ver quem são?</h3>
        <p class="mb-6 text-blue-100 opacity-90 max-w-md mx-auto">Assine o Connect Gold para desbloquear suas curtidas e dar match instantâneo com quem já te escolheu.</p>
        <button onclick="openPremium('Revelar Curtidas')" class="bg-white text-blue-600 px-10 py-4 rounded-2xl font-black shadow-xl hover:scale-105 active:scale-95 transition-all uppercase tracking-widest text-xs">
            DESBLOQUEAR AGORA
        </button>
    </div>
</section>

<div id="modal-premium" class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm hidden">
    <div class="bg-[#1e293b] w-full max-w-[400px] rounded-[3rem] overflow-hidden shadow-2xl border border-white/10">

        <div class="bg-gradient-to-b from-[#3b82f6] to-[#6366f1] p-8 text-center text-white relative">
            <button onclick="closePremium()" class="absolute top-4 right-6 text-white/50 hover:text-white text-2xl">×</button>
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                <i class="fa-solid fa-crown text-3xl text-amber-400"></i>
            </div>
            <h2 class="text-2xl font-black tracking-tight">Connect Gold</h2>
            <p class="text-[10px] uppercase font-bold opacity-80 tracking-widest mt-1" id="premium-motivo">Para liberar: Ver Curtidas</p>
        </div>

        <div class="p-8 space-y-6">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-blue-500/20 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-check text-blue-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">Curtidas ilimitadas</p>
                        <p class="text-slate-400 text-[10px]">Sem limites diários</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-amber-500/20 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">Ver quem te curtiu</p>
                        <p class="text-slate-400 text-[10px]">Saiba antes de dar o swipe</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div id="plano-mensal" onclick="selecionarPlano('mensal', 19.90)"
                    class="plano-card border-2 border-slate-700 rounded-3xl p-4 text-center cursor-pointer transition-all bg-slate-800/50">
                    <p class="text-slate-400 text-[10px] font-bold uppercase">Mensal</p>
                    <p class="text-white font-black text-lg">R$ 19,90</p>
                </div>

                <div id="plano-anual" onclick="selecionarPlano('anual', 199.90)"
                    class="plano-card border-2 border-cyan-400 rounded-3xl p-4 text-center cursor-pointer relative bg-slate-800/50 shadow-[0_0_15px_rgba(34,211,238,0.2)]">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-yellow-400 text-slate-900 text-[8px] font-black px-3 py-1 rounded-full uppercase whitespace-nowrap">Melhor Valor</span>
                    <p class="text-slate-400 text-[10px] font-bold uppercase">Anual</p>
                    <p class="text-white font-black text-lg">R$ 199,90</p>
                </div>
            </div>

            <button id="btn-assinar-modal" onclick="abrirPagamentoInterno()" class="w-full bg-gradient-to-r from-cyan-300 to-blue-400 text-slate-900 py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all">
                ASSINAR AGORA
            </button>

            <button onclick="closePremium()" class="w-full text-slate-500 text-[10px] font-bold uppercase tracking-widest hover:text-slate-300 transition-colors">
                Não, prefiro a versão limitada
            </button>
        </div>
    </div>
</div>

<div id="modal-pagamento-interno" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
    <div class="relative w-full max-w-[400px] bg-[#1e293b] rounded-[3rem] overflow-hidden shadow-2xl border border-white/10">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <button onclick="fecharPagamentoInterno()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></button>
            <h3 class="text-white font-bold">Pagamento Seguro</h3>
            <div class="w-4"></div>
        </div>
        <div class="p-8 space-y-6">
            <div class="space-y-3">
                <button onclick="mostrarCamposCartao()" class="w-full p-4 bg-slate-800 border border-slate-700 rounded-2xl flex items-center gap-4 hover:border-blue-500 transition group">
                    <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-white font-bold text-sm">Cartão de Crédito</p>
                    </div>
                </button>
                <button onclick="mostrarCamposCartao()" class="w-full p-4 bg-slate-800 border border-slate-700 rounded-2xl flex items-center gap-4 hover:border-green-500 transition group">
                    <div class="w-10 h-10 bg-green-500/10 rounded-full flex items-center justify-center text-green-400 group-hover:bg-green-500 group-hover:text-white transition">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-white font-bold text-sm">Cartão de Débito</p>
                    </div>
                </button>
            </div>
            <div id="form-cartao-interno" class="hidden space-y-4 pt-4 border-t border-slate-800 animate-in slide-in-from-bottom-2">
                <input type="text" placeholder="0000 0000 0000 0000" class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 px-4 text-white text-sm outline-none focus:ring-1 focus:ring-blue-500">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" placeholder="MM/AA" class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 px-4 text-white text-sm outline-none focus:ring-1 focus:ring-blue-500">
                    <input type="text" placeholder="CVV" class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 px-4 text-white text-sm outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <button onclick="processarSimulacaoFinal()" class="w-full py-4 bg-blue-600 text-white font-black rounded-2xl uppercase text-xs tracking-widest shadow-lg active:scale-95 transition-all">Confirmar e Pagar</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-sucesso" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden animate-in fade-in duration-300">
    <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[3rem] overflow-hidden shadow-2xl">
        <div class="bg-gradient-to-br from-amber-400 to-orange-500 p-8 text-center text-white">
            <div class="w-20 h-20 bg-white/20 rounded-3xl flex items-center justify-center mx-auto mb-4 animate-bounce">
                <i class="fa-solid fa-crown text-4xl text-white"></i>
            </div>
            <h2 class="text-3xl font-black uppercase italic tracking-tighter">Connect Gold Ativo!</h2>
            <p class="opacity-90 font-bold">Você agora faz parte da elite do Connect.</p>
        </div>
        <div class="p-8">
            <h4 class="text-slate-400 font-black text-xs uppercase tracking-widest mb-4">Benefícios Desbloqueados:</h4>
            <ul class="space-y-4 mb-8">
                <li class="flex items-center gap-3 text-slate-700 dark:text-slate-200 font-bold"><i class="fa-solid fa-circle-check text-green-500 text-xl"></i> Ver quem te curtiu (Sem Blur)</li>
                <li class="flex items-center gap-3 text-slate-700 dark:text-slate-200 font-bold"><i class="fa-solid fa-circle-check text-green-500 text-xl"></i> Mensagens Ilimitadas</li>
                <li class="flex items-center gap-3 text-slate-700 dark:text-slate-200 font-bold"><i class="fa-solid fa-circle-check text-green-500 text-xl"></i> Impulsionamento de Perfil</li>
                <li class="flex items-center gap-3 text-slate-700 dark:text-slate-200 font-bold"><i class="fa-solid fa-circle-check text-green-500 text-xl"></i> Selo de Verificado Gold</li>
            </ul>
            <button onclick="fecharSucesso()" class="w-full bg-slate-900 dark:bg-white dark:text-slate-900 text-white py-5 rounded-2xl font-black shadow-xl hover:scale-105 transition-all uppercase tracking-widest text-sm">APROVEITAR AGORA</button>
        </div>
    </div>
</div>

<script>
/* ========= CONFIG ========= */
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ========= ESTADO ========= */
let IS_GOLD = false;
let valorSelecionado = 199.90; // mantém igual seu padrão anual

/* ========= MODAIS (SEU CÓDIGO, SEM MEXER NO DESIGN) ========= */
function openPremium(motivo) {
  document.getElementById('premium-motivo').innerText = "Para liberar: " + motivo;
  document.getElementById('modal-premium').classList.remove('hidden');
}
function closePremium() {
  document.getElementById('modal-premium').classList.add('hidden');
}
function selecionarPlano(tipo, valor) {
  valorSelecionado = valor;
  document.querySelectorAll('.plano-card').forEach(card => {
    card.classList.remove('border-cyan-400', 'shadow-[0_0_15px_rgba(34,211,238,0.2)]');
    card.classList.add('border-slate-700');
  });
  const cardAtivo = document.getElementById(`plano-${tipo}`);
  cardAtivo.classList.remove('border-slate-700');
  cardAtivo.classList.add('border-cyan-400', 'shadow-[0_0_15px_rgba(34,211,238,0.2)]');
}

/* ========= PAGAMENTO INTERNO (mantém UI; sem localStorage) ========= */
function abrirPagamentoInterno() {
  closePremium();
  document.getElementById('modal-pagamento-interno').classList.remove('hidden');
}
function fecharPagamentoInterno() {
  document.getElementById('modal-pagamento-interno').classList.add('hidden');
  document.getElementById('form-cartao-interno').classList.add('hidden');
}
function mostrarCamposCartao() {
  document.getElementById('form-cartao-interno').classList.remove('hidden');
}

/* ========= AQUI: em vez de localStorage, chama API ========= */
async function processarSimulacaoFinal() {
  Swal.fire({
    title: 'Processando...',
    text: 'Comunicando com a operadora',
    timer: 1500,
    timerProgressBar: true,
    background: '#1e293b',
    color: '#ffffff',
    didOpen: () => { Swal.showLoading(); }
  }).then(async () => {
    try {
      // endpoint server-side pra ativar gold (sessão/banco)
      // se você já tiver outro nome, troca aqui, mas SEM barra:
      const resp = await fetch('api_assinatura_simular.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': CSRF
        },
        body: JSON.stringify({ plano: (valorSelecionado === 199.90 ? 'anual' : 'mensal') })
      });

      const data = await resp.json();
      if (!data.ok) throw new Error(data.error || 'Falha ao ativar assinatura');

      fecharPagamentoInterno();

      // recarrega status e UI
      await carregarStatusGold();
      await carregarCurtidasReais();
      verificarAcessoGold();

      document.getElementById('modal-sucesso').classList.remove('hidden');
    } catch (e) {
      Swal.fire({
        icon: 'error',
        title: 'Não ativou o Gold',
        text: String(e.message || e),
        background: '#1e293b',
        color: '#ffffff'
      });
    }
  });
}

function fecharSucesso() { document.getElementById('modal-sucesso').classList.add('hidden'); }

/* ========= BANCO REAL (SEM LOCALSTORAGE) ========= */

/** 1) pega se usuário é gold */
async function carregarStatusGold() {
  const res = await fetch('api_me_status.php', {
    method: 'GET',
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  });

  const data = await res.json();
  IS_GOLD = !!(data.user && data.user.is_gold);
}

/** 2) lista curtidas reais */
async function carregarCurtidasReais() {
  const res = await fetch('api_likes_list.php', {
    method: 'GET',
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  });

  const data = await res.json();
  renderizarCurtidas(data.likes || []);
}

/** 3) renderiza mantendo seu design */
function renderizarCurtidas(lista) {
  const grid = document.getElementById('grid-fotos');
  grid.innerHTML = '';

  // mantém seu badge igual
  if (!lista || lista.length === 0) {
    grid.innerHTML = `
      <div class="col-span-full text-center text-slate-400 font-medium">
        Nenhuma curtida ainda.
      </div>
    `;
    atualizarContador();
    verificarAcessoGold();
    return;
  }

  lista.forEach(perfil => {
    const blurClass = IS_GOLD ? '' : 'blur-2xl scale-125';
    const btnTexto = IS_GOLD ? 'Conversar' : 'Ver Perfil';

    // mantém a mesma ação visual:
    // - gold: vai pra mensagens.php
    // - não gold: abre premium
    const btnClick = IS_GOLD
      ? `window.location.href='mensagens.php?id=${perfil.id}'`
      : `openPremium('Ver Perfil de ${perfil.nome}')`;

    grid.innerHTML += `
      <div class="group relative aspect-[3/4] rounded-[2.5rem] overflow-hidden shadow-xl bg-slate-200 transition-all hover:scale-[1.02]">
        <img src="${perfil.foto}" class="img-perfil w-full h-full object-cover transition duration-700 ${blurClass} group-hover:scale-150">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col items-center justify-end p-6">
          <p class="text-white font-bold mb-3">${perfil.nome}, ${perfil.idade}</p>
          <button onclick="${btnClick}" class="btn-perfil w-full bg-white text-slate-900 py-3 rounded-2xl font-extrabold text-[11px] shadow-2xl uppercase tracking-wider">${btnTexto}</button>
        </div>
      </div>
    `;
  });

  atualizarContador();
  verificarAcessoGold();
}

/* ========= LÓGICA DE ACESSO (SEM LOCALSTORAGE) ========= */
function verificarAcessoGold() {
  if (IS_GOLD) {
    document.querySelectorAll('.img-perfil').forEach(img => img.classList.remove('blur-2xl'));
    document.querySelectorAll('.btn-perfil').forEach(btn => {
      btn.innerText = 'Conversar';
      // o onclick real já foi setado no render; aqui só mantém seu comportamento.
    });
    const banner = document.getElementById('banner-azul');
    if (banner) banner.classList.add('hidden');

    const badge = document.getElementById('badge-contagem');
    if (badge) { badge.classList.add('bg-green-500'); }
  } else {
    // se não gold, garante banner visível (igual seu arquivo)
    const banner = document.getElementById('banner-azul');
    if (banner) banner.classList.remove('hidden');
  }
}

function atualizarContador() {
  const badge = document.getElementById('badge-contagem');
  const total = document.getElementById('grid-fotos').children.length;
  if (badge) {
    badge.innerText = total > 99 ? "99+" : total;
    badge.classList.remove('hidden');
  }
}

/* ========= INIT ========= */
document.addEventListener('DOMContentLoaded', async () => {
  try {
    await carregarStatusGold();
    await carregarCurtidasReais();
    verificarAcessoGold();
  } catch (e) {
    console.error(e);
    Swal.fire({
      icon: 'error',
      title: 'Erro ao carregar curtidas',
      text: 'Não foi possível carregar do servidor.',
      background: '#1e293b',
      color: '#ffffff'
    });
  }
});
</script>

</body>
</html>
</script>