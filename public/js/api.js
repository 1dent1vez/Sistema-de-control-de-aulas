/**
 * apiFetch — Wrapper global de fetch con auto-auth, manejo de errores y 401 redirect.
 *
 * @autor        Equipo GAMA
 * @version      1.0.0
 * @creado       2026-05-19
 */

(function () {
  'use strict';

  var TOAST_DURATION = 4500;

  function getToken() {
    return localStorage.getItem('auth_token');
  }

  function clearSession() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    document.cookie = 'sam_token=; path=/; max-age=0; SameSite=Lax';
  }

  function redirectLogin() {
    clearSession();
    window.location.href = '/';
  }

  function showToast(title, message, type) {
    type = type || 'success';
    var container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
      document.body.appendChild(container);
    }
    var t = document.createElement('div');
    t.style.cssText = 'background:#fff;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,.12);padding:12px 16px;' +
      'display:flex;align-items:center;gap:12px;min-width:300px;transform:translateX(120%);' +
      'transition:transform .25s ease;border-left:4px solid ' + (type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#28a745') + ';';
    t.innerHTML = '<div style="width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;' +
      'background:' + (type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#28a745') + ';color:#fff;font-size:12px;">' +
      '<i class="fas ' + (type === 'error' ? 'fa-times' : type === 'warning' ? 'fa-exclamation' : 'fa-check') + '"></i></div>' +
      '<div style="flex:1"><div style="font-weight:600;font-size:14px;color:#212529;">' + title + '</div>' +
      '<div style="font-size:13px;color:#6c757d;">' + message + '</div></div>' +
      '<button onclick="this.parentElement.remove()" style="background:none;border:none;color:#ced4da;cursor:pointer;padding:4px;"><i class="fas fa-times"></i></button>';
    container.appendChild(t);
    requestAnimationFrame(function () { t.style.transform = 'translateX(0)'; });
    setTimeout(function () { t.style.transform = 'translateX(120%)'; setTimeout(function () { t.remove(); }, 260); }, TOAST_DURATION);
  }

  function apiFetch(url, options) {
    options = options || {};
    options.headers = options.headers || {};

    var token = getToken();
    if (token) {
      options.headers['Authorization'] = 'Bearer ' + token;
    }

    if (!options.headers['Accept']) {
      options.headers['Accept'] = 'application/json';
    }

    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
      if (!options.headers['Content-Type']) {
        options.headers['Content-Type'] = 'application/json';
      }
      options.body = JSON.stringify(options.body);
    }

    return fetch(url, options)
      .then(function (response) {
        if (response.status === 401 || response.status === 403) {
          showToast('Sesión expirada', 'Redirigiendo al inicio de sesión...', 'warning');
          setTimeout(redirectLogin, 800);
          throw new Error(response.status === 401 ? 'Unauthenticated' : 'Forbidden');
        }
        return response.json().then(function (data) {
          if (!response.ok) {
            var err = new Error(data.message || 'Error del servidor');
            err.status = response.status;
            err.data = data;
            throw err;
          }
          return data;
        });
      })
      .catch(function (err) {
        if (err.message === 'Unauthenticated' || err.message === 'Forbidden') throw err;
        if (err.name === 'TypeError' || err.message === 'Failed to fetch' || err.name === 'AbortError') {
          showToast('Sin conexión', 'Sin conexión a internet. Verifica tu red e intenta de nuevo.', 'error');
          throw new Error('Sin conexión');
        }
        if (err instanceof SyntaxError) {
          showToast('Respuesta inválida', 'El servidor devolvió una respuesta inesperada.', 'error');
          throw new Error('Respuesta inválida');
        }
        if (err.status >= 500 || !err.status) {
          showToast('Error del servidor', 'Ocurrió un error inesperado. Contacta al administrador.', 'error');
        }
        throw err;
      });
  }

  window.apiFetch = apiFetch;
  window.showToast = showToast;
  window.clearSession = clearSession;
})();
