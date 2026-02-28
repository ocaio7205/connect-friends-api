const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const rateLimit = require('express-rate-limit');

const app = express();

// --- INTEGRIDADE DE PROXY (ESSENCIAL PARA RENDER) ---
// Isso resolve o erro de 'X-Forwarded-For' que apareceu nos seus logs
app.set('trust proxy', 1);

// --- CONFIGURAÇÕES GERAIS ---
app.use(cors());
app.use(express.json());

// --- CONFIGURAÇÃO ANTI-SPAM (AJUSTADA) ---
const recuperacaoLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // Janela de 15 minutos
  max: 10, // Aumentado para 10 para facilitar seus testes de TCC
  message: {
    error: "Muitas tentativas.",
    message: "Aguarde um pouco antes de solicitar um novo código."
  },
  standardHeaders: true,
  legacyHeaders: false,
});

// --- CONFIGURAÇÃO DO GMAIL (VERSÃO ULTRA-ESTÁVEL) ---
const transporter = nodemailer.createTransport({
  host: 'smtp.gmail.com',
  port: 465,
  secure: true, // Usa SSL/TLS (mais seguro para a porta 465)
  auth: {
    user: 'connectfriendsofc@gmail.com',
    pass: 'dgyirpyyvmjiamvf' // Sua Senha de App de 16 dígitos
  },
  // Integridades de conexão para evitar o erro de ETIMEDOUT (Tempo de conexão)
  connectionTimeout: 20000, // 20 segundos
  greetingTimeout: 20000,
  socketTimeout: 20000,
  tls: {
    rejectUnauthorized: false // Permite conexões de servidores cloud como o Render
  }
});

// --- ROTA DE ENVIO DE CÓDIGO ---
app.post('/enviar-codigo', recuperacaoLimiter, (req, res) => {
  const { email } = req.body;
  
  if (!email) {
    return res.status(400).json({ error: "E-mail não fornecido" });
  }

  // Gera um código aleatório de 6 dígitos
  const codigo = Math.floor(100000 + Math.random() * 900000);

  const mailOptions = {
    from: '"Connect Friends" <connectfriendsofc@gmail.com>', 
    to: email,
    subject: '🔐 Seu Código Connect Friends',
    html: `
    <div style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #0f172a; padding: 40px; color: #f1f5f9; text-align: center; border-radius: 20px;">
        <div style="margin-bottom: 20px;">
            <h1 style="color: #3b82f6; font-size: 28px; margin: 0;">Connect Friends</h1>
        </div>
        <div style="background-color: #1e293b; border: 1px solid #334155; padding: 30px; border-radius: 24px; display: inline-block;">
            <p style="color: #94a3b8; font-size: 14px;">Seu código de verificação é:</p>
            <div style="background: linear-gradient(135deg, #2dd4bf 0%, #3b82f6 100%); padding: 20px; border-radius: 16px; margin: 20px 0;">
                <span style="font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #ffffff;">${codigo}</span>
            </div>
            <p style="color: #64748b; font-size: 11px;">Não compartilhe este código com ninguém.</p>
        </div>
    </div>
    `
  };

  console.log(`[${new Date().toISOString()}] Tentando enviar para: ${email}`);

  transporter.sendMail(mailOptions, (error, info) => {
    if (error) {
      console.error("!!! ERRO AO ENVIAR E-MAIL !!!");
      console.error("Detalhes técnicos:", error.message);
      return res.status(500).json({ 
        error: "Falha no servidor de e-mail", 
        details: error.message 
      });
    }
    
    console.log("SUCESSO: E-mail enviado para " + email);
    res.status(200).json({ message: 'Enviado!', codigo: codigo });
  });
});

// --- ROTA DE TESTE (Para você abrir no navegador e ver se o servidor ligou) ---
app.get('/', (req, res) => {
  res.send('🚀 Servidor Connect Friends está ONLINE!');
});

// --- INICIALIZAÇÃO ---
const PORT = process.env.PORT || 10000; // Render usa frequentemente a 10000
app.listen(PORT, () => {
  console.log(`=========================================`);
  console.log(`🚀 SERVIDOR CONNECT FRIENDS ATIVO`);
  console.log(`📡 Porta: ${PORT}`);
  console.log(`=========================================`);
});