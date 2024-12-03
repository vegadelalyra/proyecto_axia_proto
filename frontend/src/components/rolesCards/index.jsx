import React, { useEffect, useState } from 'react';
import { ROLES } from '../../constants/domain';
import { useAuth } from '../../contexts/authContext';

const RolesCards = ({ searchedRole }) => {
  // Initial selected role: get the user role from auth context if uncached selected role
  const { user } = useAuth();
  const [selectedRole, setSelectedRole] = useState(() => {
    const storedRole = localStorage.getItem('axiaSelectedRole');
    return storedRole ? storedRole : user.userRole;
  });

  const roles = [
    { name: ROLES.ADMINISTRADOR, permission: 'Gestiona el sistema' },
    { name: ROLES.REPORTER, permission: 'Genera informes' },
    { name: ROLES.CURATOR, permission: 'Supervisa contenido' },
    { name: ROLES.REPARADOR, permission: 'Repara equipos' },
    { name: ROLES.DISEÑADOR, permission: 'Crea diseños' },
    { name: ROLES.CLIENTE_FINAL, permission: 'Accede a los recursos' },
    { name: ROLES.CALIDAD, permission: 'Controla calidad' },
    { name: ROLES.CONTROL, permission: 'Gestiona control' },
    { name: ROLES.PLANIFICADOR, permission: 'Organiza tareas' },
    { name: ROLES.MANAGER, permission: 'Administra equipos' },
    { name: ROLES.FABRICANTE, permission: 'Produce bienes' },
    { name: ROLES.SUPPORT, permission: 'Brinda soporte' },
  ];

  useEffect(() => {
    if (searchedRole != null) {
      console.log(searchedRole);
      handleSelectRole(searchedRole);
    }
  }, [searchedRole]);

  const handleSelectRole = role => {
    if (selectedRole === role) return;
    setSelectedRole(role);
    localStorage.setItem('axiaSelectedRole', role);
  };

  return (
    <section>
      <div className='roles-cards__container'>
        {roles.map((role, index) => (
          <div
            key={index}
            className={`role-card ${
              selectedRole === role.name ? 'selectedRol' : ''
            }`}
            onClick={() => handleSelectRole(role.name)}>
            <h3>{role.name}</h3>
            <p>Resumen del rol</p>
            <p className='role-permissions'>{role.permission}</p>
            {selectedRole === role.name && <button>IR A VISTA</button>}
          </div>
        ))}
      </div>
    </section>
  );
};

export default RolesCards;
