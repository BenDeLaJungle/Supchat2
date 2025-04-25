import React, { useEffect, useState, useRef } from 'react';
import Message from './Message';
import { apiFetch } from '../services/api';

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

        //Tri par timestamp croissant
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

  useEffect(() => {
    const connectToMercure = async () => {
      try {
        const tokenRes = await fetch('http://localhost:8000/api/mercure-token', {
          headers: {
            Authorization: `Bearer ${userAuthToken}`,
          },
        });
  
        const tokenData = await tokenRes.json();
        const mercureToken = tokenData.token;
  
        const url = new URL('http://localhost:3000/.well-known/mercure');
        url.searchParams.append('topic', `channel/${channelId}`);
  
        //On ajoute le token dans les options de EventSource
        const eventSource = new EventSource(url.toString(), {
          withCredentials: true,
        });
  
        eventSourceRef.current = eventSource;
  
        eventSource.onmessage = (event) => {
          try {
            const newMessage = JSON.parse(event.data);
            console.log("Message Mercure reçu :", newMessage);
  
            setMessages((prev) => {
              const alreadyExists = prev.some(m => m.id === newMessage.id);
              if (alreadyExists) return prev;
  
              const all = [...prev, newMessage];
              return all.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
            });
          } catch (err) {
            console.error("Erreur parsing Mercure :", err);
          }
        };
  
        eventSource.onerror = (err) => {
          console.error("Erreur EventSource :", err);
          eventSource.close();
        };
  
      } catch (error) {
        console.error("Erreur Mercure setup :", error);
      }
    };
  
    connectToMercure();
  
    return () => {
      if (eventSourceRef.current) {
        eventSourceRef.current.close();
      }
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

