import React, { useState } from 'react';
import Cookies from 'js-cookie';
import { ROLES_MAPPING } from '../../constants/domain';
import { FaSearch } from 'react-icons/fa';
import ExpandableList from '../expandableList';
import { TITLES } from '../../routes/routes';

const Panel = ({ section }) => {
  // Hover and focus state for the input icon
  const [isHovered, setIsHovered] = useState(false);
  const [isFocused, setIsFocused] = useState(false);
  const [searchTerm, setSearchTerm] = useState(''); // Add state for the search term

  // Retrieve user data from cookies
  const userData = Cookies.get('session')
    ? JSON.parse(Cookies.get('session'))
    : null;

  // Get the role from the user data, and then find the mapped role name
  const userRole = userData ? ROLES_MAPPING[userData.rol] : 'Rol no asignado';

  return (
    <aside className='panel'>
      <p className='title'>
        Panel de <br /> {userRole}
      </p>
      <section className='input-container'>
        <FaSearch
          className={`icon ${isHovered ? 'hovered' : ''} ${
            isFocused ? 'focused' : ''
          }`}
        />
        <input
          maxLength={20}
          type='text'
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
          onFocus={() => setIsFocused(true)}
          onBlur={() => setIsFocused(false)}
          placeholder={'Buscar funcionalidad'}
          value={searchTerm} // Controlled input
          onChange={e => setSearchTerm(e.target.value)} // Update search term
        />
      </section>
      <ExpandableList data={TITLES} searchTerm={searchTerm} />{' '}
    </aside>
  );
};

export default Panel;
