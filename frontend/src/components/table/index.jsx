import React, { useEffect, useState } from 'react';
import { useTheme } from '../../contexts/themeContext';
import { FaArrowLeft, FaSearch } from 'react-icons/fa';
import WaitRoom from '../waitroom';

const Table = () => {
  // States for hover and focus interaction
  const [isHovered, setIsHovered] = useState(false);
  const [isFocused, setIsFocused] = useState(false);

  return (
    <main className='table'>
      <section className='table__header'>
        <nav className='table__header--nav'>
          <div style={{ display: 'flex', alignItems: 'center' }}>
            <div className='table__header--back'>
              <FaArrowLeft />
            </div>
            <div className='table__header--separator'></div>
            <span>Escoge una opción para visualizar</span>
          </div>
          <div className='table__header--input-container'>
            {/* Input field with hover and focus logic */}
            <input
              type='text'
              className='table__header--input'
              onMouseEnter={() => setIsHovered(true)}
              onMouseLeave={() => setIsHovered(false)}
              onFocus={() => setIsFocused(true)}
              onBlur={() => setIsFocused(false)}
              placeholder='Buscar'
            />
            <span className='separator'>|</span>
            {/* Search icon with conditional styles */}
            <FaSearch
              className={`search-icon ${isHovered ? 'hovered' : ''} ${
                isFocused ? 'focused' : ''
              }`}
            />
          </div>
        </nav>
        <WaitRoom />
        {/* <picture
          style={{
            position: 'absolute',
            border: '1px solid red',
            bottom: '10px',
          }}>
          <img src='/src/assets/images/logo_dark.png' alt='AXIA' />
        </picture> */}
      </section>
      {/* <section className='table__body'>THE BIG TABLE2</section> */}
    </main>
  );
};

export default Table;
