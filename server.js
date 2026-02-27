const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const rateLimit = require('express-rate-limit');

const app = express();

// --- CONFIGURAÇÕES GERAIS ---
// Permite que o seu HTML (mesmo no Netlify) converse com este servidor
app.use(cors());
app.use(express.json());

// --- CONFIGURAÇÃO ANTI-SPAM / ANTI-FAKE ---
// Protege seu servidor de ser inundado por pedidos falsos
const recuperacaoLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // Janela de 15 minutos
  max: 3, // Limita cada IP a 3 solicitações por janela
  message: {
    error: "Muitas tentativas detectadas.",
    message: "Por segurança, aguarde 15 minutos antes de solicitar um novo código."
  },
  standardHeaders: true,
  legacyHeaders: false,
});

// --- CONFIGURAÇÃO DO GMAIL ---
const transporter = nodemailer.createTransport({
  service: 'gmail',
  host: 'smtp.gmail.com',
  port: 465,
  secure: true,
  auth: {
    user: 'connectfriendsofc@gmail.com', 
    pass: 'dgyirpyyvmjiamvf' // Sua Senha de App configurada
  },
  tls: {
    rejectUnauthorized: false
  }
});

// --- ROTA DE RECUPERAÇÃO DE SENHA ---
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
    subject: '🔐 Seu Código de Recuperação Connect Friends',
    html: `
    <div style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #0f172a; padding: 40px; color: #f1f5f9; text-align: center; border-radius: 20px;">
        <div style="margin-bottom: 20px;">
            <h1 style="color: #3b82f6; font-style: italic; font-size: 28px; margin: 0; letter-spacing: -1px;">Connect Friends</h1>
            <p style="color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; margin-top: 5px;">Private Access Platform</p>
        </div>
        
        <div style="background-color: #1e293b; border: 1px solid #334155; padding: 30px; border-radius: 24px; max-width: 400px; margin: 0 auto;">
            <h2 style="font-size: 18px; font-weight: 800; text-transform: uppercase; margin-bottom: 10px; color: #ffffff;">Verificação</h2>
            <p style="color: #94a3b8; font-size: 14px; line-height: 1.5;">Você solicitou a recuperação de acesso. Utilize o código de 6 dígitos abaixo:</p>
            
            <div style="background: linear-gradient(135deg, #2dd4bf 0%, #3b82f6 100%); padding: 20px; border-radius: 16px; margin: 25px 0; display: block;">
                <span style="font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #ffffff;">${codigo}</span>
            </div>
            
            <p style="color: #64748b; font-size: 11px;">Este código é pessoal e expira em breve. Não o compartilhe com ninguém.</p>
        </div>
        
        <div style="margin-top: 30px; border-top: 1px solid #1e293b; padding-top: 20px;">
            <p style="color: #475569; font-size: 10px;">&copy; 2026 Connect Friends. Todos os direitos reservados.</p>
        </div>
    </div>
    `
  };

  console.log(`Tentando enviar e-mail para: ${email}...`);

  transporter.sendMail(mailOptions, (error, info) => {
    if (error) {
      console.log("---------- ERRO NO ENVIO ----------");
      console.log(error); 
      return res.status(500).json({ error: "Falha no servidor de e-mail" });
    }
    
    console.log("SUCESSO: E-mail enviado para " + email);
    res.status(200).json({ message: 'Enviado!', codigo: codigo });
  });
});

// --- INICIALIZAÇÃO DO SERVIDOR ---
// O Render exige que a porta seja dinâmica via process.env.PORT
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`=========================================`);
  console.log(`🚀 SERVIDOR CONNECT FRIENDS ATIVO`);
  console.log(`📡 Porta: ${PORT}`);
  console.log(`🛡️  Filtro anti-spam ativado`);
  console.log(`=========================================`);
});