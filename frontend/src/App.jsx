import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Layout from './layout/Layout';
import Login from './pages/Login';
import NotFound from './pages/NotFound';

const App = () => {
  const isAuthenticated = localStorage.getItem('isAuthenticated');

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
