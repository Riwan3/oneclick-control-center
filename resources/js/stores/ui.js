import { defineStore } from 'pinia';

// Below this width (Tailwind's `lg`) the sidebar becomes an off-canvas
// drawer — phones and iPad portrait. At/above it, the fixed sidebar
// (expanded or collapsed to a rail) is used, as before.
const DESKTOP_QUERY = '(min-width: 1024px)';

export const useUiStore = defineStore('ui', {
    state: () => ({
        sidebarCollapsed: false,
        mobileNavOpen: false,
        isDesktop: typeof window === 'undefined' ? true : window.matchMedia(DESKTOP_QUERY).matches,
        isLoading: false,
    }),
    getters: {
        // The icon-only rail only exists on desktop; the drawer is always full width.
        railCollapsed: (state) => state.isDesktop && state.sidebarCollapsed,
    },
    actions: {
        toggleSidebar() {
            if (this.isDesktop) {
                this.sidebarCollapsed = !this.sidebarCollapsed;
            } else {
                this.mobileNavOpen = !this.mobileNavOpen;
            }
        },
        closeMobileNav() {
            this.mobileNavOpen = false;
        },
        watchViewport() {
            const mq = window.matchMedia(DESKTOP_QUERY);
            const sync = () => {
                this.isDesktop = mq.matches;
                if (mq.matches) this.mobileNavOpen = false;
            };
            sync();
            mq.addEventListener('change', sync);
        },
    },
});
