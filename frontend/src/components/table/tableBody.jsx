import { Route, Routes } from 'react-router-dom';
import RolesCards from '../RolesCards';
import DynamicTable from '../dynamicTable';

const TableBody = () => {
  return (
    <section className='table__body'>
      <Routes>
        <Route path='/roles/vista' element={''} />
        <Route path='/familias/crud' element={<DynamicTable />} />
      </Routes>
    </section>
  );
};

export default TableBody;
