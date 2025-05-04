import { useEffect, useRef } from 'react';

const WebSocketHandler = ({ channelId, onMessage }) => {
  const wsRef = useRef(null);

  useEffect(() => {
    let hasError = false;

    const connectWebSocket = () => {
      const ws = new WebSocket('ws://localhost:3001/');

      wsRef.current = ws;

      ws.onopen = () => {
        console.log('🌟 WebSocket connecté');
        ws.send(JSON.stringify({ type: 'subscribe', channel: channelId }));
      };

      ws.onmessage = (event) => {
        try {
          const rawMessage = JSON.parse(event.data);
          if (rawMessage.type === 'subscribe') return;
          console.log("📥 Reçu WebSocket :", rawMessage);
          if (rawMessage.id && rawMessage.content && rawMessage.timestamp && rawMessage.author) {
            onMessage(rawMessage);
          } else {
            console.warn('🚫 Message invalide', rawMessage);
          }
        } catch (err) {
          console.error('💥 Erreur parse WS message', err);
        }
      };

      ws.onerror = () => { hasError = true; };
      
      ws.onclose = (e) => {
        if (hasError || e.code === 1006) {
          console.log('⏳ WS déconnecté, reconnexion dans 2s...');
          setTimeout(connectWebSocket, 2000);
        }
      };
    };

    connectWebSocket();

    return () => {
      if (wsRef.current) wsRef.current.close();
    };
  }, [channelId, onMessage]);

  return null;
};

export default WebSocketHandler;
 