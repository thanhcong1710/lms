import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

const app = createApp(App);
import i18n from './i18n';
import Pagination from './components/Pagination.vue';
app.component('Pagination', Pagination);
app.use(router);
app.use(i18n);
app.mount('#app');
