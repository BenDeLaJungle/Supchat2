import React, { useState } from 'react';
import '../styles/index.css';
import { Link } from 'react-router-dom';
import AdminHeader from './Adminheader';

export default function Messaging() {
  const [messages, setMessages] = useState([
    { id: 1, author: "Benjamin", text: "Hello 👋", time: "10:01" },
    { id: 2, author: "Felix", text: "Salut ! #welcome", time: "10:02" }
  ]);
  const [newMessage, setNewMessage] = useState('');
  const [newConversationVisible, setNewConversationVisible] = useState(false);
  const [recipient, setRecipient] = useState('');

  const handleSend = () => {
    if (!newMessage.trim() || !recipient) return;
    const message = {
      id: messages.length + 1,
      author: "Moi",
      text: newMessage,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    };
    setMessages([...messages, message]);
    setNewMessage('');
  };

  return (
    <>
      <AdminHeader />
      <div className="messaging-container">
        <h2 className="welcome-name">Conversation</h2>
        <button className="start-conv-btn" onClick={() => setNewConversationVisible(!newConversationVisible)}>
          ➕ Nouvelle conversation
        </button>

        {newConversationVisible && (
          <div className="new-conversation-bar">
            <select
              className="recipient-select"
              value={recipient}
              onChange={(e) => setRecipient(e.target.value)}
            >
              <option value="">-- Choisir un destinataire --</option>
              <option value="Nathan">Nathan</option>
              <option value="Felix">Felix</option>
              <option value="Geoffroy">Geoffroy</option>
            </select>
            <input
              type="text"
              className="message-input"
              placeholder="Votre message..."
              value={newMessage}
              onChange={(e) => setNewMessage(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && handleSend()}
            />
            <button className="send-btn" onClick={handleSend}>Envoyer</button>
          </div>
        )}

        <div className="message-list">
          {messages.map((msg) => (
            <div key={msg.id} className="message-item">
              <strong>{msg.author}</strong> <span>({msg.time})</span>
              <p>{msg.text}</p>
            </div>
          ))}
        </div>
      </div>
      <Link to="/test-messages" className="acces-test">
          🔗 Accéder à la messagerie test
      </Link>
    </>
  );
}

