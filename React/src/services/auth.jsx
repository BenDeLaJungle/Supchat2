export function login(token, mercureToken) {
  localStorage.setItem('authToken', token);
  localStorage.setItem('mercureToken', mercureToken);
}

export function logout() {
  localStorage.removeItem('authToken');
  localStorage.removeItem('mercureToken');
}

export function getToken() {
  return localStorage.getItem('authToken');
}

export function getMercureToken() {
  return localStorage.getItem('mercureToken');
}

export function isLoggedIn() {
  return !!getToken();
}
