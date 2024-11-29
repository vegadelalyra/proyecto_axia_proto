import React from 'react';
import { FaArrowLeft } from 'react-icons/fa';
import { useNavigate } from 'react-router-dom';

const NotFound = () => {
  const navigate = useNavigate();

  const goBack = () => {
    const referrer = document.referrer;
    const domain = import.meta.env.VITE_DOMAIN;

    // Check if the referrer is part of the same domain
    if (referrer && referrer.includes(domain)) {
      navigate(-1); // Go back to the last page if it's within the same domain
    } else {
      navigate('/'); // Otherwise, navigate to the home page
    }
  };

  const goToHome = () => {
    navigate('/'); // Navigate to the home page
  };

  return (
    <section className='not_found_404'>
      <div style={{ display: 'flex', alignItems: 'center', gap: '58px' }}>
        <div
          className='back_arrow_404'
          onClick={goBack}
          style={{ cursor: 'pointer' }}>
          <FaArrowLeft />
        </div>
        <h1>404</h1>
      </div>
      <span className='message_404'>
        Tenemos problemas encontrando la página que estás solicitando, puedes
        volver al inicio con el siguiente botón:
      </span>
      <div className='stick_404'></div>
      <button onClick={goToHome}>VOLVER AL INICIO</button>
    </section>
  );
};

export default NotFound;
