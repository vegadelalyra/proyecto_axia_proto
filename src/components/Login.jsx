import React, { useState } from 'react';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);

  return (
    <form>
      <div className='login-header'>
        <p className='login-title'>Inicio de sesión</p>
        <p className='login-subtitle'>GMAO WEB</p>
      </div>

      {/* <label htmlFor='usuario'>Usuario</label> */}
      <input id='usuario' type='text' placeholder='Usuario' />

      <a href='#'>¿Olvidaste tu usuario?</a>

      {/* <label htmlFor='contraseña'>Contraseña</label> */}
      <div style={{ position: 'relative' }}>
        <input
          id='contraseña'
          type={showPassword ? 'text' : 'password'}
          placeholder='Contraseña'
          autoComplete='true'
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

      <a href='#'>Restablecer contraseña</a>

      <div className='checkbox-container'>
        <input type='checkbox' id='remember' />
        <label htmlFor='remember'>
          Recordar mi <span>inicio de sesión</span>
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
