import { useEffect } from 'react';
import { useSocket } from '../../context/SocketContext';


const WebSocketHandler = ({ channelId, onMessage }) => {
  const { socket, isReady } = useSocket();

  useEffect(() => {
    if (!isReady || !socket) return;

    socket.emit('subscribe', channelId);

    const handleMessage = (rawMessage) => {
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
