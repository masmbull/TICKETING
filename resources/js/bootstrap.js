import axios from 'axios';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.axios = axios;
window.Alpine = Alpine;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Alpine.plugin(collapse);
Alpine.start();
