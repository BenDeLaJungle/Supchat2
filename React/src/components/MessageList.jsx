import React, { useEffect, useState, useRef } from 'react';
import Message from './Message';
import { apiFetch } from '../services/api';
import { getMercureToken } from '../services/auth';

const MessageList = ({ channelId }) => {
  const [messages, setMessages] = useState([]);
  const [offset, setOffset] = useState(0);
  const [error, setError] = useState(null);
  const [isFetching, setIsFetching] = useState(false);
  const listRef = useRef();
  const eventSourceRef = useRef(null);

  const fetchMessages = async () => {
    try {
      setIsFetching(true);
      const data = await apiFetch(`channels/${channelId}/messages?limit=20&offset=${offset}`);

      setMessages((prev) => {
        const ids = new Set(prev.map(m => m.id));
        const uniques = data.filter(m => !ids.has(m.id));
        const all = [...prev, ...uniques];
        return all.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
      });

      setError(null);
    } catch (err) {
      console.error("Erreur récupération messages :", err.message);
      setError("Impossible de charger les messages.");
    } finally {
      setIsFetching(false);
    }
  };

  const handleScroll = () => {
    const el = listRef.current;
    if (el && el.scrollTop + el.clientHeight >= el.scrollHeight - 5 && !isFetching) {
      setOffset((prev) => prev + 20);
    }
  };

  useEffect(() => {
    fetchMessages();
  }, [offset, channelId]);

  useEffect(() => {
    const el = listRef.current;
    if (!el) return;

    el.addEventListener('scroll', handleScroll);
    return () => el.removeEventListener('scroll', handleScroll);
  }, [isFetching]);

  useEffect(() => {
    if (!channelId) return;
  
    const token = getMercureToken();
    if (!token) {
      console.error('💔 Impossible de se connecter à Mercure : pas de token Mercure');
      return;
    }
  
    const url = new URL('http://localhost:3000/.well-known/mercure');
    url.searchParams.append('topic', `/channels/${channelId}`);
    url.searchParams.append('token', token);
  
    console.log('🌸 Connexion Mercure avec token :', url.toString());
  
    const eventSource = new EventSource(url.toString());
  
    eventSource.onopen = () => {
      console.log('🌟 Connecté à Mercure avec succès !');
    };
  
    eventSource.onmessage = (event) => {
      console.log('💌 Nouveau message Mercure :', event.data);
  
      try {
        const newMessage = JSON.parse(event.data);
        setMessages(prevMessages => [...prevMessages, newMessage]);
      } catch (error) {
        console.error('💥 Erreur en parsant le message reçu :', error);
      }
    };
  
    eventSource.onerror = (error) => {
      console.error('❌ Erreur de connexion Mercure :', error);
      eventSource.close();
    };
  
    eventSourceRef.current = eventSource;
  
    return () => {
      console.log('👋 Déconnexion de Mercure');
      eventSource.close();
    };
  }, [channelId]);
  

  return (
    <div ref={listRef} className="h-[400px] overflow-y-scroll border rounded p-2 bg-white">
      {error && (
        <div className="text-red-600 bg-red-100 p-2 rounded mb-2 shadow-sm">
          {error}
        </div>
      )}
      {messages.map((msg) => (
        <Message key={msg.id} {...msg} />
      ))}
    </div>
  );
};

export default MessageList;

