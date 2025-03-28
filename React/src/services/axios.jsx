import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api', // adapte le chemin à ton backend
  withCredentials: true,                // si tu utilises des cookies (genre sessions Symfony)
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

export default api;



 