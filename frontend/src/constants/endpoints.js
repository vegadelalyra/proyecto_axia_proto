const API_URL = import.meta.env.VITE_DOMAIN + import.meta.env.VITE_PHP_API_URL;

export const ENDPOINTS = {
  API_URL,
  USER_AUTHENTICATION: `${API_URL}includes/business/UserAuthenticator.php`,
  // Add more endpoints here as needed
};
