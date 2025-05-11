import React, { useEffect, useState, useRef } from 'react';
import '../styles/color.css';
import '../styles/chat.css';
import { apiFetch } from '../services/api';
import Message from './Message';

const MessageList = ({ channelId, messages, onMessagesFetched, canEdit, userId }) => {
  const [error, setError] = useState(null);
  const [isFetching, setIsFetching] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const listRef = useRef();

  const uniqueMessages = [
    ...new Map(messages.map((msg) => [msg.id, msg])).values()
  ];

  // Récupère les messages par pagination
  const fetchMessages = async (before = null) => {
    try {
      setIsFetching(true);
  
      let url = `channels/${channelId}/messages?limit=20`;
      if (before && before.timestamp && before.id) {
        url += `&before=${encodeURIComponent(before.timestamp)}&before_id=${before.id}`;
      }
  
      const data = await apiFetch(url);
  
      if (data.length === 0) {
        setHasMore(false);
      } else {
        onMessagesFetched([...data, ...messages]);
      }
  
      setError(null);
    } catch (err) {
      console.error("Erreur récupération messages :", err.message);
      setError("Impossible de charger les messages.");
    } finally {
      setIsFetching(false);
    }
  };

  // Supprime localement un message
  const handleDeleteMessage = (id) => {
    const updated = messages.filter((m) => m.id !== id);
    onMessagesFetched(updated);
  };

  // Reçoit un message via WebSocket et met à jour la liste
  const handleLiveMessage = (newMsg) => {
    const exists = messages.some((m) => m.id === newMsg.id);

    if (newMsg.deleted) {
      const updated = messages.map((m) =>
        m.id === newMsg.id
          ? { ...m, content: '[Ce message a été supprimé]', deleted: true }
          : m
      );
      onMessagesFetched(updated);
      return;
    }

    if (!exists) {
      onMessagesFetched([...messages, newMsg]);
    } else {
      const updated = messages.map((m) =>
        m.id === newMsg.id ? newMsg : m
      );
      onMessagesFetched(updated);
    }
  };

  // Scroll vers le haut pour charger les messages précédents
  const handleScroll = () => {
    const el = listRef.current;
    if (el.scrollTop <= 5 && !isFetching && hasMore) {
      const oldestMessage = uniqueMessages[0];
      if (oldestMessage) {
        fetchMessages({ timestamp: oldestMessage.timestamp, id: oldestMessage.id });
      }
    }
  };

  useEffect(() => {
    fetchMessages();
  }, [channelId]);

  useEffect(() => {
    const el = listRef.current;
    if (!el) return;
    el.addEventListener('scroll', handleScroll);
    return () => el.removeEventListener('scroll', handleScroll);
  }, [uniqueMessages, isFetching, hasMore]);

  // Garde le scroll à la même position si messages ajoutés en haut
  useEffect(() => {
    const el = listRef.current;
    if (!el) return;

    if (isFetching && el.scrollTop === 0) {
      const prevHeight = el.scrollHeight;
      const observer = new MutationObserver(() => {
        const newHeight = el.scrollHeight;
        el.scrollTop = newHeight - prevHeight;
        observer.disconnect();
      });
      observer.observe(el, { childList: true, subtree: true });
    }
  }, [isFetching]);

  return (
    <div ref={listRef} className="message-list">
        {error && <div className="message-error">{error}</div>}
        {!hasMore && <div className="no-more">Tu es tout en haut</div>}
        {isFetching && <div className="loading">Chargement...</div>}

        {uniqueMessages.map((msg) => (
          <Message 
            key={msg.id} 
            {...msg} 
            canEditGlobal={canEdit} 
            currentUserId={userId} 
            onDelete={handleDeleteMessage}
            channelId={channelId}
          />
        ))}
    </div>
  );
};

export default MessageList;
