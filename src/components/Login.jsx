import React, { useState } from 'react';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);

  return (
    <form>
      <h1>Inicio de Sesión</h1>
      <p style={{ color: '#6C6C6C' }}>GMAO WEB</p>

      <label htmlFor='usuario'>Usuario</label>
      <input id='usuario' type='text' placeholder='Usuario' />

      <a href='#' style={{ color: '#2071B7' }}>
        ¿Olvidaste tu usuario?
      </a>

      <label htmlFor='contraseña'>Contraseña</label>
      <div style={{ position: 'relative' }}>
        <input
          id='contraseña'
          type={showPassword ? 'text' : 'password'}
          placeholder='Contraseña'
        />
        <span
          style={{
            position: 'absolute',
            right: '10px',
            top: '50%',
            transform: 'translateY(-50%)',
            cursor: 'pointer',
          }}
          onClick={() => setShowPassword(!showPassword)}>
          👁
        </span>
      </div>

      <a href='#' style={{ color: '#2071B7' }}>
        Restablecer contraseña
      </a>

      <div className='checkbox-container'>
        <input type='checkbox' id='remember' />
        <label htmlFor='remember'>Recordar mi inicio de sesión</label>
      </div>

      <button type='submit'>INICIAR SESIÓN</button>

      <div className='links'>
        <a href='#'>Crear un usuario</a>
      </div>
    </form>
  );
};

export default Login;
