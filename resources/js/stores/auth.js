import { defineStore } from 'pinia';

// The three official Control Center roles. A OneClick site's own local
// "Super Admin" is a separate, site-level concept — not one of these.
export const CONTROL_CENTER_ROLES = ['super_admin', 'management', 'view'];

export const useAuthStore = defineStore('auth', {
    state: () => ({
        isAuthenticated: false,
        user: null,
        // Mock/default role — stands in until real authentication exists.
        role: 'super_admin',
    }),
    actions: {
        setRole(role) {
            if (CONTROL_CENTER_ROLES.includes(role)) {
                this.role = role;
            }
        },
    },
});
