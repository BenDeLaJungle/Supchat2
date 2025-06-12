import React, { useEffect, useState, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import '../../styles/Color.css';
import '../../styles/Chat.css';
import { apiFetch } from '../../services/api';
import Message from './Message';

const MessageList = ({ channelId, messages, onMessagesFetched, canEdit, userId, onBack }) => {
  const [error, setError] = useState(null);
  const [isFetching, setIsFetching] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [channelName, setChannelName] = useState('');
  const listRef = useRef();
  const navigate = useNavigate();

  const uniqueMessages = [
    ...new Map(messages.map((msg) => [msg.id, msg])).values()
  ].filter(msg => !msg.deleted);

  const fetchChannelName = async () => {
    try {
      const data = await apiFetch(`channels/${channelId}`);
      setChannelName(data.name || '');
    } catch (err) {
      console.error("Erreur nom canal :", err.message);
      setChannelName('');
    }
  };

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
    fetchChannelName();
  }, [channelId]);

  useEffect(() => {
    const el = listRef.current;
    if (!el) return;
    el.addEventListener('scroll', handleScroll);
    return () => el.removeEventListener('scroll', handleScroll);
  }, [uniqueMessages, isFetching, hasMore]);

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
    <div className="message-list-wrapper">
      <div className="message-header">
        <h3 className="channel-title">
          {channelName.startsWith("priv_") ? "Conversation privée" : channelName}
        </h3>
      </div>

      <div ref={listRef} className="message-list">
        {error && <div className="message-error">{error}</div>}
        {isFetching && <div className="loading">Chargement...</div>}

        {uniqueMessages.map((msg) => (
          <Message
            key={msg.id}
            {...msg}
            canEditGlobal={canEdit}
            currentUserId={userId}
            channelId={channelId}
          />
        ))}
      </div>
    </div>
  );
};

export default MessageList;