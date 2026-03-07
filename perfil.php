<?php
require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();
$csrf  = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | Connect Friends</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc;
            transition: background-color 0.3s;
        }

        .gradient-bg { 
            background: linear-gradient(135deg, #2dd4bf 0%, #3b82f6 50%, #a855f7 100%); 
        }

        /* Suporte ao Modo Noturno */
        body.dark-mode { background-color: #0f172a; }
        body.dark-mode .profile-card, body.dark-mode #perfil-menu, body.dark-mode .modal-content, body.dark-mode .edit-modal-content, body.dark-mode .new-post-content { background-color: #1e293b; border-color: #334155; }
        body.dark-mode h2, body.dark-mode h3, body.dark-mode span:not(.text-blue-600) { color: #f1f5f9; }
        body.dark-mode p, body.dark-mode .menu-item-text { color: #94a3b8; }
        body.dark-mode .bg-white { background-color: #1e293b; }
        body.dark-mode .hover\:bg-gray-50:hover { background-color: #334155; }
        body.dark-mode .border-gray-100, body.dark-mode .border-gray-50 { border-color: #334155; }

        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        #modal-post, #modal-editar-perfil, #modal-novo-post { backdrop-filter: blur(8px); }
        
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* Estilo da Grade de Edição (Estilo Tinder) */
        .grid-edit-item {
            aspect-ratio: 2/3;
            background-color: #f1f5f9;
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
            border: 2px dashed #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body.dark-mode .grid-edit-item { background-color: #0f172a; border-color: #334155; }
        
        .grid-edit-item img { width: 100%; height: 100%; object-fit: cover; }
        
        .btn-remove-photo {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(4px);
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            border: 1px solid rgba(255,255,255,0.2);
            z-index: 10;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        body.dark-mode .glass-effect {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body>

<div class="fade-in max-w-6xl mx-auto py-6 lg:py-12 px-4 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="profile-card bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 sticky top-10 transition-colors duration-300">
                <div class="flex flex-col items-center text-center">
                    <div class="relative mb-6">
                        <div class="w-32 h-32 lg:w-40 lg:h-40 rounded-full gradient-bg p-1 shadow-2xl overflow-hidden group">
                            <img id="foto-preview" src="https://i.pravatar.cc/150?u=me" class="rounded-full border-4 border-white w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        </div>
                        <label for="input-foto" class="absolute bottom-2 right-2 bg-white w-10 h-10 rounded-full shadow-lg flex items-center justify-center text-blue-500 hover:scale-110 cursor-pointer border border-gray-50">
                            <i class="fa-solid fa-camera text-sm"></i>
                            <input type="file" id="input-foto" accept="image/*" class="hidden" onchange="previewImagem(event)">
                        </label>
                    </div>

                    <div class="flex items-center gap-3 justify-center mb-1">
                        <h2 class="text-2xl font-black tracking-tight text-gray-800" id="profile-name">Carregando...</h2>
                        <button onclick="togglePerfilMenu(event)" class="w-8 h-8 flex items-center justify-center bg-gray-50 rounded-full hover:bg-gray-100 transition">
                            <i class="fa-solid fa-ellipsis text-gray-400"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-blue-500 font-black uppercase tracking-[0.2em] mb-6">Membro Connect</p>

                    <div class="flex justify-center w-full py-4 border-y border-gray-50 dark:border-slate-700/50 my-2">
                        <div class="text-center flex-1">
                            <p class="font-black text-xl text-gray-800" id="post-count">0</p>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Postagens</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-4 bg-gray-50/50 dark:bg-slate-800/40 p-5 rounded-[1.5rem] border border-gray-50 dark:border-slate-700/30">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sobre mim</h3>
                    <p id="profile-bio" class="text-sm text-gray-600 leading-relaxed font-medium italic">Nenhuma biografia definida.</p>
                    <div id="perfil-interesses" class="flex flex-wrap gap-2 pt-2"></div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-8 relative">
            <div id="perfil-menu" class="hidden absolute top-0 right-0 bg-white border border-gray-100 shadow-2xl rounded-[2rem] w-64 overflow-hidden z-[60] animate-in fade-in zoom-in-95 duration-200">
                <div class="p-2 space-y-1">
                    <button onclick="abrirModalEditar()" class="menu-item-text w-full px-4 py-3 text-left text-sm font-bold text-gray-600 hover:bg-gray-50 rounded-2xl flex items-center gap-3 transition">
                        <i class="fa-solid fa-user-pen text-pink-500 w-5"></i> Editar Perfil
                    </button>
                    <button onclick="window.location.href='configuracoes.php'" class="menu-item-text w-full px-4 py-3 text-left text-sm font-bold text-gray-600 hover:bg-gray-50 rounded-2xl flex items-center gap-3 transition">
                        <i class="fa-solid fa-gear text-blue-500 w-5"></i> Configurações
                    </button>
                    <button onclick="toggleDarkMode()" class="menu-item-text w-full px-4 py-3 text-left text-sm font-bold text-gray-600 hover:bg-gray-50 rounded-2xl flex items-center gap-3 transition">
                        <i class="fa-solid fa-moon text-indigo-500 w-5"></i> Modo Noturno
                    </button>
                    <button onclick="alterarIdioma()" class="menu-item-text w-full px-4 py-3 text-left text-sm font-bold text-gray-600 hover:bg-gray-50 rounded-2xl flex items-center gap-3 transition">
                        <i class="fa-solid fa-language text-emerald-500 w-5"></i> Idioma (PT-BR)
                    </button>
                    <button onclick="falarComSuporte()" class="menu-item-text w-full px-4 py-3 text-left text-sm font-bold text-gray-600 hover:bg-gray-50 rounded-2xl flex items-center gap-3 transition">
                        <i class="fa-solid fa-headset text-blue-500 w-5"></i> Fale com a gente
                    </button>
                    <div class="px-4 py-2 border-t border-gray-50">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tenho interesse em:</p>
                        <div class="flex gap-2">
                            <button onclick="setInteresse('Homem')" id="btn-homem" class="flex-1 py-2 text-[10px] font-bold rounded-xl border border-gray-100 transition-all">Homens</button>
                            <button onclick="setInteresse('Mulher')" id="btn-mulher" class="flex-1 py-2 text-[10px] font-bold rounded-xl border border-gray-100 transition-all">Mulheres</button>
                        </div>
                    </div>
                    <div class="my-1 border-t border-gray-50"></div>
                    <button onclick="logout()" class="menu-item-text w-full px-4 py-3 text-left text-sm font-bold text-gray-600 hover:bg-gray-50 rounded-2xl flex items-center gap-3 transition">
                        <i class="fa-solid fa-right-from-bracket text-orange-500 w-5"></i> Sair da Conta
                    </button>
                    <button onclick="deletarConta()" class="w-full px-4 py-3 text-left text-sm font-bold text-red-500 hover:bg-red-50 rounded-2xl flex items-center gap-3 transition">
                        <i class="fa-solid fa-trash-can w-5"></i> Deletar Conta
                    </button>
                </div>
            </div>

            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest px-2">Momentos</h3>

            <div id="feed-grid" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div onclick="abrirModalCriarPost()" class="aspect-square rounded-[2.5rem] border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-3 text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-all cursor-pointer bg-white group shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-blue-50 transition">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest">Novo Post</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-novo-post" class="fixed inset-0 z-[120] hidden flex items-center justify-center p-4 bg-black/60">
    <div class="new-post-content bg-white dark:bg-[#1e293b] w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl font-black text-gray-800 dark:text-white italic">Novo Post</h2>
            <button onclick="fecharModalCriarPost()" class="text-gray-400 hover:text-red-500 transition">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>
        <div class="p-8 space-y-6">
            <label class="block w-full aspect-video rounded-[2rem] border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer group relative overflow-hidden text-center">
                <div id="placeholder-upload" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                    <i class="fa-solid fa-image text-4xl mb-3 group-hover:scale-110 transition duration-300"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Escolher Foto</span>
                </div>
                <input type="file" id="input-novo-post" accept="image/*" class="hidden" onchange="previewNovoPost(event)">
                <img id="preview-novo-post-img" class="hidden w-full h-full object-cover">
            </label>
            <textarea id="legenda-novo-post" rows="3" class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-100 dark:border-gray-700 rounded-2xl px-5 py-4 text-sm text-gray-800 dark:text-white outline-none focus:border-blue-500 transition resize-none" placeholder="Escreva uma legenda incrível..."></textarea>
            <button onclick="confirmarPostagem()" class="w-full py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-black uppercase tracking-widest rounded-2xl hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-blue-500/20">
                Publicar Agora
            </button>
        </div>
    </div>
</div>

<div id="modal-editar-perfil" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4 bg-black/60 overflow-y-auto">
    <div class="edit-modal-content bg-[#f8fafc] w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white dark:bg-[#1e293b]">
            <h2 class="text-lg font-black text-gray-800">Editar info</h2>
            <button onclick="salvarAlteracoesPerfil()" class="text-blue-500 font-bold text-sm">OK</button>
        </div>
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scroll">
            <div>
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-4">Mídia (Máx. 6 fotos)</p>
                <div class="grid grid-cols-3 gap-3" id="grade-edit-fotos" style="min-height: 250px;"></div>
            </div>
            <div class="space-y-4">
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Sobre você</p>
                <div class="space-y-2">
                    <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Bio</label>
                    <textarea id="edit-bio" rows="4" class="w-full bg-white dark:bg-[#0f172a] border border-gray-100 dark:border-gray-700 rounded-2xl px-4 py-3 text-sm outline-none focus:border-blue-500 transition resize-none dark:text-white" placeholder="Sua bio aparecerá aqui..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="file" id="input-grade-foto" accept="image/*" class="hidden" onchange="processarFotoGrade(event)">

<div id="modal-post" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-black/80 transition-all">
    <div class="modal-content bg-white w-full max-w-5xl rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col md:flex-row h-[80vh]">
        <div class="md:w-3/5 bg-black flex items-center justify-center relative">
            <img id="modal-img" src="" class="max-w-full max-h-full object-contain">
        </div>
        <div class="md:w-2/5 flex flex-col bg-white dark:bg-[#1e293b]">
            <div class="p-6 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img id="modal-user-img" src="" class="w-10 h-10 rounded-full border-2 border-blue-500 p-0.5 object-cover">
                    <div>
                        <p id="modal-user-name" class="font-black text-sm text-gray-800 dark:text-white leading-none">User</p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-tighter mt-1">Agora mesmo</p>
                    </div>
                </div>
                <button onclick="fecharModal()" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div id="lista-comentarios" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scroll">
                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">Comentários</p>
            </div>
            <div class="p-6 border-t border-gray-50 dark:border-gray-700 space-y-4">
                <div class="flex items-center gap-4">
                 <button onclick="curtirPostModal()" class="text-xl transition">
                        <i class="fa-regular fa-heart text-gray-800 dark:text-white"></i>
                    </button>
                    <p class="text-xs font-bold text-gray-800 dark:text-white"><span id="modal-likes">0</span> curtidas</p>
                </div>
                <div class="flex gap-2 bg-gray-50 dark:bg-gray-800 p-3 rounded-2xl items-center">
                    <input type="text" id="input-comentario" placeholder="Escreva algo..." class="bg-transparent flex-1 text-sm outline-none dark:text-white">
                    <button onclick="adicionarComentario()" class="text-blue-500 font-black text-xs uppercase tracking-widest hover:text-blue-600 transition">Postar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/** ========= CONFIG ========= */
const CSRF_TOKEN = <?= json_encode($csrf) ?>;

/** ========= ESTADO (sem localStorage) ========= */
let ME = null;
let fotosPerfilGrade = [];
let postsCache = [];
let postAbertoId = null;

/** ========= HELPERS ========= */
function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, s => (
    {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]
  ));
}

async function apiGet(url) {
  const r = await fetch(url, {
    credentials: 'include',
    headers: { 'Accept': 'application/json' }
  });
  const j = await r.json().catch(() => null);
  if (!j || !j.ok) throw new Error((j && j.error) ? j.error : 'Erro');
  return j;
}

async function apiPost(url, payload) {
  const r = await fetch(url, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-Token': CSRF_TOKEN
    },
    body: JSON.stringify(payload || {})
  });
  const j = await r.json().catch(() => null);
  if (!j || !j.ok) throw new Error((j && j.error) ? j.error : 'Erro');
  return j;
}

/** ========= INIT ========= */
document.addEventListener('DOMContentLoaded', async () => {
  try {
    await carregarDadosPerfil();
    await carregarMeusPosts();
    renderizarGradeEdicao();
  } catch (e) {
    alert(e.message || 'Erro ao carregar');
  }
});

/** ========= PERFIL ========= */
async function carregarDadosPerfil() {
  const j = await apiGet('api_me.php');
  ME = j.user;

  const nome = ME?.username || 'Usuário';
  const foto = ME?.foto_perfil || "https://i.pravatar.cc/150?u=me";
  const bioTxt = (ME?.bio && String(ME.bio).trim() !== '') ? ME.bio : "Bem-vindo ao meu perfil!";
  const idadeTxt = ME?.idade ? (ME.idade + ' anos') : '';

  document.getElementById('profile-name').textContent = nome;
  document.getElementById('modal-user-name').textContent = nome;

  document.getElementById('foto-preview').src = foto;
  document.getElementById('modal-user-img').src = foto;

  document.getElementById('profile-bio').innerHTML = `<b>${escapeHtml(idadeTxt)}</b><br>✨ ${escapeHtml(bioTxt)}`;
  document.getElementById('edit-bio').value = bioTxt;

  // interesses
  const interesses = Array.isArray(ME?.interesses) ? ME.interesses : [];
  const container = document.getElementById('perfil-interesses');
  container.innerHTML = '';
  interesses.forEach(interest => {
    const span = document.createElement('span');
    span.className = "px-3 py-1 bg-blue-50 text-blue-600 text-[9px] font-black uppercase rounded-full border border-blue-100";
    span.textContent = interest;
    container.appendChild(span);
  });

  // fotos grade
  fotosPerfilGrade = Array.isArray(ME?.fotos) ? ME.fotos : [];
}

/** ========= MODAIS ========= */
function abrirModalCriarPost() {
  document.getElementById('modal-novo-post').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function fecharModalCriarPost() {
  document.getElementById('modal-novo-post').classList.add('hidden');
  document.body.style.overflow = 'auto';
}
function fecharModal() {
  const modal = document.getElementById('modal-post');
  modal.classList.add('hidden');
  document.body.style.overflow = 'auto';
}
function abrirModalEditar() {
  document.getElementById('modal-editar-perfil').classList.remove('hidden');
  document.getElementById('perfil-menu').classList.add('hidden');
  renderizarGradeEdicao();
}
function fecharModalEditar() {
  document.getElementById('modal-editar-perfil').classList.add('hidden');
}

function togglePerfilMenu(e) {
  e.stopPropagation();
  document.getElementById('perfil-menu').classList.toggle('hidden');
}
document.addEventListener('click', (e) => {
  const menu = document.getElementById('perfil-menu');
  if(menu && !menu.contains(e.target)) menu.classList.add('hidden');
  if(e.target.id === 'modal-post') fecharModal();
});

/** ========= POSTS ========= */
function previewNovoPost(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = document.getElementById('preview-novo-post-img');
      img.src = e.target.result;
      img.classList.remove('hidden');
      document.getElementById('placeholder-upload').classList.add('hidden');
    };
    reader.readAsDataURL(file);
  }
}

async function confirmarPostagem() {
  const imgPreview = document.getElementById('preview-novo-post-img');
  const imgSource = imgPreview.src;
  const legenda = document.getElementById('legenda-novo-post').value;

  if (!imgSource || imgSource === "") {
    alert("Por favor, selecione uma imagem primeiro!");
    return;
  }

  try {
    await apiPost('api_post_criar.php', { imagem: imgSource, legenda: legenda });

    // limpeza
    imgPreview.src = "";
    imgPreview.classList.add('hidden');
    document.getElementById('placeholder-upload').classList.remove('hidden');
    document.getElementById('legenda-novo-post').value = "";

    fecharModalCriarPost();
    await carregarMeusPosts();
  } catch (e) {
    alert(e.message || 'Erro ao postar');
  }
}

async function carregarMeusPosts() {
  const grid = document.getElementById('feed-grid');
  if (!grid) return;

  const j = await apiGet('api_posts_me.php');
  postsCache = Array.isArray(j.posts) ? j.posts : [];

  // mantém seu card "Novo Post" igual
  grid.innerHTML = `
      <div onclick="abrirModalCriarPost()" class="aspect-square rounded-[2.5rem] border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-3 text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-all cursor-pointer bg-white dark:bg-gray-800/50 group shadow-sm">
          <div class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center group-hover:bg-blue-50 transition">
              <i class="fa-solid fa-plus text-xl"></i>
          </div>
          <span class="text-[10px] font-black uppercase tracking-widest">Novo Post</span>
      </div>
  `;

  postsCache.forEach(post => {
    const div = document.createElement('div');
    div.className = "rounded-[2.5rem] overflow-hidden shadow-sm aspect-square fade-in relative group cursor-pointer";
    div.innerHTML = `
      <img src="${post.imagem}" onclick="abrirModal('${post.id}')" class="w-full h-full object-cover hover:scale-105 transition duration-700">
      <button onclick="event.stopPropagation(); excluirPost(${post.id});" class="absolute top-4 right-4 bg-black/50 backdrop-blur-md text-white w-8 h-8 rounded-full opacity-0 group-hover:opacity-100 transition z-10">
          <i class="fa-solid fa-trash-can text-xs"></i>
      </button>
    `;
    grid.appendChild(div);
  });

  const countEl = document.getElementById('post-count');
  if(countEl) countEl.textContent = postsCache.length;
}

async function excluirPost(id) {
  if(!confirm("Excluir este post?")) return;
  try {
    await apiPost('api_post_excluir.php', { post_id: id });
    await carregarMeusPosts();
  } catch (e) {
    alert(e.message || 'Erro ao excluir');
  }
}

/** ========= DETALHE DO POST (MODAL) ========= */
async function abrirModal(postId) {
  try {
    const j = await apiGet('api_post_detalhe.php?post_id=' + encodeURIComponent(postId));
    const post = j.post;
    if(!post) return;

    postAbertoId = post.id;

    document.getElementById('modal-img').src = post.imagem;

    // mantém nome/foto no modal com base no usuário logado
    document.getElementById('modal-user-name').textContent = ME?.username || "Usuário";
    document.getElementById('modal-user-img').src = ME?.foto_perfil || "https://i.pravatar.cc/150?u=me";

    document.getElementById('modal-likes').textContent = post.curtidas || 0;

    const lista = document.getElementById('lista-comentarios');
    lista.innerHTML = `<p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">Comentários</p>`;

    (post.comentarios || []).forEach(c => {
      const div = document.createElement('div');
      div.className = "flex flex-col fade-in";
      div.innerHTML = `<p class="text-sm"><span class="font-black text-gray-800 dark:text-white mr-2">${escapeHtml(c.username)}:</span><span class="text-gray-600 dark:text-gray-300">${escapeHtml(c.comentario)}</span></p>`;
      lista.appendChild(div);
    });

    document.getElementById('modal-post').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  } catch (e) {
    alert(e.message || 'Erro ao abrir post');
  }
}

async function adicionarComentario() {
  const input = document.getElementById('input-comentario');
  const txt = (input.value || '').trim();
  if (!txt) return;

  try {
    await apiPost('api_comentario_add.php', { post_id: postAbertoId, comentario: txt });
    input.value = "";
    await abrirModal(String(postAbertoId));
  } catch (e) {
    alert(e.message || 'Erro ao comentar');
  }
}

async function curtirPostModal() {
  try {
    await apiPost('api_post_like.php', { post_id: postAbertoId });
    await abrirModal(String(postAbertoId));
  } catch (e) {
    alert(e.message || 'Erro ao curtir');
  }
}

/** ========= EDITAR PERFIL ========= */
async function salvarAlteracoesPerfil() {
  try {
    const bio = document.getElementById('edit-bio').value;
    await apiPost('api_perfil_salvar.php', { bio: bio, fotos: fotosPerfilGrade });
    await carregarDadosPerfil();
    fecharModalEditar();
  } catch (e) {
    alert(e.message || 'Erro ao salvar perfil');
  }
}

function renderizarGradeEdicao() {
  const container = document.getElementById('grade-edit-fotos');
  if(!container) return; 
  container.innerHTML = '';

  for (let i = 0; i < 6; i++) {
    const div = document.createElement('div');
    div.className = "grid-edit-item group";
    if (fotosPerfilGrade[i]) {
      div.innerHTML = `<img src="${fotosPerfilGrade[i]}"><button onclick="removerFotoGrade(${i})" class="btn-remove-photo"><i class="fa-solid fa-x"></i></button>`;
    } else {
      div.innerHTML = `<div class="w-full h-full flex items-center justify-center cursor-pointer" onclick="document.getElementById('input-grade-foto').click()"><i class="fa-solid fa-plus text-gray-300 text-xl"></i></div>`;
    }
    container.appendChild(div);
  }
}

function processarFotoGrade(event) {
  const file = event.target.files[0];
  if (!file) return;
  if (fotosPerfilGrade.length >= 6) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    fotosPerfilGrade.push(e.target.result);
    renderizarGradeEdicao();
  };
  reader.readAsDataURL(file);
}

function removerFotoGrade(index) {
  fotosPerfilGrade.splice(index, 1);
  renderizarGradeEdicao();
}

function previewImagem(event) {
  const file = event.target.files[0];
  if(!file) return;

  const reader = new FileReader();
  reader.onload = async (e) => {
    // visual imediato
    document.getElementById('foto-preview').src = e.target.result;
    document.getElementById('modal-user-img').src = e.target.result;

    // salva no banco
    try {
      await apiPost('api_perfil_foto.php', { foto_perfil: e.target.result });
      await carregarDadosPerfil();
    } catch (err) {
      alert(err.message || 'Erro ao salvar foto');
    }
  };
  reader.readAsDataURL(file);
}

/** ========= OUTROS ========= */
function toggleDarkMode() {
  document.body.classList.toggle('dark-mode'); // sem localStorage
}

function setInteresse(tipo) {
  // por enquanto só muda o visual (sem localStorage)
  const btnH = document.getElementById('btn-homem');
  const btnM = document.getElementById('btn-mulher');
  if(tipo === 'Homem') {
    if(btnH) btnH.className = "flex-1 py-2 text-[10px] font-bold rounded-xl bg-blue-500 text-white transition-all";
    if(btnM) btnM.className = "flex-1 py-2 text-[10px] font-bold rounded-xl border border-gray-100 text-gray-400 transition-all";
  } else {
    if(btnM) btnM.className = "flex-1 py-2 text-[10px] font-bold rounded-xl bg-pink-500 text-white transition-all";
    if(btnH) btnH.className = "flex-1 py-2 text-[10px] font-bold rounded-xl border border-gray-100 text-gray-400 transition-all";
  }
}

function deletarConta() {
  alert("Eu faço o delete permanente com segurança (senha + CSRF) depois, pra não correr risco.");
}

function logout() {
  window.location.href = 'logout.php';
}

function falarComSuporte() { window.location.href = "mailto:connectfriend84@gmail.com"; }
function alterarIdioma() { alert("Idioma: Português (Brasil)"); }
</script>
</body>
</html>