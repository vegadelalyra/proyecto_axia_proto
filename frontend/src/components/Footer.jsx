import React from 'react';

const Footer = () => {
  return (
    <footer
      style={{
        position: 'fixed',
        bottom: 0,
        left: 0,
        width: '100%',
        height: '95px',
        backgroundColor: 'var(--secondary-background)',
        display: 'flex',
        justifyContent: 'flex-end',
        alignItems: 'center',
        paddingRight: '50px',
        opacity: 1,
      }}>
      <span style={{ marginRight: '20px' }}>Aviso legal</span>
      <span style={{ marginRight: '20px' }}>Política de privacidad</span>
      <span>Política de cookies</span>
    </footer>
  );
};

export default Footer;
