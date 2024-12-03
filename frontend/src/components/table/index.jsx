import React from 'react';
import TableHeader from './tableHeader';
import TableBody from './tableBody';

const Table = ({ section }) => {
  return (
    <main className='table'>
      <TableHeader section={section} />
      <TableBody />
    </main>
  );
};

export default Table;
