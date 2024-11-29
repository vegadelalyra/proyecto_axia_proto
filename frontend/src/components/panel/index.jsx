import React, { useState } from 'react';
import Cookies from 'js-cookie';
import { ROLES_MAPPING } from '../../constants/domain';
import { FaSearch } from 'react-icons/fa';

// import './styles.sass';

const Panel = () => {
  // Hover state for the icon
  const [isHovered, setIsHovered] = useState(false);
  const [isFocused, setIsFocused] = useState(false);

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
          className={`icon ${isHovered ? 'hovered' : ''}
        ${isFocused ? 'focused' : ''}
        `}
        />
        <input
          maxLength={20}
          type='text'
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
          onFocus={() => setIsFocused(true)}
          onBlur={() => setIsFocused(false)}
          placeholder={'Buscar funcionalidad'}
        />
      </section>
    </aside>
  );
};

export default Panel;
