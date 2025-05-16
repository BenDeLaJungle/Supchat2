import { useEffect, useContext } from 'react';
import { toast } from 'react-toastify';
import { SocketContext } from '../context/SocketContext';

function NotificationListener() {
  const { socket } = useContext(SocketContext);

  useEffect(() => {
    if (!socket) return;

    socket.on('notification', (data) => {
      console.log('📥 Notification reçue :', data); // ← Ajout ici
      toast.info(data.message); // Affiche le toast
    });

    return () => {
      socket.off('notification');
    };
  }, [socket]);

  return null; // Pas d'affichage visuel nécessaire
}

export default NotificationListener;
