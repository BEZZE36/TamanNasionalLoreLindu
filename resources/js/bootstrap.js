import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token for all axios requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    window.csrfToken = csrfToken;
}

// Interceptor untuk handle 419 error (CSRF token expired)
// Auto-reload halaman untuk mendapatkan token baru
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            window.location.reload();
            return new Promise(() => { }); // Pending promise to prevent further handling
        }
        return Promise.reject(error);
    }
);
