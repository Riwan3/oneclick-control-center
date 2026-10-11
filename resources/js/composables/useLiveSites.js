import { onMounted, onUnmounted } from 'vue';
import { loadSites } from '../data/siteCatalogue';

const REFRESH_MS = 60_000;

// Loads the site catalogue from the API and keeps it fresh. Call once
// from the authenticated layout. A failed refresh keeps the last data.
export function useLiveSites() {
    let timer = null;

    onMounted(() => {
        loadSites();
        timer = setInterval(loadSites, REFRESH_MS);
    });

    onUnmounted(() => clearInterval(timer));
}
