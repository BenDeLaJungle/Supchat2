// src/context/AuthContext.jsx
import { createContext, useContext, useState, useEffect } from 'react';
import { getToken, logout } from '../services/auth';
import { apiFetch } from '../services/api';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);

  useEffect(() => {
    const token = getToken();
    if (token) {
      apiFetch('api/user')
        .then(fetchedUser => setUser(fetchedUser))
        .catch(() => logout());
    }
  }, []);

  useEffect(() => {
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme === 'dark' || storedTheme === 'light') {
      document.body.className = `theme-${storedTheme}`;
      return;
    }

    if (user?.theme !== undefined) {
      const apiTheme = user.theme ? 'dark' : 'light';
      document.body.className = `theme-${apiTheme}`;
    }
  }, [user]);

  return (
    <AuthContext.Provider value={{ user, setUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
