import React from 'react';
import { useEffect } from 'react';
import { useState } from 'react';
import { FaSun, FaMoon } from 'react-icons/fa';

const Header = () => {
  const [isDarkMode, setIsDarkMode] = useState(() => {
    const savedTheme = localStorage.getItem('isDarkMode');
    return savedTheme === 'true';
  });

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

  return (
    <header>
      <div style={{ marginLeft: '50px', width: '100%' }}>
        <div className='container'>
          <span>
            Axia@axiaservicios.com | C/ Paduleta 18, Polígono Industrial Júndiz,
            01015 Vitoria-Gasteiz
          </span>
          <span className='container_contact' style={{ marginRight: '50px' }}>
            +34945354738
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
            }}
          />
          <div className='split-button' style={{ marginRight: '50px' }}>
            <FaMoon
              onClick={() => setIsDarkMode(true)}
              style={{
                color: isDarkMode ? 'var(--color-principal)' : 'inherit',
              }}
            />
            <FaSun
              onClick={() => setIsDarkMode(false)}
              style={{
                color: !isDarkMode ? 'var(--color-principal)' : 'inherit',
              }}
            />
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
