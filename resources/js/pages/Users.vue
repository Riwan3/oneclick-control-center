<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useNotificationStore } from '../stores/notification';
import { useUsers } from '../composables/useUsers';
import { roles } from '../config/roles';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiStatus from '../components/ui/UiStatus.vue';
import UserAccessFields from '../components/users/UserAccessFields.vue';

const auth = useAuthStore();
const notifications = useNotificationStore();
const { users, loading, error, sites, modules, fetchUsers, fetchCatalogues, createUser, updateUser, deleteUser } =
    useUsers();

const STATUSES = ['active', 'inactive'];

function blankForm() {
    return {
        username: '',
        name: '',
        email: '',
        password: '',
        role: 'view',
        status: 'active',
        // Default role above is non-super-admin, so the default access
        // mode must be 'selected' — there is no implicit "all" for that
        // role (see UserAccessFields.vue / backend validation).
        siteAccessMode: 'selected',
        siteIds: [],
        moduleAccessMode: 'selected',
        moduleIds: [],
    };
}

const showCreateForm = ref(false);
const formError = ref(null);
const submitting = ref(false);
const form = ref(blankForm());

const editingUser = ref(null);
const editFormError = ref(null);
const editSubmitting = ref(false);
const editForm = ref(blankForm());

const actionError = ref(null);
const deletingId = ref(null);

const fieldClasses =
    'mt-1.5 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

function resetForm() {
    form.value = blankForm();
    formError.value = null;
}

async function openCreateForm() {
    editingUser.value = null;
    showCreateForm.value = !showCreateForm.value;
    if (showCreateForm.value) {
        resetForm();
        await fetchCatalogues();
    }
}

function accessPayload(source) {
    return {
        site_access_mode: source.siteAccessMode,
        site_ids: source.siteAccessMode === 'selected' ? source.siteIds : [],
        module_access_mode: source.moduleAccessMode,
        module_ids: source.moduleAccessMode === 'selected' ? source.moduleIds : [],
    };
}

async function handleCreate() {
    submitting.value = true;
    formError.value = null;

    const result = await createUser({
        username: form.value.username,
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        role: form.value.role,
        status: form.value.status,
        ...accessPayload(form.value),
    });

    if (result.success) {
        showCreateForm.value = false;
        resetForm();
        // The backend also sent the acting Super Admin a notification for
        // this — refresh the bell's unread count so it's visible right away.
        notifications.fetchUnreadCount();
    } else {
        formError.value = Object.values(result.errors ?? {}).flat()[0] ?? result.message;
    }

    submitting.value = false;
}

async function openEditForm(user) {
    showCreateForm.value = false;
    actionError.value = null;
    editingUser.value = user;
    editForm.value = {
        ...blankForm(),
        name: user.name,
        email: user.email,
        role: user.role,
        status: user.status,
        siteAccessMode: user.siteAccess?.mode ?? 'all',
        siteIds: user.siteAccess?.siteIds ?? [],
        moduleAccessMode: user.moduleAccess?.mode ?? 'all',
        moduleIds: user.moduleAccess?.moduleIds ?? [],
    };
    editFormError.value = null;
    await fetchCatalogues();
}

function closeEditForm() {
    editingUser.value = null;
}

async function handleUpdate() {
    editSubmitting.value = true;
    editFormError.value = null;

    const payload = {
        name: editForm.value.name,
        email: editForm.value.email,
        role: editForm.value.role,
        status: editForm.value.status,
        ...accessPayload(editForm.value),
    };
    if (editForm.value.password) payload.password = editForm.value.password;

    const result = await updateUser(editingUser.value.id, payload);

    if (result.success) {
        editingUser.value = null;
    } else {
        editFormError.value = Object.values(result.errors ?? {}).flat()[0] ?? result.message;
        // The record may no longer exist (stale list) — resync from the server.
        if (result.notFound) {
            editingUser.value = null;
            fetchUsers();
        }
    }

    editSubmitting.value = false;
}

async function handleDelete(user) {
    if (user.id === auth.user?.id) return;
    if (!window.confirm(`Delete ${user.username}? This cannot be undone.`)) return;

    actionError.value = null;
    deletingId.value = user.id;

    const result = await deleteUser(user.id);
    if (!result.success) {
        actionError.value = result.message;
        // The row was already gone server-side — refresh so the list
        // matches reality instead of staying stuck showing it.
        if (result.notFound) fetchUsers();
    }

    deletingId.value = null;
}

