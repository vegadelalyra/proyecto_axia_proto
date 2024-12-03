import React, { useState } from 'react';
import { FaArrowLeft, FaSearch } from 'react-icons/fa';
import WaitRoomHero from '../waitroom/WaitRoomHero';
import RolesCards from '../RolesCards';
import { ROLES } from '../../constants/domain';

const TableHeader = ({ section }) => {
  // States for hover and focus interaction
  const [isHovered, setIsHovered] = useState(false);
  const [isFocused, setIsFocused] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [searchedRole, setSearchedRole] = useState(null);

  const isRootPath = location.pathname === '/';

  // Handler for searching and selecting a role
  const handleSearch = e => {
    const query = e.target.value.toLowerCase();
    setSearchTerm(query);

    if (!isRootPath && query) {
      const roles = Object.values(ROLES);
      const matchedRole = roles.find(role =>
        role.toLowerCase().includes(query)
      );

      setSearchedRole(matchedRole || null);
    }
  };

  return (
    <section className='table__header'>
      <nav className='table__header--nav'>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <div className='table__header--back'>
            <FaArrowLeft onClick={() => window.history.back()} />
          </div>
          <div className='table__header--separator'></div>
          <span>
            {isRootPath ? (
              'Escoge una opción para visualizar'
            ) : (
              <>
                <strong>{section.title}</strong> &gt; {section.subtitle}{' '}
              </>
            )}
          </span>
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
            value={searchTerm}
            onChange={handleSearch}
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
      {isRootPath ? (
        <WaitRoomHero />
      ) : (
        <RolesCards searchedRole={searchedRole} />
      )}
      <picture
        style={{
          position: 'absolute',
          border: '1px solid red',
          bottom: '10px',
        }}>
        <img src='/src/assets/icons/axia_logo.svg' alt='AXIA' />
      </picture>
    </section>
  );
};

export default TableHeader;
