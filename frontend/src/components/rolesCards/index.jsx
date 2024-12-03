import React, { useState } from 'react';

const RolesCards = () => {
  const [selectedRole, setSelectedRole] = useState(null);

  const roles = [
    { name: 'Administrador', permission: 'Gestiona el sistema' },
    { name: 'Reporter', permission: 'Genera informes' },
    { name: 'Curador', permission: 'Supervisa contenido' },
    { name: 'Reparador', permission: 'Repara equipos' },
    { name: 'Diseñador', permission: 'Crea diseños' },
    { name: 'Cliente Final', permission: 'Accede a los recursos' },
    { name: 'Calidad', permission: 'Controla calidad' },
    { name: 'Control', permission: 'Gestiona control' },
    { name: 'Planificador', permission: 'Organiza tareas' },
    { name: 'Manager', permission: 'Administra equipos' },
    { name: 'Fabricante', permission: 'Produce bienes' },
    { name: 'Support', permission: 'Brinda soporte' },
  ];

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
