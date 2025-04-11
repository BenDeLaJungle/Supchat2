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
        .then(setUser)
        .catch(() => logout());
    }
  }, []);

  return (
    <AuthContext.Provider value={{ user, setUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
