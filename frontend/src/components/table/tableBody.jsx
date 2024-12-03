import { Route, Routes } from 'react-router-dom';
import DynamicTable from '../dynamicTable';

const TableBody = () => {
  return (
    <section className='table__body'>
      <Routes>
        <Route path='/roles/vista' element={''} />
        <Route path='/familias/crud' element={''} />
      </Routes>
    </section>
  );
};

export default TableBody;
