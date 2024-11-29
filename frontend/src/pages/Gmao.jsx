import React from 'react';
import Cookies from 'js-cookie';
import Panel from '../components/panel';

const Gmao = () => {
  const logout = () => {
    Cookies.remove('session');
    window.location.reload(); // Reload to redirect back to the login page
  };
  return (
    <>
      <Panel />
      <div>
        <h1>Welcome to GMAO</h1>
        <p>This is the GMAO page.</p>
        <button onClick={logout}>DESCONECTARME</button>
      </div>
    </>
  );
};

export default Gmao;
