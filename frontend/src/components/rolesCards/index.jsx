import React, { useEffect, useState } from 'react';
import { ROLES } from '../../constants/domain';

const RolesCards = ({ searchedRole }) => {
  const [selectedRole, setSelectedRole] = useState(null);

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
      setSelectedRole(searchedRole);
    }
  }, [searchedRole]);

  const handleSelectRole = role => {
    if (selectedRole === role.name) return;
    setSelectedRole(role.name);
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
            onClick={() => handleSelectRole(role)}>
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
