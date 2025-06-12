import { useEffect, useContext } from 'react';
import { toast } from 'react-toastify';
import { SocketContext } from '../../context/SocketContext';

function NotificationListener() {
  const { socket } = useContext(SocketContext);

  useEffect(() => {
    if (!socket) return;

    socket.on('notification', (data) => {
      toast.info(data.message);
    });

    return () => {
      socket.off('notification');
    };
  }, [socket]);

  return null; 
}

export default NotificationListener;
