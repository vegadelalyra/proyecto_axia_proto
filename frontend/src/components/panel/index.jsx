import React, { useRef, useState } from 'react';
import { FaSearch } from 'react-icons/fa';
import ExpandableList from '../expandableList';
import { TITLES } from '../../routes/routes';
import { useAuth } from '../../contexts/authContext';

const Panel = ({ section }) => {
  // Hover and focus state for the input icon
  const [isHovered, setIsHovered] = useState(false);
  const [isFocused, setIsFocused] = useState(false);
  const [searchTerm, setSearchTerm] = useState(''); // Add state for the search term
  const inputRef = useRef();

  // Get user role from authentication context
  const { user } = useAuth();

  const handleIconClick = () => {
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  return (
    <aside className='panel'>
      <p className='title'>
        Panel de <br /> {user.userRole}
      </p>
      <section
        className='input-container'
        onMouseEnter={() => setIsHovered(true)}
        onMouseLeave={() => setIsHovered(false)}
        onFocus={() => setIsFocused(true)}
        onBlur={() => setIsFocused(false)}>
        <FaSearch
          onClick={handleIconClick}
          className={`icon ${isHovered ? 'hovered' : ''} ${
            isFocused ? 'focused' : ''
          }`}
        />
        <input
          maxLength={20}
          ref={inputRef}
          type='text'
          placeholder={'Buscar funcionalidad'}
          value={searchTerm} // Controlled input
          onChange={e => setSearchTerm(e.target.value)} // Update search term
        />
      </section>
      <ExpandableList data={TITLES} searchTerm={searchTerm} section={section} />
    </aside>
  );
};

export default Panel;
