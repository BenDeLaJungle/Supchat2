import express from 'express';
import { WebSocketServer } from 'ws';
import http from 'http';
import cors from 'cors';

// Création du serveur HTTP classique
const app = express();
const server = http.createServer(app);

app.use(cors({
  origin: 'http://localhost:5173',
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type']
}));

// Création du WebSocket Server
const wss = new WebSocketServer({ server });

// Map des connexions WebSocket vers leur channel
const clientsChannels = new Map();

// Gestion des connexions WebSocket
wss.on('connection', (ws) => {
  console.log('🌟 Nouvelle connexion WebSocket !');

  ws.on('message', (event) => {
    try {
      const data = JSON.parse(event.toString());

      // Gestion abonnement à un canal
      if (data.type === 'subscribe' && data.channel) {
        clientsChannels.set(ws, data.channel);
        console.log(`📡 Client abonné au canal #${data.channel}`);
        return;
      }

      const clientChannel = clientsChannels.get(ws);
      if (!clientChannel) {
        console.warn('🚫 Client non abonné à un canal, message ignoré');
        return;
      }

      // Broadcast aux clients du même channel SAUF l'émetteur
      wss.clients.forEach((client) => {
        const clientChan = clientsChannels.get(client);
        if (
          client !== ws &&
          client.readyState === 1 &&
          clientChan === clientChannel
        ) {
          client.send(JSON.stringify(data));
        }
      });

      console.log(`📣 Message broadcasté dans le canal #${clientChannel} :`, data);

    } catch (err) {
      console.error('💥 Erreur parsing message JSON :', err.message);
    }
  });

  ws.on('close', () => {
    clientsChannels.delete(ws);
    console.log('👋 Un client s\'est déconnecté');
  });

  ws.on('error', (error) => {
    console.error('💥 Erreur WebSocket :', error);
  });
});

// Route POST pour broadcast via HTTP API
app.post('/broadcast', express.json(), (req, res) => {
  const { id, content, timestamp, author, channel } = req.body;

  if (!id || !content || !timestamp || !author || !channel) {
    return res.status(400).json({ error: 'Paramètres invalides.' });
  }

  const message = { id, content, timestamp, author };

  wss.clients.forEach((client) => {
    const clientChan = clientsChannels.get(client);
    if (client.readyState === 1 && clientChan === channel) {
      client.send(JSON.stringify(message));
    }
  });

  console.log(`📡 Message broadcasté via API sur le canal #${channel} :`, message);
  res.status(200).json({ success: true });
});

// Petit endpoint de test
app.get('/', (req, res) => {
  res.send('Hello WebSocket World! 🌸');
});

// Lancement du serveur
const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
  console.log(`🚀 Serveur HTTP+WS lancé sur http://localhost:${PORT}`);
});

