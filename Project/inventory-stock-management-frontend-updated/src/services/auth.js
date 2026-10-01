import api from './api';

export const login = async (email, password) => {
  const response = await api.post('/login', { email, password });
  return response.data;
};

export const register = async (name, email, password) => {
  const response = await api.post('/register', { name, email, password });
  return response.data;
};

export const logout = async () => {
  const response = await api.post('/logout');
  return response.data;
};
