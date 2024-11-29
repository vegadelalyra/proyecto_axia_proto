export const login = (username, password) => {
  if (username === 'admin' && password === 'password') {
    localStorage.setItem('isLoggedIn', 'true');
    return true;
  }
  return false;
};

export const logout = () => {
  localStorage.removeItem('isLoggedIn');
};
