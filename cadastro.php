<?php
declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";

// Gera ou recupera o token CSRF
$csrf = function_exists('csrf_token') ? csrf_token() : "";

// Redireciona se já estiver logado
if (function_exists('is_logged_in') && is_logged_in()) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connect Friends | Acesso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        :root {
            --brand-gradient: linear-gradient(135deg, #2dd4bf 0%, #3b82f6 50%, #a855f7 100%);
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
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
            margin: 0;
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
        .input-field:focus {
            border-color: #3b82f6;
            background: rgba(15, 23, 42, 0.8);
            outline: none;
        }
        .error-msg {
            color: #f87171;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 4px;
            display: none;
        }
        .tab-active { color: #fff; position: relative; }
        .tab-active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 4px;
            background: var(--brand-gradient);
            border-radius: 10px;
        }
        .fade-up { animation: fadeUp 0.6s ease-out forwards; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        #modal-recuperar {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <div class="fixed top-0 left-0 w-full h-full z-[-1]">
        <div class="absolute top-[-10%] left-[-10%] w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-[440px] fade-up">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black italic tracking-tighter gradient-text">Connect Friends</h1>
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.4em] mt-2">Private Access</p>
        </div>

        <div class="glass-card rounded-[2.5rem] p-8 lg:p-10">
            <div class="flex justify-center gap-10 mb-8">
                <button onclick="switchTab('login')" id="btn-login" class="text-xs font-black uppercase tab-active">Entrar</button>
                <button onclick="switchTab('cadastro')" id="btn-cadastro" class="text-xs font-black uppercase text-slate-500">Cadastrar</button>
            </div>

            <form id="form-login" onsubmit="handleLogin(event)" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2">E-mail Institucional</label>
                    <input type="email" required id="login-email" placeholder="seu@email.com" class="w-full px-5 py-4 rounded-2xl input-field font-medium">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Senha</label>
                    <input type="password" required id="login-senha" placeholder="••••••••" class="w-full px-5 py-4 rounded-2xl input-field font-medium">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="abrirModalRecuperar()" class="text-[10px] font-bold text-blue-400 uppercase">Esqueceu a senha?</button>
                </div>
                <button type="submit" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg active:scale-95 transition-all">
                    Acessar Plataforma
                </button>
            </form>

            <form id="form-cadastro" onsubmit="handleCadastro(event)" class="hidden space-y-5">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Nome Completo</label>
                    <input type="text" id="nome-usuario" placeholder="Ex: João Silva" class="w-full px-5 py-4 rounded-2xl input-field font-medium text-sm">
                    <div id="error-nome" class="error-msg">Informe seu nome</div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Nascimento</label>
                    <input type="date" id="data-nascimento" class="w-full px-5 py-4 rounded-2xl input-field font-medium text-sm">
                    <div id="error-idade" class="error-msg">Você precisa ter pelo menos 18 anos</div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2">E-mail</label>
                    <input type="email" id="email-cadastro" placeholder="seu@email.com" class="w-full px-5 py-4 rounded-2xl input-field font-medium text-sm" required>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Definir Senha</label>
                    <input type="password" id="senha-cadastro" placeholder="8+ caracteres, número e símbolo" class="w-full px-5 py-4 rounded-2xl input-field font-medium text-sm">
                    <div id="error-senha" class="error-msg">Senha fraca: use 8+ caracteres com símbolos</div>
                </div>
                <button type="submit" class="w-full py-5 bg-white text-slate-900 rounded-2xl font-black text-xs uppercase tracking-[0.2em] active:scale-95 transition-all mt-4">
                    Criar Minha Conta
                </button>
            </form>

            <div class="relative flex py-8 items-center">
                <div class="flex-grow border-t border-slate-800"></div>
                <span class="flex-shrink mx-4 text-[9px] font-black text-slate-600 uppercase tracking-[0.2em]">Social Login</span>
                <div class="flex-grow border-t border-slate-800"></div>
            </div>

            <div class="flex gap-4">
                <button onclick="handleSocialLogin('Google', this)" class="flex-1 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl flex items-center justify-center hover:bg-slate-700">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5">
                </button>
                <button onclick="handleSocialLogin('Facebook', this)" class="flex-1 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl flex items-center justify-center hover:bg-slate-700">
                    <i class="fa-brands fa-facebook text-[#1877F2] text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="modal-recuperar">
        <div class="glass-card w-full max-w-[400px] rounded-[2rem] p-8 space-y-6">
            <div class="text-center">
                <i class="fa-solid fa-envelope-open-text text-3xl text-blue-500 mb-4"></i>
                <h3 class="text-xl font-black uppercase">Recuperar Senha</h3>
            </div>
            <div class="space-y-4">
                <input type="email" id="email-recuperacao" placeholder="Seu e-mail" class="w-full px-5 py-4 rounded-2xl input-field font-medium text-sm">
                <button onclick="enviarCodigoRecuperacao(this)" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase">Enviar Link</button>
                <button onclick="fecharModalRecuperar()" class="w-full text-[10px] font-black text-slate-500 uppercase">Voltar</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?= json_encode($csrf) ?>;

        async function apiPost(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify(payload)
            });
            const data = await response.json().catch(() => null);
            if (!data || !data.ok) throw new Error(data?.message || 'Erro no servidor');
            return data;
        }

        function switchTab(tab) {
            const loginForm = document.getElementById('form-login');
            const cadastroForm = document.getElementById('form-cadastro');
            const btnLogin = document.getElementById('btn-login');
            const btnCadastro = document.getElementById('btn-cadastro');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                cadastroForm.classList.add('hidden');
                btnLogin.classList.add('tab-active');
                btnCadastro.classList.remove('tab-active');
            } else {
                loginForm.classList.add('hidden');
                cadastroForm.classList.remove('hidden');
                btnCadastro.classList.add('tab-active');
                btnLogin.classList.remove('tab-active');
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = 'Entrando...';

            try {
                const email = document.getElementById('login-email').value;
                const senha = document.getElementById('login-senha').value;
                const data = await apiPost('api/api_login.php', { email, senha });
                window.location.href = data.tem_perfil ? 'index.php' : 'criacaoperfil.php';
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Erro', text: err.message, background: '#0f172a', color: '#fff' });
                btn.disabled = false;
                btn.innerHTML = 'Acessar Plataforma';
            }
        }

        async function handleCadastro(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            
            // Validações básicas de frontend
            const nome = document.getElementById('nome-usuario').value;
            const dataNasc = document.getElementById('data-nascimento').value;
            const email = document.getElementById('email-cadastro').value;
            const senha = document.getElementById('senha-cadastro').value;

            const idade = dataNasc ? (new Date().getFullYear() - new Date(dataNasc).getFullYear()) : 0;

            if (!nome) { document.getElementById('error-nome').style.display = 'block'; return; }
            if (idade < 18) { document.getElementById('error-idade').style.display = 'block'; return; }

            btn.disabled = true;
            btn.innerHTML = 'Criando Conta...';

            try {
                // Aqui o POST vai direto para o cadastro do banco
                await apiPost('api/api_cadastro.php', {
                    username: nome,
                    email: email,
                    senha: senha,
                    data_nascimento: dataNasc
                });

                // Sucesso direto: vai para a criação de perfil
                window.location.href = 'criacaoperfil.php';
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Erro', text: err.message, background: '#0f172a', color: '#fff' });
                btn.disabled = false;
                btn.innerHTML = 'Criar Minha Conta';
            }
        }

        // Funções de modal e social login mantidas para funcionamento da UI
        function abrirModalRecuperar() { document.getElementById('modal-recuperar').style.display = 'flex'; }
        function fecharModalRecuperar() { document.getElementById('modal-recuperar').style.display = 'none'; }
        function handleSocialLogin(p, btn) { btn.innerHTML = '...'; setTimeout(() => window.location.href='index.php', 800); }
    </script>
</body>
</html>