import React, { useRef, useState, useEffect } from 'react';
import { useAuth } from '../contexts/authContext';
import { FaDoorOpen } from 'react-icons/fa';
import { logout } from '../utils/auth';
import { useTheme } from '../contexts/themeContext'; // import useTheme

const Header = () => {
  const { isAuthenticated, user } = useAuth();
  const { isDarkMode, activateDarkMode } = useTheme(); // get dark mode state and toggle function
  const [copiedText, setCopiedText] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [animationKey, setAnimationKey] = useState(0);
  const timeoutRef = useRef(null);

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
        {!isAuthenticated ? (
          <div className='container'>
            <span style={{ marginBottom: '21px' }}>
              <span
                style={{ cursor: 'pointer' }}
                onClick={() => handleCopyToClipboard('Axia@axiaservicios.com')}>
                Axia@axiaservicios.com
              </span>{' '}
              | C/ Paduleta 18, Polígono Industrial Júndiz, 01015
              Vitoria-Gasteiz
            </span>
            <span className='container_contact' style={{ marginRight: '50px' }}>
              <span
                style={{ cursor: 'pointer' }}
                onClick={() => handleCopyToClipboard('+34 945 354 738')}>
                +34 945 354 738
              </span>
            </span>
          </div>
        ) : (
          ''
        )}

        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <div style={{ display: 'flex', alignItems: 'center' }}>
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
                cursor: 'pointer',
                marginRight: '17px',
                userSelect: 'none',
              }}
            />
            <p
              style={{
                borderLeft: '1px solid #707070',
                paddingLeft: '14px',
                fontStyle: 'italic',
              }}>
              GMAO <br /> Versión <span style={{ color: '#2071B7' }}>1.0</span>
            </p>
          </div>
          <div style={{ display: 'flex', alignItems: 'center' }}>
            <div className='split-button' style={{ marginRight: '50px' }}>
              <img
                src='/src/assets/icons/dark_mode.svg'
                alt='Activate Dark Mode'
                onClick={() => {
                  if (isDarkMode) return;
                  activateDarkMode(true);
                }} // toggle dark mode
                className={`${isDarkMode ? 'svg--dark' : ''}`}
                role='button'
                aria-label='Activate Dark Mode'
              />
              <img
                src='/src/assets/icons/light_mode.svg'
                alt='Activate Light Mode'
                onClick={() => {
                  if (!isDarkMode) return;
                  activateDarkMode(false);
                }} // toggle dark mode
                className={`${isDarkMode ? '' : 'svg--light'}`}
                role='button'
                aria-label='Activate Light Mode'
              />
            </div>

            {isAuthenticated ? (
              <>
                <div
                  className='profile-card'
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '18px',
                    marginRight: '24px',
                  }}>
                  <div className='profile-picture'>
                    <img
                      src={
                        user?.profilePicture ||
                        '/src/assets/images/user_pic.jpg'
                      }
                      alt='Profile'
                      style={{
                        width: '68px',
                        height: '68px',
                        borderRadius: '50%',
                      }}
                    />
                  </div>
                  <div className='profile-info'>
                    <div className='user-name'>{user?.Name || 'User Name'}</div>
                    <div className='user-role'>
                      {user?.userRole || 'User Role'}
                    </div>
                    <div className='company-name'>{'Axia servicios'}</div>
                  </div>
                </div>
                <FaDoorOpen className='header_logout' onClick={logout} />
              </>
            ) : (
              ''
            )}
          </div>
        </div>
      </div>

      {showModal && (
        <div
          key={animationKey}
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
