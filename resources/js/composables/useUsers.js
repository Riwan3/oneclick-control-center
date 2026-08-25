// Talks to the real GET/POST /api/users endpoints (see
// routes/web.php + UserManagementController). No mock data here — this
// is the one page in this milestone backed by the real database rather
// than resources/js/data/mock/*.
import { ref } from 'vue';
import { apiJson } from '../lib/http';

export function useUsers() {
    const users = ref([]);
    const loading = ref(false);
    const error = ref(null);

    async function fetchUsers() {
        loading.value = true;
        error.value = null;
        const { ok, data } = await apiJson('/api/users');
        if (ok) {
            users.value = data?.data ?? data ?? [];
        } else {
            error.value = data?.message ?? 'Unable to load users.';
        }
        loading.value = false;
    }

    async function createUser(payload) {
        const { ok, data } = await apiJson('/api/users', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

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
        const { ok, status, data } = await apiJson(`/api/users/${id}`, {
            method: 'PATCH',
            body: JSON.stringify(payload),
        });

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
        const { ok, status, data } = await apiJson(`/api/users/${id}`, { method: 'DELETE' });

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

    return { users, loading, error, fetchUsers, createUser, updateUser, deleteUser };
}
