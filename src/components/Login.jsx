import React, { useState } from 'react';
import { FaEye, FaEyeSlash } from 'react-icons/fa';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);

  const [formData, setFormData] = useState({
    username: '',
    password: '',
  });

  // Simulate database for testing porpuses
  const usersDatabase = [{ username: 'demo', password: 'demo' }];

  const handleSubmit = event => {
    event.preventDefault(); // Prevent the default form submission

    // Check if the entered username and password match any entry in the database
    const user = usersDatabase.find(
      user =>
        user.username === formData.username &&
        user.password === formData.password
    );

    if (user) {
      alert('LOGGED IN');
    } else {
      alert('Invalid credentials');
    }

    console.log('Form submitted:', formData);
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
    <form onSubmit={handleSubmit}>
      <div className='login-header'>
        <p className='login-title'>Inicio de sesión</p>
        <p className='login-subtitle'>GMAO WEB</p>
      </div>

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

      <span style={{ display: 'flex', justifyContent: 'flex-end' }}>
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

      <span style={{ display: 'flex', justifyContent: 'flex-end' }}>
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
          <span>Crear un usuario</span> {/* Remove from tabbing */}
        </a>
      </div>
    </form>
  );
};

export default Login;
