import React, { useState } from 'react';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);

  return (
    <form>
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'flex-start',
        }}>
        <p style={{ color: 'var(--color-secundario)' }}>Inicio de sesión</p>
        <p style={{ color: 'var(--texto-secundario)' }}>GMAO WEB</p>
      </div>

      {/* <label htmlFor='usuario'>Usuario</label> */}
      <input id='usuario' type='text' placeholder='Usuario' />

      <a href='#' style={{ color: '#2071B7' }}>
        ¿Olvidaste tu usuario?
      </a>

      {/* <label htmlFor='contraseña'>Contraseña</label> */}
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
        <label htmlFor='remember'>
          Recordar mi{' '}
          <span style={{ color: 'var(--color-principal)' }}>
            inicio de sesión
          </span>
        </label>
      </div>

      <button type='submit'>INICIAR SESIÓN</button>

      <div className='links'>
        <a href='#'>Crear un usuario</a>
      </div>
    </form>
  );
};

export default Login;
