const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const rateLimit = require('express-rate-limit');

const app = express();

// --- CONFIGURAÇÕES GERAIS ---
// Permite que o seu HTML (mesmo no Netlify) converse com este servidor
app.use(cors());
app.use(express.json());

// --- CONFIGURAÇÃO ANTI-SPAM ---
const recuperacaoLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 5, // Aumentei para 5 tentativas para facilitar seus testes
  message: {
    error: "Muitas tentativas.",
    message: "Aguarde um pouco antes de solicitar um novo código."
  },
  standardHeaders: true,
  legacyHeaders: false,
});

// --- CONFIGURAÇÃO DO GMAIL (VERSÃO ESTÁVEL PARA RENDER) ---
const transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: 'connectfriendsofc@gmail.com',
    pass: 'dgyirpyyvmjiamvf' // Sua Senha de App de 16 dígitos
  },
  tls: {
    // Essencial para evitar erros de conexão em servidores cloud
    rejectUnauthorized: false
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
    <div style="font-family: sans-serif; background-color: #0f172a; padding: 40px; color: #f1f5f9; text-align: center;">
        <h1 style="color: #3b82f6;">Connect Friends</h1>
        <div style="background-color: #1e293b; padding: 30px; border-radius: 20px; display: inline-block;">
            <p>Seu código de verificação é:</p>
            <h2 style="font-size: 32px; letter-spacing: 5px; color: #ffffff;">${codigo}</h2>
        </div>
    </div>
    `
  };

  console.log(`Solicitação recebida para: ${email}`);

  transporter.sendMail(mailOptions, (error, info) => {
    if (error) {
      console.error("!!! ERRO AO ENVIAR E-MAIL !!!");
      console.error("Detalhes:", error.message);
      return res.status(500).json({ error: "Falha no servidor de e-mail", details: error.message });
    }
    
    console.log("SUCESSO: E-mail enviado para " + email);
    res.status(200).json({ message: 'Enviado!', codigo: codigo });
  });
});

// --- INICIALIZAÇÃO ---
// O Render define a porta automaticamente. Se não houver, usa a 3000 localmente.
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 SERVIDOR CONNECT FRIENDS RODANDO NA PORTA ${PORT}`);
});