import React from 'react';
import MessageList from '../components/MessageList';
import MessageForm from '../components/MessageForm';
import { useAuth } from '../context/AuthContext';
import '../styles/message.css';


const PrivateMessage = () => {
  const { user } = useAuth();
  const channelId = 1;

  return (
    <div className="privateMessage p-4 bg-gray-50 rounded shadow">
      <h3 className="text-lg font-semibold mb-4">
        💬 Test de messagerie sur le canal #{channelId}
      </h3>

      <MessageList channelId={channelId} />

      <MessageForm
        channelId={channelId}
        userId={user?.id}
        onMessageSent={() => console.log("Message envoyé !")}
      />
    </div>
  );
};

export default PrivateMessage;