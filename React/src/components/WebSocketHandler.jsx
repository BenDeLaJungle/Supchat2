import { useEffect } from 'react';
import { useSocket } from '../context/SocketContext';


const WebSocketHandler = ({ channelId, onMessage }) => {
  const { socket, isReady } = useSocket();

  useEffect(() => {
    if (!isReady || !socket) return;

    console.log('📡 Envoi de subscribe après connexion confirmée !');
    socket.emit('subscribe', channelId);

    const handleMessage = (rawMessage) => {
      console.log("💌 Message reçu via Socket.IO :", rawMessage);
      
      if (rawMessage?.deleted && rawMessage.id) {
        onMessage(rawMessage);
        return;
      }
      if (
        rawMessage &&
        rawMessage.id &&
        rawMessage.content &&
        rawMessage.timestamp &&
        rawMessage.author
      ) {onMessage(rawMessage);
      } else {console.warn('⚠️ Message invalide', rawMessage);}




    };

    socket.on('message', handleMessage);

    return () => {
      socket.off('message', handleMessage);
    };
  }, [isReady, socket, channelId, onMessage]);

  return null;
};

export default WebSocketHandler;
