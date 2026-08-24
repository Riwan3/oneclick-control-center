import { defineStore } from 'pinia';
import { CONTROL_CENTER_ROLES, getRole } from '../config/roles';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        isAuthenticated: false,
        user: null,
        // Mock/default role — stands in until real authentication exists.
        role: 'super_admin',
    }),
    getters: {
        roleDefinition: (state) => getRole(state.role),
    },
    actions: {
        setRole(role) {
            if (CONTROL_CENTER_ROLES.includes(role)) {
                this.role = role;
            }
        },
        hasRole(role) {
            return this.role === role;
        },
        can(capability) {
            return Boolean(getRole(this.role)?.capabilities?.[capability]);
        },
    },
});
