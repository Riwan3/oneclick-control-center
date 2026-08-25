// Talks to the real GET/POST /api/users endpoints (see
// routes/web.php + UserManagementController). No mock data here — this
// is the one page in this milestone backed by the real database rather
// than resources/js/data/mock/*.
import { ref } from 'vue';
import { userService } from '../services/userService';

export function useUsers() {
    const users = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // Site/module catalogues for the access pickers — fetched once when
    // the create/edit form is first opened, not on every page load.
    const sites = ref([]);
    const modules = ref([]);
    const cataloguesLoaded = ref(false);

    async function fetchUsers() {
        loading.value = true;
        error.value = null;
        const { ok, data } = await userService.fetchUsers();
        if (ok) {
            users.value = data?.data ?? data ?? [];
        } else {
            error.value = data?.message ?? 'Unable to load users.';
        }
        loading.value = false;
    }

    async function fetchCatalogues() {
        if (cataloguesLoaded.value) return;

        const [sitesResult, modulesResult] = await Promise.all([
            userService.fetchSites(),
            userService.fetchModules(),
        ]);

        if (sitesResult.ok) sites.value = sitesResult.data ?? [];
        if (modulesResult.ok) modules.value = modulesResult.data ?? [];
        cataloguesLoaded.value = true;
    }

    async function createUser(payload) {
        const { ok, data } = await userService.createUser(payload);

        if (ok) {
            users.value = [...users.value, data?.data ?? data];
            return { success: true };
        }

        return {
            success: false,
            message: data?.message ?? 'Unable to create user.',
            errors: data?.errors ?? {},
        };
    }

    async function updateUser(id, payload) {
        const { ok, status, data } = await userService.updateUser(id, payload);

        if (ok) {
            const updated = data?.data ?? data;
            users.value = users.value.map((u) => (u.id === id ? updated : u));
            return { success: true };
        }

        return {
            success: false,
            message: data?.message ?? 'Unable to update user.',
            errors: data?.errors ?? {},
            notFound: status === 404,
        };
    }

    async function deleteUser(id) {
        const { ok, status, data } = await userService.deleteUser(id);

        if (ok) {
            users.value = users.value.filter((u) => u.id !== id);
            return { success: true };
        }

        return {
            success: false,
            message: data?.message ?? 'Unable to delete user.',
            notFound: status === 404,
        };
    }

    return {
        users,
        loading,
        error,
        sites,
        modules,
        fetchUsers,
        fetchCatalogues,
        createUser,
        updateUser,
        deleteUser,
    };
}