onMounted(fetchUsers);
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">User Management</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Control Center users, roles, site access, and module access.
                </p>
            </div>

            <UiButton v-if="auth.can('user_manage')" @click="openCreateForm">
                {{ showCreateForm ? 'Cancel' : 'Add User' }}
            </UiButton>
        </div>

        <UiCard v-if="showCreateForm">
            <h3 class="text-card-title mb-4">New User</h3>
            <form class="space-y-6" @submit.prevent="handleCreate">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-label" for="new-username">Username / NRP</label>
                        <input id="new-username" v-model="form.username" type="text" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="new-name">Name</label>
                        <input id="new-name" v-model="form.name" type="text" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="new-email">Email</label>
                        <input id="new-email" v-model="form.email" type="email" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="new-password">Password</label>
                        <input
                            id="new-password"
                            v-model="form.password"
                            type="password"
                            minlength="8"
                            required
                            :class="fieldClasses"
                        />
                    </div>
                    <div>
                        <label class="text-label" for="new-role">Control Center Role</label>
                        <select id="new-role" v-model="form.role" :class="fieldClasses">
                            <option v-for="r in roles" :key="r.key" :value="r.key">{{ r.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-label" for="new-status">Status</label>
                        <select id="new-status" v-model="form.status" :class="fieldClasses">
                            <option v-for="s in STATUSES" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-border pt-6">
                    <UserAccessFields
                        v-model:site-access-mode="form.siteAccessMode"
                        v-model:site-ids="form.siteIds"
                        v-model:module-access-mode="form.moduleAccessMode"
                        v-model:module-ids="form.moduleIds"
                        :role="form.role"
                        :sites="sites"
                        :modules="modules"
                    />
                </div>

                <p v-if="formError" class="text-sm font-medium text-danger">{{ formError }}</p>

                <UiButton type="submit" variant="primary" :disabled="submitting">
                    {{ submitting ? 'Creating…' : 'Create User' }}
                </UiButton>
            </form>
        </UiCard>

        <UiCard v-if="editingUser">
            <h3 class="text-card-title mb-1">Edit User</h3>
            <p class="text-caption mb-4">{{ editingUser.username }} — username / NRP cannot be changed.</p>
            <form class="space-y-6" @submit.prevent="handleUpdate">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-label" for="edit-name">Name</label>
                        <input id="edit-name" v-model="editForm.name" type="text" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="edit-email">Email</label>
                        <input id="edit-email" v-model="editForm.email" type="email" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="edit-password">New Password</label>
                        <input
                            id="edit-password"
                            v-model="editForm.password"
                            type="password"
                            minlength="8"
                            placeholder="Leave blank to keep current password"
                            :class="fieldClasses"
                        />
                    </div>
                    <div>
                        <label class="text-label" for="edit-role">Control Center Role</label>
                        <select id="edit-role" v-model="editForm.role" :class="fieldClasses">
                            <option v-for="r in roles" :key="r.key" :value="r.key">{{ r.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-label" for="edit-status">Status</label>
                        <select id="edit-status" v-model="editForm.status" :class="fieldClasses">
                            <option v-for="s in STATUSES" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-border pt-6">
                    <UserAccessFields
                        v-model:site-access-mode="editForm.siteAccessMode"
                        v-model:site-ids="editForm.siteIds"
                        v-model:module-access-mode="editForm.moduleAccessMode"
                        v-model:module-ids="editForm.moduleIds"
                        :role="editForm.role"
                        :sites="sites"
                        :modules="modules"
                    />
                </div>

                <p v-if="editFormError" class="text-sm font-medium text-danger">{{ editFormError }}</p>

                <div class="flex items-center gap-3">
                    <UiButton type="submit" variant="primary" :disabled="editSubmitting">
                        {{ editSubmitting ? 'Saving…' : 'Save Changes' }}
                    </UiButton>
                    <UiButton type="button" variant="ghost" @click="closeEditForm">Cancel</UiButton>
                </div>
            </form>
        </UiCard>

        <UiCard padding="sm">
            <p v-if="actionError" class="mb-3 text-sm font-medium text-danger">{{ actionError }}</p>

            <div v-if="loading" class="py-6 text-center text-body">Loading users…</div>
            <div v-else-if="error" class="py-6 text-center text-sm font-medium text-danger">{{ error }}</div>
            <div v-else-if="users.length === 0" class="py-6 text-center text-body">No users yet.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="px-3 py-2 text-left text-label">Username / NRP</th>
                            <th class="px-3 py-2 text-left text-label">Name</th>
                            <th class="px-3 py-2 text-left text-label">Role</th>
                            <th class="px-3 py-2 text-left text-label">Status</th>
                            <th class="px-3 py-2 text-left text-label">Site Access</th>
                            <th class="px-3 py-2 text-left text-label">Module Access</th>
                            <th v-if="auth.can('user_manage')" class="px-3 py-2 text-right text-label">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-b border-border last:border-0">
                            <td class="px-3 py-2.5 font-medium text-text-primary">{{ user.username }}</td>
                            <td class="px-3 py-2.5 text-text-secondary">
                                {{ user.name }}
                                <p class="text-caption">{{ user.email }}</p>
                            </td>
                            <td class="px-3 py-2.5">
                                <UiBadge variant="info">{{ user.role }}</UiBadge>
                            </td>
                            <td class="px-3 py-2.5">
                                <UiStatus
                                    :variant="user.status === 'active' ? 'success' : 'neutral'"
                                    :label="user.status"
                                />
                            </td>
                            <td class="px-3 py-2.5 text-text-secondary">{{ user.siteAccessSummary }}</td>
                            <td class="px-3 py-2.5 text-text-secondary">{{ user.moduleAccessSummary }}</td>
                            <td v-if="auth.can('user_manage')" class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <UiButton variant="ghost" size="sm" @click="openEditForm(user)">Edit</UiButton>
                                    <UiButton
                                        v-if="user.id !== auth.user?.id"
                                        variant="danger"
                                        size="sm"
                                        :disabled="deletingId === user.id"
                                        @click="handleDelete(user)"
                                    >
                                        {{ deletingId === user.id ? 'Deleting…' : 'Delete' }}
                                    </UiButton>
                                    <span v-else class="text-caption">You</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </UiCard>
    </div>
</template>
