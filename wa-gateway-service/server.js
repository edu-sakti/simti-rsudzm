require('dotenv').config();
const express = require('express');
// Fix for Node 18 where global crypto might be undefined for baileys
if (typeof globalThis.crypto === 'undefined') {
  globalThis.crypto = require('crypto').webcrypto;
}
const fs = require('fs');
const path = require('path');
const QRCode = require('qrcode');
const { makeWASocket, useMultiFileAuthState, fetchLatestBaileysVersion, DisconnectReason } = require('@whiskeysockets/baileys');

const app = express();
app.use(express.json());

let sock = null;
let lastQr = null;
let status = 'disconnected';
const authDir = path.resolve(__dirname, 'auth');
const API_TOKEN = process.env.WA_GATEWAY_TOKEN || '';
let lastSendAt = 0;
let sendQueue = Promise.resolve();
let sentCount = 0;
let connectedPhone = null;
let lastActiveAt = null;

const hasAuth = () => {
  try {
    return fs.existsSync(path.join(authDir, 'creds.json'));
  } catch (e) {
    return false;
  }
};

async function initSocket() {
  if (sock) return sock;

  const { state, saveCreds } = await useMultiFileAuthState('./auth');
  const { version } = await fetchLatestBaileysVersion();

  sock = makeWASocket({
    version,
    auth: state,
    printQRInTerminal: false
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', (update) => {
    const { connection, qr, lastDisconnect } = update;
    if (qr) {
      lastQr = qr;
      status = 'qr';
    }
    if (connection === 'open') {
      status = 'connected';
      lastQr = null;
      lastActiveAt = new Date().toISOString();
      try {
        const me = sock.user;
        const id = me?.id || '';
        connectedPhone = id ? id.split('@')[0] : null;
      } catch (e) {
        connectedPhone = null;
      }
    }
    if (connection === 'close') {
      const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
      status = 'disconnected';
      lastActiveAt = new Date().toISOString();
      connectedPhone = null;
      sock = null;
      if (shouldReconnect) {
        initSocket().catch(() => {});
      }
    }
  });

  return sock;
}

app.get('/health', (req, res) => {
  res.json({ ok: true, status, hasAuth: hasAuth(), sentCount, phone: connectedPhone, lastActiveAt });
});

app.post('/connect', async (req, res) => {
  try {
    await initSocket();
    res.json({ ok: true, status });
  } catch (e) {
    res.status(500).json({ ok: false, message: 'Gagal memulai koneksi.' });
  }
});

app.get('/status', (req, res) => {
  res.json({ ok: true, status, hasAuth: hasAuth(), sentCount, phone: connectedPhone, lastActiveAt });
});

app.get('/qr', async (req, res) => {
  if (!lastQr) {
    return res.status(404).json({ ok: false, message: 'QR belum tersedia.' });
  }
  try {
    const dataUrl = await QRCode.toDataURL(lastQr);
    res.json({ ok: true, qr: dataUrl });
  } catch (e) {
    res.status(500).json({ ok: false, message: 'Gagal membuat QR.' });
  }
});

app.post('/send', async (req, res) => {
  try {
    const token = req.header('x-api-key') || '';
    if (API_TOKEN && token !== API_TOKEN) {
      return res.status(401).json({ ok: false, message: 'Token tidak valid.' });
    }
    const { phone, message } = req.body || {};
    if (!phone || !message) {
      return res.status(422).json({ ok: false, message: 'phone dan message wajib diisi.' });
    }
    if (!sock || status !== 'connected') {
      return res.status(400).json({ ok: false, message: 'Gateway belum terhubung.' });
    }
    const jid = String(phone).replace(/\D/g, '') + '@s.whatsapp.net';
    const text = String(message);

    // Queue sends and enforce minimum 5 seconds between messages
    const [check] = await sock.onWhatsApp(jid);
    if (!check?.exists) {
      return res.status(400).json({ ok: false, message: 'Nomor tidak terdaftar di WhatsApp.' });
    }

    sendQueue = sendQueue
      .then(async () => {
        const now = Date.now();
        const waitMs = Math.max(0, 5000 - (now - lastSendAt));
        if (waitMs > 0) {
          await new Promise((r) => setTimeout(r, waitMs));
        }
        await sock.sendMessage(jid, { text });
        lastSendAt = Date.now();
        lastActiveAt = new Date().toISOString();
        sentCount += 1;
      })
      .catch((err) => {
        console.error('Send queue error:', err?.message || err);
      });

    await sendQueue;
    res.json({ ok: true, queued: true });
  } catch (e) {
    console.error('Send error:', e?.message || e);
    res.status(500).json({ ok: false, message: e?.message || 'Gagal mengirim pesan.' });
  }
});

app.post('/logout', async (req, res) => {
  try {
    if (sock) {
      try {
        await sock.logout();
      } catch (e) {}
      sock = null;
    }
    if (fs.existsSync(authDir)) {
      fs.rmSync(authDir, { recursive: true, force: true });
    }
    lastQr = null;
    status = 'disconnected';
    lastActiveAt = new Date().toISOString();
    connectedPhone = null;
    res.json({ ok: true });
  } catch (e) {
    res.status(500).json({ ok: false, message: 'Gagal menghapus session.' });
  }
});

const port = process.env.PORT || 3001;
app.listen(port, () => {
  console.log(`WA Gateway service listening on ${port}`);
});
