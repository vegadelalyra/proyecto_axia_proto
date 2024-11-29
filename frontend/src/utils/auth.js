import axios from 'axios';
import Cookies from 'js-cookie';
import { ENDPOINTS } from '../constants/endpoints';
import { handleError } from './errorHandler';

export const login = async ({ username, password, rememberMe }) => {
  try {
    const response = await axios.post(ENDPOINTS.USER_AUTHENTICATION, {
      userId: username,
      password: password,
    });

    if (response.data.success) {
      const userData = response.data.data;

      // Save user data to cookies
      Cookies.set('session', JSON.stringify(userData), {
        expires: rememberMe ? 7 : 1,
        secure: true,
        sameSite: 'strict',
      });

      return true;
    } else {
      console.error('Login failed:', response.data);
      return false;
    }
  } catch (error) {
    handleError({
      message: error.message,
      status: error.response?.status,
      code: error.code,
    });
    return false;
  }
};

export const logout = () => {
  Cookies.remove('session');
  window.location.reload(); // Reload to redirect back to the login page
};
