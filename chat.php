<?php
declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";
require_login();

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Chat | Connect Friends</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

        /* Ajuste para funcionar dentro da div main do index */
        .chat-container {
            height: 100%;
            display: flex;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        .foto-grade-detalhe {
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 1rem;
            background-color: #334155;
            width: 100%;
        }

        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }

        /* Estilo para feedback visual ao segurar a mensagem */
        .msg-bubble-active:active {
            transform: scale(0.96);
            transition: transform 0.1s;
            opacity: 0.8;
        }

        @media (max-width: 1024px) {
            #inbox-list, #active-chat {
                position: absolute;
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
            }
            #active-chat:not(.hidden) {
                z-index: 50;
                display: flex !important;
            }
        }
    </style>
</head>
<body class="bg-[#0f172a]">

<div class="chat-container bg-[#0f172a] lg:p-4 gap-4 h-full">

    <aside id="inbox-list" class="w-full lg:w-96 bg-[#1e293b] flex flex-col lg:rounded-3xl shadow-sm border border-slate-800 overflow-hidden shrink-0">
        <div class="p-6 border-b border-slate-800">
            <h2 class="text-2xl font-black text-white tracking-tight">Mensagens</h2>
        </div>

        <div class="p-4 border-b border-slate-800 bg-[#0f172a]/50">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 px-2">Novos Connects</p>
            <div id="carrossel-matches" class="flex gap-4 overflow-x-auto no-scrollbar pb-2 px-2">

                <div onclick="abrirCheckout()" class="flex-shrink-0 flex flex-col items-center gap-1 cursor-pointer group">
                    <div class="w-16 h-16 rounded-full p-1 border-2 border-amber-400 bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center relative overflow-hidden active:scale-95 transition">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100" class="absolute inset-0 w-full h-full object-cover blur-sm opacity-40">
                        <span id="contador-curtidas-real" class="relative z-10 text-white font-black text-xs shadow-sm">0</span>
                    </div>
                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-tighter">Curtidas</span>
                </div>

            </div>
        </div>

        <div id="container-lista-chats" class="flex-1 overflow-y-auto no-scrollbar"></div>
    </aside>

    <main id="active-chat" class="hidden flex-1 flex-col bg-[#1e293b] lg:rounded-3xl shadow-sm border border-slate-800 overflow-hidden relative">
        <div id="chat-header-clickable" class="p-4 lg:p-5 border-b border-slate-800 flex items-center justify-between bg-[#1e293b]/90 backdrop-blur-md z-10 cursor-pointer hover:bg-slate-800/50 transition">
            <div class="flex items-center gap-4">
                <button onclick="event.stopPropagation(); window.voltarParaListaChat()" class="lg:hidden p-2 -ml-2 text-slate-400"><i class="fa-solid fa-chevron-left text-lg"></i></button>
                <img id="header-foto-chat" src="" class="w-10 h-10 rounded-full object-cover border-2 border-slate-700">
                <div>
                    <h3 id="header-nome-chat" class="font-bold text-white leading-tight"></h3>
                    <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest">Online</p>
                </div>
            </div>
        </div>

        <div id="lista-mensagens-chat" class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-4 no-scrollbar bg-[#0f172a]"></div>

        <div class="p-4 bg-[#1e293b] border-t border-slate-800 relative pb-safe">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <button onclick="toggleMenuFoto()" class="text-slate-400 hover:text-white transition p-2">
                        <i class="fa-solid fa-camera text-xl"></i>
                    </button>
                    <div id="menu-foto" class="hidden absolute bottom-full left-0 mb-4 w-52 bg-[#1e293b] border border-slate-700 rounded-2xl shadow-2xl overflow-hidden z-[100]">
                        <button onclick="document.getElementById('input-file-foto').click(); toggleMenuFoto()" class="w-full flex items-center gap-3 p-4 hover:bg-slate-800 transition text-left">
                            <i class="fa-solid fa-image text-blue-400"></i>
                            <span class="text-xs font-bold text-slate-200">Foto Normal</span>
                        </button>
                        <div class="h-[1px] bg-slate-800"></div>
                        <button onclick="document.getElementById('input-file-temp').click(); toggleMenuFoto()" class="w-full flex items-center gap-3 p-4 hover:bg-slate-800 transition text-left">
                            <i class="fa-solid fa-bolt text-amber-400"></i>
                            <span class="text-xs font-bold text-slate-200">Visualização Única</span>
                        </button>
                    </div>
                </div>
                <input type="text" id="input-chat-texto" placeholder="Sua mensagem..." class="flex-1 bg-[#0f172a] text-white border-none rounded-2xl py-3 px-5 text-sm outline-none focus:ring-1 focus:ring-blue-500/50">
                <button onclick="window.enviarMensagemTexto()" class="w-11 h-11 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-blue-500 transition shrink-0 active:scale-90">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </button>
                <input type="file" id="input-file-foto" class="hidden" accept="image/*" onchange="processarImagem(event, 'normal')">
                <input type="file" id="input-file-temp" class="hidden" accept="image/*" onchange="processarImagem(event, 'temp')">
            </div>
        </div>
    </main>
</div>

<div id="modal-checkout" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="relative w-full max-w-[400px] bg-[#1e293b] rounded-[2.5rem] overflow-hidden shadow-2xl border border-slate-700">

        <div class="w-full bg-gradient-to-b from-cyan-500 to-blue-600 p-10 flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-4 border border-white/30 backdrop-blur-md">
                <i class="fa-solid fa-crown text-yellow-400 text-3xl"></i>
            </div>
            <h2 class="text-white text-3xl font-black tracking-tight">Connect Gold</h2>
            <p class="text-white/80 text-[10px] font-bold uppercase tracking-widest mt-1">Para liberar: Revelar Curtidas</p>
        </div>

        <div class="p-8 space-y-5 text-slate-200">
            <div class="flex items-center gap-4">
                <div class="w-6 h-6 rounded-full bg-blue-500/20 flex items-center justify-center"><i class="fa-solid fa-check text-[10px] text-blue-400"></i></div>
                <div>
                    <p class="font-bold text-sm">Curtidas Ilimitadas</p>
                    <p class="text-slate-400 text-[10px]">Sem limites diários</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-6 h-6 rounded-full bg-amber-500/20 flex items-center justify-center"><i class="fa-solid fa-star text-[10px] text-amber-400"></i></div>
                <div>
                    <p class="font-bold text-sm">Ver quem te curtiu</p>
                    <p class="text-slate-400 text-[10px]">Saiba antes de dar o swipe</p>
                </div>
            </div>
        </div>

        <div class="px-8 grid grid-cols-2 gap-4 mb-8 pt-4">
            <div class="border-2 border-slate-700 rounded-3xl p-4 text-center">
                <p class="text-slate-400 text-[10px] font-bold uppercase">Mensal</p>
                <p class="text-white font-black text-lg">R$ 19,90</p>
            </div>
            <div class="relative border-2 border-cyan-400 bg-cyan-400/10 rounded-3xl p-4 text-center">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-yellow-400 text-yellow-950 text-[8px] font-black px-3 py-1 rounded-full uppercase shadow-md whitespace-nowrap">Melhor Valor</div>
                <p class="text-slate-400 text-[10px] font-bold uppercase">Anual</p>
                <p class="text-white font-black text-lg">R$ 199,90</p>
            </div>
        </div>

        <div class="px-8 pb-10 flex flex-col gap-4">
            <button onclick="abrirPagamentoSimulado()" class="w-full py-4 bg-gradient-to-r from-cyan-400 to-blue-400 text-[#1e293b] font-black rounded-2xl uppercase text-xs tracking-widest shadow-lg active:scale-95 transition">Assinar Agora</button>
            <button onclick="fecharCheckout()" class="text-slate-500 text-[10px] font-bold uppercase text-center tracking-widest">Não, prefiro a version limitada</button>
        </div>
    </div>
</div>

<div id="modal-pagamento" class="hidden fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
    <div class="relative w-full max-w-[400px] bg-[#1e293b] rounded-[2.5rem] overflow-hidden shadow-2xl border border-slate-700">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <button onclick="fecharPagamento()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left"></i></button>
            <h3 class="text-white font-bold">Checkout Seguro</h3>
            <div class="w-4"></div>
        </div>
        <div class="p-8 space-y-6">
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest text-center">Forma de Pagamento</p>
            <div class="space-y-3">
                <button onclick="selecionarMetodo('Crédito')" class="w-full p-4 bg-slate-800 border border-slate-700 rounded-2xl flex items-center gap-4 hover:border-blue-500 transition group">
                    <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-white font-bold text-sm">Cartão de Crédito</p>
                    </div>
                </button>
                <button onclick="selecionarMetodo('Débito')" class="w-full p-4 bg-slate-800 border border-slate-700 rounded-2xl flex items-center gap-4 hover:border-green-500 transition group">
                    <div class="w-10 h-10 bg-green-500/10 rounded-full flex items-center justify-center text-green-400 group-hover:bg-green-500 group-hover:text-white transition">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-white font-bold text-sm">Cartão de Débito</p>
                    </div>
                </button>
            </div>
            <div id="area-dados-cartao" class="hidden space-y-4 pt-4 border-t border-slate-800">
                <input type="text" placeholder="Número do Cartão" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl py-3 px-4 text-white text-sm outline-none">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" placeholder="MM/AA" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl py-3 px-4 text-white text-sm outline-none">
                    <input type="text" placeholder="CVV" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl py-3 px-4 text-white text-sm outline-none">
                </div>
                <button onclick="finalizarCompraSimulada()" class="w-full py-4 bg-blue-600 text-white font-black rounded-2xl uppercase text-xs tracking-widest shadow-lg active:scale-95 transition">Confirmar Assinatura</button>
            </div>
        </div>
    </div>
</div>

<script>
    // =========================
    // CSRF do PHP
    // =========================
    const CSRF_TOKEN = <?= json_encode($csrf, JSON_UNESCAPED_UNICODE) ?>;

    // =========================
    // API helper (sem barras, com underscore)
    // =========================
    if (typeof window.apiFetch !== "function") {
        window.apiFetch = async function(url, options = {}) {
            const opts = {
                credentials: "include",
                headers: { ...(options.headers || {}) },
                ...options
            };
            const method = (opts.method || "GET").toUpperCase();

            // Se mandar objeto no body, vira JSON
            if (!["GET","HEAD","OPTIONS"].includes(method)) {
                opts.headers["X-CSRF-Token"] = CSRF_TOKEN;
                if (opts.body && typeof opts.body === "object" && !(opts.body instanceof FormData)) {
                    opts.headers["Content-Type"] = "application/json";
                    opts.body = JSON.stringify(opts.body);
                }
            }

            const r = await fetch(url, opts);
            const ct = r.headers.get("content-type") || "";
            const data = ct.includes("application/json")
                ? await r.json()
                : { ok: false, error: "Resposta inválida" };

            if (!data.ok && r.status === 401) {
                window.location.replace("capa.php");
            }
            return data;
        }
    }

    // =========================
    // Variáveis de controle (agora por match_id)
    // =========================
    window.activeChat = null; // { match_id, other_id, nome, foto }

    // Funções de Interface
    function toggleMenuFoto() { document.getElementById('menu-foto').classList.toggle('hidden'); }
    function abrirCheckout() { document.getElementById('modal-checkout').classList.remove('hidden'); }
    function fecharCheckout() { document.getElementById('modal-checkout').classList.add('hidden'); }

    // --- FUNÇÕES DE PAGAMENTO (mantidas) ---
    function abrirPagamentoSimulado() {
        fecharCheckout();
        document.getElementById('modal-pagamento').classList.remove('hidden');
    }
    function fecharPagamento() {
        document.getElementById('modal-pagamento').classList.add('hidden');
        document.getElementById('area-dados-cartao').classList.add('hidden');
    }
    function selecionarMetodo(tipo) {
        document.getElementById('area-dados-cartao').classList.remove('hidden');
    }
    function finalizarCompraSimulada() {
        Swal.fire({
            title: 'Processando...',
            text: 'Validando dados do cartão',
            timer: 2000,
            timerProgressBar: true,
            background: '#1e293b',
            color: '#ffffff',
            didOpen: () => { Swal.showLoading(); }
        }).then(() => {
            Swal.fire({
                title: 'Assinatura Ativa!',
                text: 'Você agora é um membro Connect Gold.',
                icon: 'success',
                background: '#1e293b',
                color: '#ffffff',
                confirmButtonColor: '#3b82f6'
            });
            fecharPagamento();
            const elContador = document.getElementById('contador-curtidas-real');
            if (elContador) elContador.innerText = "ILIMITADO";
        });
    }

    // =========================
    // Curtidas reais (BANCO)
    // =========================
    async function atualizarContadorCurtidasReal() {
        const res = await window.apiFetch("api_likes_received_count.php", { method: "GET" });
        const el = document.getElementById("contador-curtidas-real");
        if (!el) return;

        if (res && res.ok) {
            const total = Number(res.total || 0);
            el.innerText = total > 99 ? "99+" : String(total);
        } else {
            el.innerText = "0";
        }
    }

    // =========================
    // Denúncia (segurar mensagem) -> banco
    // =========================
    function aplicarEventoDenuncia(elemento, mensagemId = null) {
        let timerDenuncia;
        const tempoSegurar = 700;

        const dispararDenuncia = () => {
            Swal.fire({
                title: 'Denunciar mensagem?',
                text: "Esta mensagem será enviada para análise e o usuário poderá ser banido.",
                icon: 'shield-halved',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Denunciar',
                cancelButtonText: 'Cancelar',
                background: '#1e293b',
                color: '#ffffff',
                borderRadius: '1.5rem'
            }).then(async (result) => {
                if (!result.isConfirmed) return;

                // Se não tiver id (render otimista), só feedback
                if (!mensagemId) {
                    Swal.fire({
                        title: 'Denúncia Enviada',
                        text: 'Obrigado por manter nossa comunidade segura.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#1e293b',
                        color: '#ffffff'
                    });
                    return;
                }

                const res = await window.apiFetch("api_denunciar_mensagem.php", {
                    method: "POST",
                    body: { mensagem_id: mensagemId, motivo: "mensagem_inapropriada" }
                });

                Swal.fire({
                    title: res && res.ok ? 'Denúncia Enviada' : 'Erro',
                    text: res && res.ok ? 'Obrigado por manter nossa comunidade segura.' : (res?.error || 'Não foi possível denunciar agora.'),
                    icon: res && res.ok ? 'success' : 'error',
                    timer: 2200,
                    showConfirmButton: false,
                    background: '#1e293b',
                    color: '#ffffff'
                });
            });
        };

        // Desktop
        elemento.addEventListener('mousedown', () => { timerDenuncia = setTimeout(dispararDenuncia, tempoSegurar); });
        elemento.addEventListener('mouseup', () => clearTimeout(timerDenuncia));
        elemento.addEventListener('mouseleave', () => clearTimeout(timerDenuncia));
        elemento.addEventListener('contextmenu', (e) => e.preventDefault());

        // Mobile
        elemento.addEventListener('touchstart', () => { timerDenuncia = setTimeout(dispararDenuncia, tempoSegurar); }, {passive: true});
        elemento.addEventListener('touchend', () => clearTimeout(timerDenuncia));
    }

    // Abrir Perfil ao clicar no Header do Chat (mantém comportamento)
    document.getElementById('chat-header-clickable').onclick = function() {
        if (window.parent && window.parent.abrirPerfilDetalhado && window.activeChat && window.activeChat.nome) {
            window.parent.abrirPerfilDetalhado(window.activeChat.nome);
        }
    };

    // =========================
    // Carrossel + lista chats
    // =========================
    function adicionarMatchAoCarrossel(nome, foto, match_id, other_id) {
        if (!nome) return;
        if (nome.toLowerCase().includes("caio")) return;

        const carrossel = document.getElementById('carrossel-matches');
        const idMatch = `match-thumb-${String(match_id)}`;

        if (!document.getElementById(idMatch)) {
            const divMatch = document.createElement('div');
            divMatch.id = idMatch;
            divMatch.onclick = () => window.abrirChatUnico({ match_id, other_id, nome, foto });
            divMatch.className = "flex-shrink-0 flex flex-col items-center gap-1 cursor-pointer group active:scale-95 transition";
            divMatch.innerHTML = `
                <div class="w-16 h-16 rounded-full p-0.5 border-2 border-blue-500 overflow-hidden shadow-lg">
                    <img src="${foto}" class="w-full h-full object-cover rounded-full">
                </div>
                <span class="text-[10px] font-bold text-slate-300 truncate w-16 text-center">${nome}</span>
            `;
            carrossel.appendChild(divMatch);
        }
    }

    function verificarCriarCardLista(chat) {
        const { match_id, nome, foto, ultimaMensagem } = chat;
        if (!nome) return;
        if (nome.toLowerCase().includes("caio")) return;

        const idCardMsg = `msg-lista-${String(match_id)}`;
        const idContainer = `card-container-${String(match_id)}`;

        if (!document.getElementById(idContainer)) {
            const container = document.getElementById('container-lista-chats');
            const novoCard = document.createElement('div');
            novoCard.id = idContainer;
            novoCard.className = "flex items-center gap-4 p-4 hover:bg-slate-800/40 cursor-pointer border-b border-slate-800 transition";
            novoCard.onclick = () => window.abrirChatUnico(chat);

            novoCard.innerHTML = `
                <img src="${foto}" class="w-14 h-14 rounded-full object-cover">
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-white">${nome}</h4>
                    <p id="${idCardMsg}" class="text-xs text-slate-400 truncate">${ultimaMensagem || "Conversa iniciada"}</p>
                </div>
            `;
            container.prepend(novoCard);
        } else {
            atualizarUltimaMensagemLista(match_id, ultimaMensagem || "");
        }
    }

    function atualizarUltimaMensagemLista(match_id, texto) {
        const el = document.getElementById(`msg-lista-${String(match_id)}`);
        if (el && texto) el.innerText = texto;
    }

    // =========================
    // Abrir chat
    // =========================
    window.abrirChatUnico = async function(chat) {
        window.activeChat = chat;

        const chatElement = document.getElementById('active-chat');
        chatElement.classList.remove('hidden');
        chatElement.style.display = 'flex';

        document.getElementById('header-nome-chat').innerText = chat.nome || "";
        document.getElementById('header-foto-chat').src = chat.foto || "";
        document.getElementById('lista-mensagens-chat').innerHTML = "";

        await carregarHistorico(chat.match_id);

        if (window.innerWidth < 1024) {
            document.getElementById('inbox-list').classList.add('hidden');
        }

        const lista = document.getElementById('lista-mensagens-chat');
        setTimeout(() => { lista.scrollTop = lista.scrollHeight; }, 100);
    };

    window.voltarParaListaChat = function() {
        document.getElementById('inbox-list').classList.remove('hidden');
        const chatElement = document.getElementById('active-chat');
        chatElement.classList.add('hidden');
        chatElement.style.display = 'none';
    };

    // =========================
    // Moderação de texto (mantida)
    // =========================
    const TERMOS_PROIBIDOS = ["palavra1", "palavra2", "ofensa1", "ofensa2"];

    function validarConteudoTexto(texto) {
        const t = texto.toLowerCase();
        return TERMOS_PROIBIDOS.some(termo => t.includes(termo));
    }

    // =========================
    // Render mensagens (sem XSS)
    // =========================
    function escapeHtml(str) {
        return String(str)
            .replaceAll("&","&amp;")
            .replaceAll("<","&lt;")
            .replaceAll(">","&gt;")
            .replaceAll('"',"&quot;")
            .replaceAll("'","&#039;");
    }

    function renderizarMensagem(conteudo, remetente, mensagemId = null) {
        const lista = document.getElementById('lista-mensagens-chat');
        const div = document.createElement('div');
        div.className = remetente === 'eu' ? "flex justify-end w-full" : "flex justify-start w-full";

        const bubble = document.createElement('div');
        bubble.className = `msg-bubble-active select-none bg-blue-600 text-white p-4 max-w-[80%] text-sm font-medium rounded-2xl ${remetente === 'eu' ? 'rounded-tr-none' : 'rounded-tl-none'} shadow-lg cursor-pointer`;
        bubble.innerHTML = escapeHtml(conteudo);

        aplicarEventoDenuncia(bubble, mensagemId);
        div.appendChild(bubble);

        lista.appendChild(div);
        lista.scrollTop = lista.scrollHeight;
    }

    function renderizarMensagemImagem(src, remetente, tipo, mensagemId = null) {
        const lista = document.getElementById('lista-mensagens-chat');
        const div = document.createElement('div');
        div.className = remetente === 'eu' ? "flex justify-end w-full" : "flex justify-start w-full";

        let extraClass = tipo === 'temp' ? 'blur-md cursor-pointer' : '';
        let iconTemp = tipo === 'temp' ? '<div class="absolute inset-0 flex items-center justify-center"><i class="fa-solid fa-bolt text-amber-400 text-3xl"></i></div>' : '';

        const containerImg = document.createElement('div');
        containerImg.className = "msg-bubble-active relative max-w-[70%] rounded-2xl overflow-hidden border-2 border-slate-700 shadow-xl cursor-pointer select-none";
        containerImg.innerHTML = `
            <img src="${src}" class="${extraClass} w-full h-auto block">
            ${iconTemp}
        `;

        aplicarEventoDenuncia(containerImg, mensagemId);

        if (tipo === 'temp') {
            containerImg.onclick = () => {
                const overlay = document.createElement('div');
                overlay.className = "fixed inset-0 z-[10001] bg-black flex items-center justify-center p-4";
                overlay.innerHTML = `<img src="${src}" class="max-w-full max-h-full rounded-lg shadow-2xl">`;
                overlay.onclick = () => {
                    overlay.remove();
                    containerImg.innerHTML = `<div class="bg-slate-800 text-slate-500 p-4 rounded-2xl text-xs italic">Foto visualizada</div>`;
                    containerImg.onclick = null;
                };
                document.body.appendChild(overlay);
            };
        }

        div.appendChild(containerImg);
        lista.appendChild(div);
        lista.scrollTop = lista.scrollHeight;
    }

    // =========================
    // Banco: lista chats/matches
    // =========================
    async function carregarListaChats() {
        const res = await window.apiFetch("api_chats_list.php", { method: "GET" });
        if (!res || !res.ok) return;

        // limpa lista/carrossel (mantém o card curtidas)
        const container = document.getElementById("container-lista-chats");
        container.innerHTML = "";

        const carrossel = document.getElementById("carrossel-matches");
        [...carrossel.querySelectorAll("[id^='match-thumb-']")].forEach(el => el.remove());

        (res.chats || []).forEach(chat => {
            adicionarMatchAoCarrossel(chat.nome, chat.foto, chat.match_id, chat.other_id);
            verificarCriarCardLista(chat);
        });
    }

    // =========================
    // Banco: histórico de mensagens
    // =========================
    async function carregarHistorico(match_id) {
        const res = await window.apiFetch(`api_messages_list.php?match_id=${encodeURIComponent(match_id)}`, { method: "GET" });
        if (!res || !res.ok) return;

        const msgs = res.messages || [];
        msgs.forEach(m => {
            const remetente = m.is_me ? "eu" : "outro";
            if (m.tipo === "texto") renderizarMensagem(m.mensagem || "", remetente, m.id);
            else renderizarMensagemImagem(m.imagem || "", remetente, (m.visualizacao_unica ? "temp" : "normal"), m.id);
        });
    }

    // =========================
    // Enviar texto (BANCO)
    // =========================
    window.enviarMensagemTexto = async function() {
        const input = document.getElementById('input-chat-texto');
        const texto = input.value.trim();

        if (!texto || !window.activeChat || !window.activeChat.match_id) return;

        if (validarConteudoTexto(texto)) {
            Swal.fire({
                title: 'Mensagem Bloqueada',
                text: 'Sua mensagem contém termos que violam nossas diretrizes.',
                icon: 'error',
                background: '#1e293b',
                color: '#ffffff',
                confirmButtonColor: '#3b82f6'
            });
            input.value = "";
            return;
        }

        // render otimista
        renderizarMensagem(texto, 'eu', null);
        input.value = "";

        const res = await window.apiFetch("api_message_send.php", {
            method: "POST",
            body: {
                match_id: window.activeChat.match_id,
                tipo: "texto",
                mensagem: texto
            }
        });

        if (!res || !res.ok) {
            Swal.fire({
                title: 'Erro',
                text: res?.error || 'Não foi possível enviar agora.',
                icon: 'error',
                background: '#1e293b',
                color: '#ffffff'
            });
        } else {
            atualizarUltimaMensagemLista(window.activeChat.match_id, texto);
        }
    };

    // =========================
    // Enviar imagem (BANCO)
    // =========================
    function processarImagem(event, modo) {
        const file = event.target.files[0];
        if (!file || !window.activeChat || !window.activeChat.match_id) return;

        Swal.fire({
            title: 'Confirmar envio?',
            text: "Fotos que violem as regras de convivência podem resultar no bloqueio da sua conta.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Enviar Foto',
            cancelButtonText: 'Cancelar',
            background: '#1e293b',
            color: '#ffffff',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#475569',
        }).then((result) => {
            if (!result.isConfirmed) return;

            const reader = new FileReader();
            reader.onload = async function(e) {
                const img = e.target.result;

                // render otimista
                renderizarMensagemImagem(img, 'eu', (modo === "temp" ? "temp" : "normal"), null);
                atualizarUltimaMensagemLista(window.activeChat.match_id, "📷 Foto");

                const res = await window.apiFetch("api_message_send.php", {
                    method: "POST",
                    body: {
                        match_id: window.activeChat.match_id,
                        tipo: "imagem",
                        imagem: img,
                        visualizacao_unica: (modo === "temp") ? 1 : 0
                    }
                });

                if (!res || !res.ok) {
                    Swal.fire({
                        title: 'Erro',
                        text: res?.error || 'Não foi possível enviar agora.',
                        icon: 'error',
                        background: '#1e293b',
                        color: '#ffffff'
                    });
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // =========================
    // Bloquear usuário (BANCO)
    // =========================
    window.bloquearUsuario = function() {
        const nomeAlvo = window.activeChat?.nome || "";
        const otherId = window.activeChat?.other_id || 0;
        const matchId = window.activeChat?.match_id || 0;
        if (!otherId || !matchId) return;

        Swal.fire({
            title: 'Bloquear usuário?',
            text: `Você tem certeza que deseja bloquear ${nomeAlvo}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Sim, bloquear',
            cancelButtonText: 'Cancelar',
            background: '#1e293b',
            color: '#ffffff',
            borderRadius: '1.5rem'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            const res = await window.apiFetch("api_block_user.php", {
                method: "POST",
                body: { match_id: matchId, bloqueado_id: otherId }
            });

            if (res && res.ok) {
                Swal.fire({
                    title: 'Bloqueado!',
                    text: 'Usuário removido com sucesso.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    background: '#1e293b',
                    color: '#ffffff',
                    borderRadius: '1.5rem'
                });
                window.voltarParaListaChat();
                await carregarListaChats();
            } else {
                Swal.fire({
                    title: 'Erro',
                    text: res?.error || 'Não foi possível bloquear agora.',
                    icon: 'error',
                    background: '#1e293b',
                    color: '#ffffff'
                });
            }
        });
    };

    // =========================
    // INIT
    // =========================
    (async function initChat() {
        await carregarListaChats();
        await atualizarContadorCurtidasReal();

        document.getElementById('input-chat-texto').onkeydown = (e) => {
            if (e.key === 'Enter') window.enviarMensagemTexto();
        };
    })();
</script>
</body>
</html>