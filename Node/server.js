import express from 'express';
import http from 'http';
import cors from 'cors';
import { Server } from 'socket.io';

const app = express();
const server = http.createServer(app);

// Middleware CORS pour l'API REST
app.use(cors({
  origin: 'http://localhost:5173',
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type']
}));

// Initialisation de socket.io
const io = new Server(server, {
  cors: {
    origin: 'http://localhost:5173',
    methods: ['GET', 'POST']
  }
});

// Map des sockets et leur channel
const clientsChannels = new Map();

io.on('connection', (socket) => {
  console.log('Nouvelle connexion Socket.IO !', socket.id);

  socket.on('subscribe', (channel) => {
    clientsChannels.set(socket.id, channel);
    socket.join(channel);
    console.log(`${socket.id} s’abonne au canal #${channel}`);
    console.log('Map actuelle :', Array.from(clientsChannels.entries()));
  });

  socket.on('message', (data) => {
    const channel = clientsChannels.get(socket.id);
    console.log(`Message reçu de ${socket.id} :`, data);
    console.log(`Canal trouvé : ${channel}`);
    if (!channel) {
      console.warn(`Socket ${socket.id} non abonné, message ignoré`);
      return;
    }
    io.to(channel).emit('message', data);
  });

  socket.on('disconnect', () => {
    console.log(`Déconnexion socket ${socket.id}`);
    clientsChannels.delete(socket.id);
  });
});

// API REST pour broadcast
app.post('/broadcast', express.json(), (req, res) => {
  const { id, content, timestamp, author, channel, deleted } = req.body;

  if (!id || !channel || (!content && !deleted)) {
    return res.status(400).json({ error: 'Paramètres invalides.' });
  }

  let message;

  if (deleted) {
    message = { id, deleted: true, channel };
  } else {
    message = { id, content, timestamp, author };
  }

  const socketsInRoom = io.sockets.adapter.rooms.get(channel);
  console.log(`Clients dans le canal #${channel} :`, [...(socketsInRoom || [])]);

  io.to(channel).emit('message', message);
  console.log(`Message broadcasté via API sur le canal #${channel} :`, message);

  res.status(200).json({ success: true });
});

// Endpoint de test
app.get('/', (req, res) => {
  res.send('Hello Socket.IO World!');
});

// Lancement du serveur
const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
  console.log(`Serveur HTTP + Socket.IO lancé sur http://localhost:${PORT}`);
});

