import { getToken, logout } from './auth';

const API_URL = 'http://localhost:8000/';

export async function apiFetch(endpoint, options = {}) {
  const token = getToken();
  const defaultHeaders = {
    'Content-Type': 'application/json',
    ...(token && { 'Authorization': `Bearer ${token}` }),
  };

  const response = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers: { ...defaultHeaders, ...(options.headers || {}) },
  });

  // Gère les erreurs 401 (non autorisé)
  if (response.status === 401) {
    logout();
    throw new Error("Non autorisé");
  }

  const contentType = response.headers.get('content-type');

  if (contentType && contentType.includes('application/json')) {
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || "Erreur inconnue");
    }

    return data;
  } else {
    const raw = await response.text();
    console.error("⚠️ Réponse non JSON reçue :", raw);
    throw new Error("Réponse non valide (pas du JSON)");
  }
}
