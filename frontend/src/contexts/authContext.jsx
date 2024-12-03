import React, { createContext, useContext } from 'react';
import Cookies from 'js-cookie';
import { ROLES_MAPPING } from '../constants/domain';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  // Get the role from the user data, and then find the mapped role name
  const session = Cookies.get('session');
  const isAuthenticated = Boolean(session);

  let user = Cookies.get('session') ? JSON.parse(Cookies.get('session')) : null;
  const userRole = user ? ROLES_MAPPING[user.rol] : 'Rol no asignado';
  user = { ...user, userRole };

  return (
    <AuthContext.Provider value={{ isAuthenticated, user }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
