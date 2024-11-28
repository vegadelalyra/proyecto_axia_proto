import React from 'react';
import { useRef } from 'react';
import { useEffect } from 'react';
import { useState } from 'react';

const Header = () => {
  const [isDarkMode, setIsDarkMode] = useState(() => {
    const savedTheme = localStorage.getItem('isDarkMode');
    return savedTheme === 'true';
  });

  const [marginTop, setMarginTop] = useState(0);

  const [copiedText, setCopiedText] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [animationKey, setAnimationKey] = useState(0);
  const timeoutRef = useRef(null);

  // toggle dark mode
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

  // sticky header
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

  // clipboardable texts with animation
  const CLIPBOARD_ANIMATION_DURATION = 6_000;
  const handleCopyToClipboard = text => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);

    navigator.clipboard
      .writeText(text)
      .then(() => {
        setCopiedText(text);
        setShowModal(true);
        setAnimationKey(prevKey => prevKey + 1);
        timeoutRef.current = setTimeout(
          () => setShowModal(false),
          CLIPBOARD_ANIMATION_DURATION - 100
        );
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
            <span
              style={{ cursor: 'pointer' }}
              onClick={() => handleCopyToClipboard('+34 945 354 738')}>
              +34 945 354 738
            </span>
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
            <img
              src='src/assets/icons/dark_mode.svg'
              alt='Activate Dark Mode'
              onClick={() => setIsDarkMode(true)}
              className={`${isDarkMode ? 'svg--dark' : ''}`}
              role='button'
              aria-label='Activate Dark Mode'
            />
            <img
              src='src/assets/icons/light_mode.svg'
              alt='Activate Light Mode'
              onClick={() => setIsDarkMode(false)}
              className={`${isDarkMode ? '' : 'svg--light'}`}
              role='button'
              aria-label='Activate Light Mode'
            />
          </div>
        </div>
      </div>

      {showModal && (
        <div
          key={animationKey} // Use animation key to force a re-render
          style={{
            position: 'fixed',
            bottom: '20px',
            left: '50%',
            transform: 'translateX(-50%)',
            padding: '10px 20px',
            backgroundColor: 'rgba(0, 0, 0, 0.7)',
            color: 'white',
            borderRadius: '5px',
            zIndex: '1000',
            animation: `fadeInOut ${CLIPBOARD_ANIMATION_DURATION}ms ease-in-out`,
          }}>
          <p style={{ margin: 0 }}>Copiado al portapapeles: {copiedText}</p>
        </div>
      )}
    </header>
  );
};

export default Header;
