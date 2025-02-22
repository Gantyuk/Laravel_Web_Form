import { createApp } from 'vue'
import App from './src/App.vue'
import { plugin, defaultConfig } from '@formkit/vue';

createApp(App)
    .use(plugin, defaultConfig())
    .mount('#app');
