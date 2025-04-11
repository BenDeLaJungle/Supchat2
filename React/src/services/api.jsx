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

  if (response.status === 401) logout();

  return response.json();
}
