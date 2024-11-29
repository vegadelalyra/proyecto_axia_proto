import React from 'react';
import Cookies from 'js-cookie';

const Gmao = () => {
  const logout = () => {
    Cookies.remove('session');
    window.location.reload(); // Reload to redirect back to the login page
  };
  return (
    <>
      <h1>Welcome to GMAO</h1>
      <p>This is the GMAO page.</p>
      <button onClick={logout}>DESCONECTARME</button>
    </>
  );
};

export default Gmao;
