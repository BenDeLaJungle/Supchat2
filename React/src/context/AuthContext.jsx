import { createContext, useContext, useState, useEffect } from 'react';
import { getToken, logout } from '../services/auth';
import { apiFetch } from '../services/api';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(getToken());

  useEffect(() => {
    if (token) {
      apiFetch('api/user')
        .then(setUser)
        .catch(() => {
          logout();
          setToken(null);
        });
    }
  }, [token]);

  return (
    <AuthContext.Provider value={{ user, setUser, token, setToken }}>
      {children}
    </AuthContext.Provider>
  );
}


export function useAuth() {
  return useContext(AuthContext);
}
