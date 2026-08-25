import { defineStore } from 'pinia';
import { CONTROL_CENTER_ROLES, getRole } from '../config/roles';
import { apiJson } from '../lib/http';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        // { id, username, name, email, role, status, siteAccessSummary, moduleAccessSummary }
        user: null,
        authenticated: false,
        // Set once fetchUser() has resolved for the first time — the
        // router guard uses this to avoid re-checking on every navigation.
        initialized: false,
        loading: false,
        error: null,
    }),
    getters: {
        role: (state) => state.user?.role ?? null,
        roleDefinition: (state) => (state.user ? getRole(state.user.role) : null),
    },
    actions: {
        hasRole(role) {
            return this.user?.role === role;
        },
        can(capability) {
            if (!this.user) return false;
            return Boolean(getRole(this.user.role)?.capabilities?.[capability]);
        },

        // DEV-ONLY: locally overrides the current user's role so
        // NavigationPreview.vue can exercise navigation/capability
        // resolution for each role. Never calls the backend, and is
        // overwritten the next time fetchUser() runs.
        setRole(role) {
            if (this.user && CONTROL_CENTER_ROLES.includes(role)) {
                this.user = { ...this.user, role };
            }
        },

        // Asks the backend whether the current session is authenticated
        // and, if so, who the user is. Called once by the router guard
        // before the first navigation resolves.
        async fetchUser() {
            this.loading = true;
            try {
                const { ok, data } = await apiJson('/api/user');
                if (ok) {
                    this.user = data?.data ?? data;
                    this.authenticated = true;
                } else {
                    this.user = null;
                    this.authenticated = false;
                }
            } finally {
                this.loading = false;
                this.initialized = true;
            }
        },

        async login(username, password) {
            this.loading = true;
            this.error = null;
            try {
                const { ok, status, data } = await apiJson('/login', {
                    method: 'POST',
                    body: JSON.stringify({ username, password }),
                });

                if (ok) {
                    this.user = data?.data ?? data;
                    this.authenticated = true;
                    this.initialized = true;
                    return true;
                }

                this.error =
                    status === 422
                        ? (Object.values(data?.errors ?? {}).flat()[0] ?? 'Invalid username or password.')
                        : (data?.message ?? 'Unable to sign in. Please try again.');
                return false;
            } catch {
                this.error = 'Unable to reach the server. Please try again.';
                return false;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            this.loading = true;
            try {
                await apiJson('/logout', { method: 'POST' });
            } finally {
                this.user = null;
                this.authenticated = false;
                this.loading = false;
            }
        },
    },
});
