import { createContext, useContext, useState } from 'react';

/**
 * Sumber kebenaran untuk "siapa yang login".
 * Untuk sekarang hanya role admin. Nanti bisa ditambah role lain.
 */
const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);

  const login = (userData) => setUser(userData);
  const logout = () => setUser(null);

  return (
    <AuthContext.Provider value={{ user, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);