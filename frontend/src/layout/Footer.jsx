import React from 'react';
import { useAuth } from '../contexts/authContext';

const Footer = () => {
  const { isAuthenticated } = useAuth();

  return (
    <footer>
      {isAuthenticated ? (
        <>
          <span className='footer-left'>© Axia Servicios · Grupo Axia</span>
          <div className='footer-right'>
            <a href='#'>Ayuda</a>
            <span>|</span>
            <a href='#' onClick={() => console.log('Notificar un error')}>
              Notificar un error
            </a>
          </div>
        </>
      ) : (
        <div className='footer-links'>
          <a
            href='https://axiaservicios.com/aviso-legal/'
            target='_blank'
            rel='noopener noreferrer'>
            Aviso legal
          </a>
          <a
            href='https://axiaservicios.com/politica-de-cookies/'
            target='_blank'
            rel='noopener noreferrer'>
            Política de cookies
          </a>
          <a
            href='https://axiaservicios.com/politica-de-privacidad/'
            target='_blank'
            rel='noopener noreferrer'>
            Política de privacidad
          </a>
        </div>
      )}
    </footer>
  );
};

export default Footer;
