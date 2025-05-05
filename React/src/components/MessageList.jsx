import React, { useEffect, useState, useRef } from 'react';
import '../styles/color.css';
import '../styles/chat.css';
import { apiFetch } from '../services/api';
import Message from './Message';

const MessageList = ({ channelId, messages, onMessagesFetched }) => {
  const [offset, setOffset] = useState(0);
  const [error, setError] = useState(null);
  const [isFetching, setIsFetching] = useState(false);
  const listRef = useRef();
  const uniqueMessages = [
    ...new Map(messages.map((msg) => [msg.id, msg])).values()
  ];

  const fetchMessages = async () => {
    try {
      setIsFetching(true);
      const data = await apiFetch(`channels/${channelId}/messages?limit=20&offset=${offset}`);
      const uniques = data.filter((msg) => !messages.some((m) => m.id === msg.id));
      if (uniques.length > 0) {
        onMessagesFetched([...uniques, ...messages]);
      }
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
    <div ref={listRef} className="message-list">
      {error && (
        <div className="message-error">
          {error}
        </div>
      )}
      {uniqueMessages.map((msg) => (
        <Message key={msg.id} {...msg} />
      ))}
    </div>
  );
};

export default MessageList;

 