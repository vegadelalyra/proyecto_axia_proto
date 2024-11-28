import React from 'react';
import { useEffect } from 'react';
import { useState } from 'react';
import { FaSun, FaMoon } from 'react-icons/fa';

const Header = () => {
  const [isDarkMode, setIsDarkMode] = useState(() => {
    const savedTheme = localStorage.getItem('isDarkMode');
    return savedTheme === 'true';
  });

  const [marginTop, setMarginTop] = useState(0);

  useEffect(() => {
    if (isDarkMode) {
      document.body.classList.add('dark-mode');
      document.body.classList.remove('light-mode');
    } else {
      document.body.classList.add('light-mode');
      document.body.classList.remove('dark-mode');
    }

    localStorage.setItem('isDarkMode', isDarkMode);
  }, [isDarkMode]);

  useEffect(() => {
    const handleScroll = () => {
      const scrollY = window.scrollY;
      if (scrollY <= 100) {
        setMarginTop((scrollY / 100) * 30); // Gradually reduce marginTop from 30px to 0px
      } else {
        setMarginTop(30); // Fix at 30px once scrollY > 100
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => {
      window.removeEventListener('scroll', handleScroll); // Cleanup on unmount
    };
  }, []);

  const handleCopyToClipboard = text => {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        alert(`Copied to clipboard: ${text}`);
      })
      .catch(err => {
        console.error('Failed to copy: ', err);
      });
  };

  return (
    <header>
      <div style={{ marginLeft: '50px', width: '100%' }}>
        <div className='container'>
          <span>
            <span
              style={{ cursor: 'pointer' }}
              onClick={() => handleCopyToClipboard('Axia@axiaservicios.com')}>
              Axia@axiaservicios.com
            </span>{' '}
            | C/ Paduleta 18, Polígono Industrial Júndiz, 01015 Vitoria-Gasteiz
          </span>
          <span className='container_contact' style={{ marginRight: '50px' }}>
            <span style={{ cursor: 'pointer' }}>+34 945 354 738</span>
          </span>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <img
            src={
              isDarkMode
                ? '/src/assets/images/logo_dark.png'
                : '/src/assets/images/logo_light.png'
            }
            alt='Axia Logo'
            style={{
              width: '256px',
              height: '59px',
              position: 'relative',
              top: '21px',
              cursor: 'pointer',
              userSelect: 'none',
            }}
          />
          <div
            className='split-button'
            style={{ marginRight: '50px', marginTop: `${marginTop}px` }}>
            <FaMoon
              onClick={() => setIsDarkMode(true)}
              className={`${isDarkMode ? 'svg--dark' : ''}`}
            />
            <FaSun
              onClick={() => setIsDarkMode(false)}
              className={`${isDarkMode ? '' : 'svg--light'}`}
            />
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
