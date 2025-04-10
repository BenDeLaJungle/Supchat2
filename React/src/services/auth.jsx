export function login(token) {
  localStorage.setItem('jwt', token);
}

export function logout() {
  localStorage.removeItem('jwt');
}

export function getToken() {
  return localStorage.getItem('jwt');
}

export function isLoggedIn() {
  return !!getToken();
}
