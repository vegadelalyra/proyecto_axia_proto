import React from 'react';
import { FaSun, FaMoon } from 'react-icons/fa';

const Header = () => {
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
            src='/src/assets/images/logo_light.png'
            alt='Axia Logo'
            style={{ width: '256px', height: '59px' }}
          />
          <div className='split-button' style={{ marginRight: '50px' }}>
            <FaMoon />
            <FaSun />
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
