const express = require("express");
const cors = require("cors");
const rateLimit = require("express-rate-limit");
const sgMail = require("@sendgrid/mail");

const app = express();

/* ========================================
   🔐 CONFIGURAÇÃO ESSENCIAL PARA RENDER
======================================== */

app.set("trust proxy", 1);

/* ========================================
   ⚙️ CONFIGURAÇÕES GERAIS
======================================== */

app.use(cors());
app.use(express.json());

/* ========================================
   🚫 RATE LIMIT (ANTI-SPAM)
======================================== */

const recuperacaoLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 10,
  message: {
    error: "Muitas tentativas.",
    message: "Aguarde antes de tentar novamente.",
  },
  standardHeaders: true,
  legacyHeaders: false,
});

/* ========================================
   📧 CONFIGURAÇÃO SENDGRID
======================================== */

if (!process.env.SENDGRID_API_KEY) {
  console.error("❌ SENDGRID_API_KEY não encontrada!");
  process.exit(1);
}

sgMail.setApiKey(process.env.SENDGRID_API_KEY);

console.log("✅ SendGrid configurado");

/* ========================================
   📩 ROTA ENVIO DE CÓDIGO
======================================== */

app.post("/enviar-codigo", recuperacaoLimiter, async (req, res) => {
  try {
    const { email } = req.body;

    if (!email) {
      return res.status(400).json({
        error: "E-mail não fornecido",
      });
    }

    // gera código 6 dígitos
    const codigo = Math.floor(100000 + Math.random() * 900000);

    const msg = {
      to: email,
      from: "connectfriendsofc@gmail.com", // ⚠️ deve ser verificado no SendGrid
      subject: "🔐 Seu Código Connect Friends",
      html: `
      <div style="font-family: Arial; padding: 30px; text-align: center;">
        <h2>Connect Friends</h2>
        <p>Seu código de verificação é:</p>
        <h1>${codigo}</h1>
        <small>Não compartilhe este código.</small>
      </div>
      `,
    };

    console.log(`📨 Tentando enviar para ${email}`);

    await sgMail.send(msg);

    console.log(`✅ E-mail enviado para ${email}`);

    res.status(200).json({
      message: "Código enviado com sucesso",
      codigo: codigo, // remover em produção
    });

  } catch (error) {
    console.error("❌ ERRO AO ENVIAR EMAIL:");
    console.error(error.response?.body || error.message);

    res.status(500).json({
      error: "Falha no envio do e-mail",
      details: error.message,
    });
  }
});

/* ========================================
   ❤️ ROTA TESTE
======================================== */

app.get("/", (req, res) => {
  res.send("🚀 Servidor Connect Friends ONLINE");
});

/* ========================================
   🚀 INICIALIZAÇÃO SERVIDOR
======================================== */
/* ========================================
   👥 BANCO TEMPORÁRIO DE USUÁRIOS
======================================== */

let usuarios = [];

/* ========================================
   📥 LISTAR USUÁRIOS
======================================== */

app.get("/usuarios", (req, res) => {
  res.json(usuarios);
});

/* ========================================
   ➕ CRIAR USUÁRIO
======================================== */

app.post("/usuarios", (req, res) => {
  const novoUsuario = req.body;

  usuarios.push(novoUsuario);

  console.log("Novo usuário:", novoUsuario);

  res.json({
    message: "Usuário salvo com sucesso",
    usuario: novoUsuario
  });
});
const PORT = process.env.PORT || 10000;

app.listen(PORT, () => {
  console.log("=================================");
  console.log("🚀 CONNECT FRIENDS SERVER ATIVO");
  console.log("📡 Porta:", PORT);
  console.log("=================================");
});
