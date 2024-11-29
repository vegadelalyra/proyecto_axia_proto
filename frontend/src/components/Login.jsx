import React, { useState } from 'react';
import { FaEye, FaEyeSlash } from 'react-icons/fa';
import axios from 'axios';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);

  const [formData, setFormData] = useState({
    username: '',
    password: '',
  });

  // Manejar el evento del envío del formulario
  const handleLogin = async e => {
    e.preventDefault(); // Prevenir el comportamiento por defecto del formulario

    try {
      // Enviar solicitud POST al backend con los datos del formulario
      const response = await axios.post(
        import.meta.env.VITE_DOMAIN +
          import.meta.env.VITE_PHP_API_URL +
          'includes/business/UserAuthenticator.php',
        {
          userId: formData.username,
          password: formData.password,
        }
      );

      // Verificar la respuesta
      if (response.data.success) {
        console.log(response.data);
      } else {
        console.error(response.data);
      }
    } catch (error) {
      // No mostramos nada en la consola
      if (error.response) {
        // Manejar el error pero sin loguearlo
        if (error.response.status === 401) {
          console.error(error.response.message);
        } else {
          console.error(error.response.message);
        }
      } else {
        console.error(error);
      }
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
