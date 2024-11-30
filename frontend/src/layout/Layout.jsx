// src/layout/Layout.jsx
import React from 'react';
import Header from './Header';
import Footer from './Footer';
import { Outlet } from 'react-router-dom';

const Layout = () => {
  return (
    <>
      <Header />
      <main className='main_content'>
        <Outlet /> {/* Child routes will be rendered here */}
      </main>
      <Footer />
    </>
  );
};

export default Layout;
