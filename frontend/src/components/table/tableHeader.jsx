import React, { useEffect, useRef, useState } from 'react';
import { FaArrowLeft, FaChevronRight, FaSearch } from 'react-icons/fa';
import WaitRoomHero from '../waitroom/WaitRoomHero';
import RolesCards from '../RolesCards';
import { ROLES } from '../../constants/domain';
import { useLocation } from 'react-router-dom';

const TableHeader = ({ section }) => {
  // States for hover and focus interaction
  const [isHovered, setIsHovered] = useState(false);
  const [isFocused, setIsFocused] = useState(false);

  // states for input search
  const [searchTerm, setSearchTerm] = useState('');
  const [searchedRole, setSearchedRole] = useState(null);

  // states for dynamic input width
  const INPUT_INITIAL_WIDTH = 593;
  const INPUT_MIN_WIDTH = 75;
  const [inputWidth, setInputWidth] = useState(INPUT_INITIAL_WIDTH);
  const [inputExpanded, setInputExpanded] = useState(false);

  const navRef = useRef(null);
  const leftSectionRef = useRef(null);

  const inputRef = useRef(null);

  const location = useLocation();
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

  const handleIconClick = () => {
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  const handleFocus = () => {
    console.log(inputWidth, 'input width');
    console.log(INPUT_MIN_WIDTH, 'min input');
    if (inputWidth !== INPUT_MIN_WIDTH) return;
    setInputWidth(INPUT_INITIAL_WIDTH);
    setInputExpanded(true);
  };

  const handleBlur = () => {
    if (!inputExpanded) return;
    setInputWidth(INPUT_MIN_WIDTH);
    setInputExpanded(false);
  };

  useEffect(() => {
    if (navRef.current && leftSectionRef.current) {
      const navWidth = navRef.current.offsetWidth;
      const leftSectionWidth = leftSectionRef.current.offsetWidth;

      const maxWidth = Math.max(
        (navWidth - leftSectionWidth) / 2,
        INPUT_MIN_WIDTH
      );
      setInputWidth(maxWidth);
    }
  }, [location.pathname]);

  return (
    <section className='table__header'>
      <nav ref={navRef} className='table__header--nav'>
        <div
          ref={leftSectionRef}
          style={{
            display: 'flex',
            alignItems: 'center',
            height: '100%',
            width: '3300px',
          }}>
          <div className='table__header--back'>
            <FaArrowLeft onClick={() => window.history.back()} />
          </div>
          <div className='table__header--separator' />
          <>
            {isRootPath ? (
              <span style={{ marginLeft: '26px' }}>
                'Escoge una opción para visualizar'
              </span>
            ) : (
              <div className='table__header--route'>
                <div className='table__header--title'>
                  <strong>{section.title}</strong>
                </div>
                <FaChevronRight style={{ height: '26px' }} />
                <div className='table__header--subtitle'>
                  {section.subtitle}
                </div>
              </div>
            )}
          </>
        </div>
        <div
          className='table__header--input-container'
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
          onFocus={() => setIsFocused(true)}
          onBlur={() => setIsFocused(false)}>
          {/* Input field with hover and focus logic */}
          <input
            type='text'
            className='table__header--input'
            maxLength={30}
            value={searchTerm}
            ref={inputRef}
            onChange={handleSearch}
            onFocus={handleFocus}
            onBlur={handleBlur}
            style={{
              width: `${inputWidth}px`,
              cursor: inputWidth === INPUT_MIN_WIDTH ? 'pointer' : 'inherit',
            }}
            placeholder={`${inputWidth === INPUT_MIN_WIDTH ? '' : 'Buscar'}`}
          />
          <span className='separator'>|</span>
          {/* Search icon with conditional styles */}
          <FaSearch
            onClick={handleIconClick}
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
    </section>
  );
};

export default TableHeader;
