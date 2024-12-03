import React from 'react';
import Panel from '../components/panel';
import Table from '../components/table';
import { useLocation } from 'react-router-dom';
import { ROUTES } from '../routes/routes';

const Gmao = () => {
  let location = useLocation();
  location = decodeURIComponent(location.pathname.toLowerCase());
  if (location.endsWith('/')) location = location.slice(0, -1);

  const resolvedSection = ROUTES[location] || '/';

  return (
    <section style={{ display: 'flex', gap: '100px' }}>
      <Panel section={resolvedSection} />
      <Table section={resolvedSection} />
    </section>
  );
};

export default Gmao;
