// src/constants/error.js
export const ERROR = {
  USER_AUTH_FAILED: {
    code: 'AUTH_FAILED',
    status: 401,
    message: 'User authentication failed. Please check your credentials.',
    response: 'Error de autenticación. Verifica tus credenciales.', // Spanish message
  },
  NETWORK_ERROR: {
    code: 'NETWORK_ERROR',
    status: 500,
    message: 'A network error occurred. Please try again later.',
    response:
      'Se produjo un error de red. Por favor, inténtalo de nuevo más tarde.', // Spanish message
  },
  UNEXPECTED_ERROR: {
    code: 'UNEXPECTED_ERROR',
    status: 500,
    message: 'An unexpected error occurred. Please contact support.',
    response:
      'Se produjo un error inesperado. Por favor, contacta con soporte.', // Spanish message
  },
};
