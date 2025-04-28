// services/mercure.js

import { getMercureToken } from './auth';

export async function fetchMercureToken(userAuthToken) {
  const res = await fetch('http://localhost:8000/api/mercure-token', {
    headers: {
      Authorization: `Bearer ${userAuthToken}`,
    },
  });

  if (!res.ok) {
    throw new Error('Erreur récupération du token Mercure');
  }

  const data = await res.json();
  return data.token;
}

export function connectToMercure(channelId, onMessage, onError) {
  const mercureToken = getMercureToken();

  if (!mercureToken) {
    console.error("💔 Pas de mercureToken trouvé.");
    return null;
  }

  const url = new URL('http://localhost:3000/.well-known/mercure');
  url.searchParams.append('topic', `/channels/${channelId}`);
  url.searchParams.append('token', mercureToken);

  console.log('🌸 Connexion Mercure URL :', url.toString());

  const eventSource = new EventSource(url.toString());

  eventSource.onmessage = (event) => {
    try {
      const newMessage = JSON.parse(event.data);
      onMessage(newMessage);
    } catch (err) {
      console.error("💥 Erreur parsing Mercure :", err);
    }
  };

  eventSource.onerror = (err) => {
    console.error("❌ Erreur EventSource :", err);
    if (onError) onError(err);
    eventSource.close();
  };

  return eventSource;
}
