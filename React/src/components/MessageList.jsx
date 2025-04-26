import React, { useEffect, useState, useRef } from 'react';
import Message from './Message';
import { apiFetch } from '../services/api';

const MessageList = ({ channelId }) => {
  const [messages, setMessages] = useState([]);
  const [offset, setOffset] = useState(0);
  const [error, setError] = useState(null);       
  const [isFetching, setIsFetching] = useState(false); 
  const listRef = useRef();

  const fetchMessages = async () => {
    try {
      setIsFetching(true);
      const data = await apiFetch(`channels/${channelId}/messages?limit=20&offset=${offset}`);

      setMessages((prev) => {
        const ids = new Set(prev.map(m => m.id));
        const uniques = data.filter(m => !ids.has(m.id));
        return [...prev, ...uniques];
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
    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 5 && !isFetching) {
      setOffset((prev) => prev + 20);
    }
  };

  useEffect(() => {
    fetchMessages();
  }, [offset]);

  useEffect(() => {
    const el = listRef.current;
    el.addEventListener('scroll', handleScroll);
    return () => el.removeEventListener('scroll', handleScroll);
  }, []);

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
