import React, { useEffect, useState } from 'react';
import { useTheme } from '../../contexts/themeContext';
import { FaArrowLeft, FaSearch } from 'react-icons/fa';

const Table = () => {
  const { isDarkMode } = useTheme();

  const [fillColor, setFillColor] = useState('#1F1F1F');

  useEffect(() => {
    setFillColor(isDarkMode ? '#1F1F1F' : '#F8F5F3');
  }, [isDarkMode]);

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
            <input type='text' className='table__header--input' />
            <span className='separator'>|</span>
            <FaSearch className='search-icon' />
          </div>
        </nav>
        <picture style={{ placeContent: 'center', height: '100%' }}>
          <svg
            className='table__header--hero'
            fill={fillColor}
            viewBox='0 0 256 256'
            xmlns='http://www.w3.org/2000/svg'
            xmlnsXlink='http://www.w3.org/1999/xlink'>
            <path
              id='XMLID_11_'
              d='M193.6,134.2l-66.6-11l9.6-6.8l16,2.2l16-2.2L193.6,134.2z M152.6,8.4c-13.4,0-24.2,10.8-24.2,24.2
            c0,13.4,10.8,24.2,24.2,24.2c15.7,0,24.2-10.8,24.2-24.2C176.7,19.2,165.9,8.4,152.6,8.4 M158.6,183.1v58.4
            c0,7.3,5.9,13.2,13.2,13.2s13.2-5.9,13.2-13.2v-58.4c0-7.3-5.9-13.2-13.2-13.2S158.6,175.8,158.6,183.1 M239.4,147.7
            c0-4.2-3.4-7.6-7.7-7.6h-15.3c-4.2,0-7.6,3.4-7.6,7.6v38.2h-17.5v38.3h40.5c4.2,0,7.7-3.4,7.7-7.7V147.7z M152.3,185.9H96.7v-38.2
            c0-4.2-3.4-7.6-7.7-7.6H73.7c-4.2,0-7.7,3.4-7.7,7.6v68.8c0,4.2,3.4,7.7,7.7,7.7h78.6V185.9z M110.9,141.6
            c-1.2,7.2,3.7,14,10.9,15.2l57.6,9.5c7.2,1.2,14-3.7,15.2-10.9c1.2-7.2-3.7-14-10.9-15.2l-57.6-9.5
            C118.9,129.5,112.1,134.4,110.9,141.6 M45.9,69.2C64.7,69.2,80,53.9,80,35.1C80,16.3,64.7,1,45.9,1C27.1,1,11.8,16.3,11.8,35.1
            C11.8,53.9,27.1,69.2,45.9,69.2 M45.9,60.8c-14.2,0-25.7-11.5-25.7-25.7C20.2,21,31.7,9.4,45.9,9.4C60,9.4,71.6,21,71.6,35.1
            C71.6,49.3,60,60.8,45.9,60.8 M62.9,31.7c1.3-0.6,1.8-2.1,1.2-3.4C63.5,27,62,26.4,60.7,27l-13.2,6.2V17.8c0-1.4-1.1-2.6-2.6-2.6
            c-1.4,0-2.6,1.1-2.6,2.6l0,19.8c0.1,0.7,0.5,1.4,1.1,1.8c0.8,0.5,1.7,0.5,2.5,0.2L62.9,31.7z M152.6,76l31.3-5.1v22
            c5,3.8,15.5,11.6,15.5,11.6c1.5,1.5,0.9,3.2,0.5,3.7c-0.9,1.2-2.5,1.4-3.7,0.5L176.7,94c-4.1-2.9-9.6-1.9-12.5,2.2
            c-2.9,4-1.9,9.6,2.2,12.5l33.4,23.4c1.5,1.1,3.8,1.8,6,1.8c1.9,0,4.1-0.8,5.7-1.9c3.5-2.5,4.8-6.9,3.6-11l-12.4-36.6
            c-3.3-9.1-11.7-19.8-24.2-19.8h-51.7c-12.5,0-20.9,10.7-24.2,19.8L90.1,121c-1.2,4.1,0.1,8.5,3.6,11c1.6,1.2,3.8,1.9,5.7,1.9
            c2.2,0,4.4-0.7,6-1.8l33.4-23.4c4-2.9,5-8.5,2.2-12.5c-2.9-4-8.5-5-12.5-2.2l-19.5,14.8c-1.2,0.9-2.8,0.6-3.7-0.5
            c-0.3-0.5-1-2.2,0.5-3.7c0,0,10.5-7.9,15.5-11.6v-22L152.6,76z'
            />
          </svg>
        </picture>
      </section>
      {/* <section className='table__body'>THE BIG TABLE2</section> */}
    </main>
  );
};

export default Table;
