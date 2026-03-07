<?php
require_once __DIR__ . "/bootstrap.php";
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connect Friends | Redefinir Senha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #334155;
            background: rgba(15, 23, 42, 0.6);
            color: white;
            outline: none;
        }

        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .fade-up { animation: fadeUp 0.6s ease-out forwards; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="w-full max-w-[440px] fade-up">
        <div class="glass-card rounded-[2.5rem] p-8 lg:p-10">
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-key text-2xl text-blue-400"></i>
                </div>
                <h2 class="text-2xl font-black uppercase tracking-tight">Redefinir Senha</h2>
                <p class="text-slate-400 text-sm mt-2">Recupere o acesso à sua conta Connect Friends.</p>
            </div>

            <form onsubmit="validarCodigo(event)" class="space-y-5">
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">E-mail da Conta</label>
                    <div class="flex gap-2">
                        <input type="email" id="email-recuperacao" placeholder="exemplo@email.com" 
                               class="flex-1 px-5 py-4 rounded-2xl input-field text-sm">
                        <button type="button" onclick="solicitarCodigo()" class="px-4 bg-slate-800 hover:bg-slate-700 rounded-2xl transition-all border border-slate-700">
                            <i class="fa-solid fa-paper-plane text-blue-400"></i>
                        </button>
                    </div>
                </div>

                <hr class="border-slate-800 my-4">

                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">Código de 6 Dígitos</label>
                        <input type="text" id="input-codigo" maxlength="6" placeholder="000000" 
                               class="w-full px-5 py-4 rounded-2xl input-field font-black text-xl text-center tracking-[0.3em]">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">Nova Senha</label>
                        <input type="password" id="nova-senha" placeholder="Mínimo 8 caracteres" 
                               class="w-full px-5 py-4 rounded-2xl input-field text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2 tracking-widest">Confirmar Senha</label>
                        <input type="password" id="confirma-senha" placeholder="••••••••" 
                               class="w-full px-5 py-4 rounded-2xl input-field text-sm">
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="w-full py-5 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg active:scale-[0.97] transition-all">
                    Atualizar Senha
                </button>
            </form>

            <div class="text-center mt-6">
                <button onclick="window.location.href='cadastro.php'" class="text-[10px] font-bold text-slate-500 hover:text-white transition-colors uppercase tracking-widest">
                    Voltar ao Login
                </button>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?= json_encode($csrf) ?>;

        function getEmail() {
            return (document.getElementById('email-recuperacao').value || '').trim();
        }

        async function apiPost(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(payload || {})
            });

            const data = await response.json().catch(() => null);
            if (!data || !data.ok) {
                const msg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Erro ao conectar.';
                throw new Error(msg);
            }
            return data;
        }

        // 1) Solicita código (server-side)
        async function solicitarCodigo() {
            const email = getEmail();

            if (!email || !email.includes('@')) {
                Swal.fire({ icon: 'error', title: 'E-mail inválido', text: 'Digite um e-mail real.', background: '#0f172a', color: '#fff' });
                return;
            }

            Swal.fire({
                title: 'Enviando código...',
                text: 'Aguarde um instante',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                await apiPost('api_senha_solicitar.php', { email });

                Swal.fire({
                    icon: 'success',
                    title: 'Enviado!',
                    text: 'O código chegou no seu e-mail.',
                    background: '#0f172a',
                    color: '#fff'
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ops!',
                    text: error.message || 'Servidor offline ou erro no envio.',
                    background: '#0f172a',
                    color: '#fff'
                });
            }
        }

        // 2) Valida e troca senha (server-side)
        async function validarCodigo(e) {
            e.preventDefault();

            const email = getEmail();
            const codigoDigitado = (document.getElementById('input-codigo').value || '').replace(/\D+/g,'');
            const senha = (document.getElementById('nova-senha').value || '');
            const confirma = (document.getElementById('confirma-senha').value || '');

            if (!email || !email.includes('@')) {
                Swal.fire({ icon: 'error', title: 'E-mail inválido', text: 'Digite o e-mail da conta.', background: '#0f172a', color: '#fff' });
                return;
            }

            if (codigoDigitado.length !== 6) {
                Swal.fire({ icon: 'warning', title: 'Código inválido', text: 'Digite os 6 números.', background: '#0f172a', color: '#fff' });
                return;
            }

            if (senha.length < 8) {
                Swal.fire({ icon: 'warning', title: 'Senha Curta', text: 'Use pelo menos 8 caracteres.', background: '#0f172a', color: '#fff' });
                return;
            }

            if (senha !== confirma) {
                Swal.fire({ icon: 'error', title: 'Diferentes', text: 'As senhas não batem.', background: '#0f172a', color: '#fff' });
                return;
            }

            const btn = document.getElementById('btn-submit');
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Salvando...';
            btn.disabled = true;

            try {
                await apiPost('api_senha_redefinir.php', {
                    email,
                    codigo: codigoDigitado,
                    senha
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Tudo pronto!',
                    text: 'Sua senha foi redefinida com sucesso.',
                    background: '#0f172a',
                    color: '#fff'
                }).then(() => {
                    window.location.href = 'cadastro.php';
                });

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: error.message || 'Não foi possível redefinir.',
                    background: '#0f172a',
                    color: '#fff'
                });
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>