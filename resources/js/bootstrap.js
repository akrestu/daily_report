/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * The following block of code provides CSRF token setup for axios
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Bootstrap JS dimuat sekali sebagai script klasik dari public/vendor (layout).
// Jangan import 'bootstrap' di sini: dua salinan membuat dropdown/modal terbuka lalu langsung tertutup.
