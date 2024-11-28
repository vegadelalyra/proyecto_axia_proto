import React, { useState } from 'react';
import { FaEye, FaEyeSlash } from 'react-icons/fa';
import axios from 'axios';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);

  const [formData, setFormData] = useState({
    username: '',
    password: '',
  });

  const [message, setMessage] = useState(''); // Para mostrar mensajes de error o éxito

  // Manejar el evento del envío del formulario
  const handleLogin = async e => {
    e.preventDefault(); // Prevenir el comportamiento por defecto del formulario

    try {
      // Enviar solicitud POST al backend con los datos del formulario
      const response = await axios.post(
        'http://localhost:3000/gmaoweb/api/includes/business/UserAuthenticator.php',
        {
          userId: formData.username,
          password: formData.password,
        }
      );

      // Verificar la respuesta
      if (response.data.success) {
        setMessage('Login exitoso');
        console.log(response.data); // Si es necesario mostrar los datos, puedes hacerlo aquí
      } else {
        setMessage('Credenciales incorrectas');
      }
    } catch (error) {
      // No mostramos nada en la consola
      if (error.response) {
        // Manejar el error pero sin loguearlo
        if (error.response.status === 401) {
          setMessage('Credenciales incorrectas');
        } else {
          setMessage('Error en la autenticación: ' + error.response.statusText);
        }
      } else {
        setMessage('Error al conectar con el servidor');
      }
    }
  };

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
            <span>Crear un usuario</span> {/* Remove from tabbing */}
          </a>
        </div>
      </section>
    </form>
  );
};

export default Login;
