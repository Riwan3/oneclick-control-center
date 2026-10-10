import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { useAuthStore } from './stores/auth';
import { registerSessionExpiredHandler } from './lib/http';
import '../css/app.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Centralized session-expiry handling (401/419) — see the comment on
// registerSessionExpiredHandler in lib/http.js for why this wiring
// lives here rather than in http.js itself. Only reacts when the store
// currently believes the user is authenticated, so the ordinary
// "not logged in yet" 401 on first app load (before any login) is left
// alone — the router guard already sends that visitor to /login.
registerSessionExpiredHandler((reason) => {
    const auth = useAuthStore(pinia);

    if (!auth.authenticated) {
        return;
    }

    auth.clearSession();

    if (router.currentRoute.value.name !== 'login') {
        router.push({
            name: 'login',
            query: { redirect: router.currentRoute.value.fullPath, reason },
        });
    }
});

app.mount('#app');