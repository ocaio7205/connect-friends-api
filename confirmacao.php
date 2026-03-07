<?php
require_once __DIR__ . "/bootstrap.php";

$csrf  = csrf_token();
$email = (string)($_SESSION['pending_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Friends | Verificação</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #0f172a; 
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0;
            overflow: hidden;
        }

        .glass-card { 
            background: rgba(30, 41, 59, 0.7); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .code-input { 
            width: 45px; 
            height: 55px; 
            text-align: center; 
            font-size: 1.5rem; 
            font-weight: bold; 
            background: #0f172a; 
            border: 2px solid #334155; 
            border-radius: 12px; 
            color: #2dd4bf; 
            transition: all 0.2s ease;
        }

        .code-input:focus { 
            border-color: #3b82f6; 
            outline: none; 
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); 
            transform: scale(1.05);
        }

        .bg-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 50% 50%, #1e293b 0%, #0f172a 100%);
        }

        /* Efeito de carregamento para o botão */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="w-full max-w-md p-6">
        <div class="glass-card rounded-[2.5rem] p-10 text-center">
            <div class="mb-6">
                <div class="w-16 h-16 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-shield-halved text-blue-400 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-black mb-2">Verifique seu E-mail</h2>
                <p class="text-slate-400 text-sm">Digite o código enviado para <br>
                    <span id="display-email" class="text-blue-400 font-bold"></span>
                </p>
            </div>
            
            <div class="flex justify-between gap-2 mb-8" id="otp-container">
                <input type="text" maxlength="1" class="code-input" autocomplete="one-time-code">
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
            </div>

            <button id="btn-confirmar" onclick="verificarCodigo()" class="w-full py-4 bg-blue-600 hover:bg-blue-500 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-[0.98]">
                Confirmar Identidade
            </button>

            <p class="mt-6 text-[10px] text-slate-500 uppercase font-bold tracking-widest">
                Não recebeu? <a href="#" onclick="reenviarCodigo()" class="text-blue-500 hover:underline">Reenviar código</a>
            </p>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?= json_encode($csrf) ?>;
        const EMAIL_PENDENTE = <?= json_encode($email) ?>;

        document.getElementById('display-email').innerText = EMAIL_PENDENTE || "seu e-mail";

        const inputs = document.querySelectorAll('.code-input');

        // Navegação automática
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.inputType === "deleteContentBackward") return;
                if (input.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

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

        // Reenvia código (server-side)
        async function reenviarCodigo() {
            if (!EMAIL_PENDENTE) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ops!',
                    text: 'Não encontrei o e-mail pendente na sessão. Volte e tente novamente.',
                    background: '#1e293b', color: '#fff'
                });
                return;
            }

            Swal.fire({
                title: 'Enviando...',
                text: 'Aguarde um momento',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                await apiPost('api/api_email_reenviar.php', {});

                Swal.fire({ 
                    icon: 'success', 
                    title: 'Enviado!', 
                    text: 'Um novo código foi enviado para seu e-mail.',
                    background: '#1e293b', color: '#fff' 
                });
            } catch (error) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Ops!', 
                    text: error.message || 'Erro ao conectar com o servidor.',
                    background: '#1e293b', color: '#fff' 
                });
            }
        }

        async function verificarCodigo() {
            let codigoDigitado = "";
            inputs.forEach(input => codigoDigitado += (input.value || ""));

            if (codigoDigitado.length < 6) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Incompleto', 
                    text: 'Digite os 6 números.', 
                    background: '#1e293b', color: '#fff'
                });
                return;
            }

            try {
                await apiPost('api/api_email_verificar.php', { codigo: codigoDigitado });

                Swal.fire({ 
                    icon: 'success', 
                    title: 'Acesso Liberado!', 
                    timer: 1500, 
                    showConfirmButton: false,
                    background: '#1e293b', color: '#fff' 
                });

                setTimeout(() => {
                    window.location.href = 'criacaoperfil.php';
                }, 1500);

            } catch (error) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Código Errado', 
                    text: error.message || 'Tente novamente.', 
                    background: '#1e293b', color: '#fff'
                });

                inputs.forEach(input => input.value = "");
                inputs[0].focus();
            }
        }
    </script>
</body>
</html>