import React, { useState } from 'react';
import { FaEye, FaEyeSlash } from 'react-icons/fa';
import axios from 'axios';
import { handleError } from '../utils/errorHandler';
import { ENDPOINTS } from '../constants/endpoints';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);

  const [formData, setFormData] = useState({
    username: '',
    password: '',
  });

  const handleLogin = async e => {
    e.preventDefault();

    try {
      const response = await axios.post(ENDPOINTS.USER_AUTHENTICATION, {
        userId: formData.username,
        password: formData.password,
      });

      if (response.data.success) {
        alert('LOGGED IN! :D');
        console.log(response.data);
      } else {
        console.error(response.data);
      }
    } catch (error) {
      handleError({
        message: error.message,
        status: error.status,
        code: error.code,
      });
    }
  };

  // Handle input change
  const handleInputChange = event => {
    const { name, value } = event.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };

  return (
    <form onSubmit={handleLogin}>
      <div className='login-header'>
        <span className='login-title'>Inicio de sesión</span>
        <span className='login-subtitle'>GMAO WEB</span>
      </div>
      <section className='center-content'>
        {/* <label htmlFor='usuario'>Usuario</label> */}
        <input
          id='username'
          name='username'
          type='text'
          value={formData.username}
          onChange={handleInputChange}
          placeholder='Usuario'
          tabIndex={1}
        />

        <span
          style={{
            display: 'flex',
            justifyContent: 'flex-end',
            marginBottom: '50px',
          }}>
          <a href='#' tabIndex={-1}>
            ¿Olvidaste tu usuario?
          </a>
        </span>

        {/* <label htmlFor='contraseña'>Contraseña</label> */}
        <div style={{ position: 'relative' }}>
          <input
            id='password'
            name='password'
            type={showPassword ? 'text' : 'password'}
            value={formData.password}
            onChange={handleInputChange}
            placeholder='Contraseña'
            autoComplete='true'
            tabIndex={2}
          />
          <span
            style={{
              position: 'absolute',
              right: '20px',
              top: '50%',
              transform: 'translateY(-50%)',
              cursor: 'pointer',
              fontSize: '40px',
            }}
            onClick={() => setShowPassword(!showPassword)}>
            {showPassword ? <FaEyeSlash /> : <FaEye />}{' '}
            {/* Conditional rendering */}
          </span>
        </div>

        <span
          style={{
            display: 'flex',
            justifyContent: 'flex-end',
            marginBottom: '25px',
          }}>
          <a href='#' tabIndex={-1}>
            Restablecer contraseña
          </a>
        </span>

        <div className='checkbox-container'>
          <input type='checkbox' id='remember' tabIndex={3} />
          <label htmlFor='remember' tabIndex={-1}>
            Recordar mi <span>inicio de sesión</span>
          </label>
        </div>

        <button type='submit'>INICIAR SESIÓN</button>

        <div className='links'>
          <a href='#' tabIndex='-1'>
            <span>Crear un usuario</span>
          </a>
        </div>
      </section>
    </form>
  );
};

export default Login;
