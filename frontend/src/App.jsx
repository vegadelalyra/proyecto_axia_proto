import React from 'react';
import Cookies from 'js-cookie';
import { Routes, Route } from 'react-router-dom';

import Layout from './layout/Layout';
import Login from './pages/Login';
import Gmao from './pages/Gmao';
import NotFound from './pages/NotFound';

const App = () => {
  const session = Cookies.get('session');
  const isAuthenticated = Boolean(session);

  return (
    <Routes>
      <Route path='/' element={<Layout />}>
        <Route index element={isAuthenticated ? <Gmao /> : <Login />} />
      </Route>
      <Route path='*' element={<NotFound />} />
    </Routes>
  );
};

export default App;
