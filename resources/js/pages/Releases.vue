<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useReleases } from '../composables/useReleases';
import { Release } from '../config/releaseOptions';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiStatus from '../components/ui/UiStatus.vue';

const auth = useAuthStore();
const { releases, loading, error, fetchReleases, createRelease, updateRelease } = useReleases();

function blankForm() {
    return { version: '', title: '', status: 'draft', release_date: '', description: '' };
}

const showCreateForm = ref(false);
const formError = ref(null);
const submitting = ref(false);
const form = ref(blankForm());

const editingRelease = ref(null);
const editFormError = ref(null);
const editSubmitting = ref(false);
const editForm = ref(blankForm());

const fieldClasses =
    'mt-1.5 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

function resetForm() {
    form.value = blankForm();
    formError.value = null;
}

function openCreateForm() {
    editingRelease.value = null;
    showCreateForm.value = !showCreateForm.value;
    if (showCreateForm.value) resetForm();
}

async function handleCreate() {
    submitting.value = true;
    formError.value = null;

    const result = await createRelease({
        ...form.value,
        release_date: form.value.release_date || null,
    });

    if (result.success) {
        showCreateForm.value = false;
        resetForm();
    } else {
        formError.value = Object.values(result.errors ?? {}).flat()[0] ?? result.message;
    }

    submitting.value = false;
}

function openEditForm(release) {
    showCreateForm.value = false;
    editingRelease.value = release;
    editForm.value = {
        version: release.version,
        title: release.title,
        status: release.status,
        release_date: release.releaseDate ?? '',
        description: release.description ?? '',
    };
    editFormError.value = null;
}

function closeEditForm() {
    editingRelease.value = null;
}

async function handleUpdate() {
    editSubmitting.value = true;
    editFormError.value = null;

    const result = await updateRelease(editingRelease.value.id, {
        ...editForm.value,
        release_date: editForm.value.release_date || null,
    });

    if (result.success) {
        editingRelease.value = null;
    } else {
        editFormError.value = Object.values(result.errors ?? {}).flat()[0] ?? result.message;
    }

    editSubmitting.value = false;
}

onMounted(fetchReleases);
</script>

<template>
    <div v-if="auth.can('version_view')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Release Management</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Formal Control Center release catalog and changelog — independent from the
                    Master Baseline version, per-site deployment versions, and Configuration
                    Snapshots.
                </p>
            </div>

            <UiButton v-if="auth.can('release_manage')" @click="openCreateForm">
                {{ showCreateForm ? 'Cancel' : 'New Release' }}
            </UiButton>
        </div>

        <UiCard v-if="showCreateForm">
            <h3 class="text-card-title mb-4">New Release</h3>
            <form class="space-y-6" @submit.prevent="handleCreate">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-label" for="new-version">Version</label>
                        <input id="new-version" v-model="form.version" type="text" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="new-title">Title</label>
                        <input id="new-title" v-model="form.title" type="text" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="new-status">Status</label>
                        <select id="new-status" v-model="form.status" :class="fieldClasses">
                            <option v-for="s in Release.STATUSES" :key="s" :value="s">{{ Release.STATUS_LABEL[s] }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-label" for="new-release-date">Release Date</label>
                        <input id="new-release-date" v-model="form.release_date" type="date" :class="fieldClasses" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-label" for="new-description">Description</label>
                        <textarea id="new-description" v-model="form.description" rows="3" :class="fieldClasses"></textarea>
                    </div>
                </div>

                <p v-if="formError" class="text-sm font-medium text-danger">{{ formError }}</p>

                <UiButton type="submit" variant="primary" :disabled="submitting">
                    {{ submitting ? 'Creating…' : 'Create Release' }}
                </UiButton>
            </form>
        </UiCard>

        <UiCard v-if="editingRelease">
            <h3 class="text-card-title mb-4">Edit Release — {{ editingRelease.version }}</h3>
            <form class="space-y-6" @submit.prevent="handleUpdate">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-label" for="edit-version">Version</label>
                        <input id="edit-version" v-model="editForm.version" type="text" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="edit-title">Title</label>
                        <input id="edit-title" v-model="editForm.title" type="text" required :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="edit-status">Status</label>
                        <select id="edit-status" v-model="editForm.status" :class="fieldClasses">
                            <option v-for="s in Release.STATUSES" :key="s" :value="s">{{ Release.STATUS_LABEL[s] }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-label" for="edit-release-date">Release Date</label>
                        <input id="edit-release-date" v-model="editForm.release_date" type="date" :class="fieldClasses" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-label" for="edit-description">Description</label>
                        <textarea id="edit-description" v-model="editForm.description" rows="3" :class="fieldClasses"></textarea>
                    </div>
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
            <div v-if="loading" class="py-6 text-center text-body">Loading releases…</div>
            <div v-else-if="error" class="py-6 text-center text-sm font-medium text-danger">{{ error }}</div>
            <div v-else-if="releases.length === 0" class="py-6 text-center text-body">No releases yet.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="px-3 py-2 text-left text-label">Version</th>
                            <th class="px-3 py-2 text-left text-label">Title</th>
                            <th class="px-3 py-2 text-left text-label">Status</th>
                            <th class="px-3 py-2 text-left text-label">Release Date</th>
                            <th class="px-3 py-2 text-left text-label">Changelog</th>
                            <th class="px-3 py-2 text-left text-label">Created By</th>
                            <th class="px-3 py-2 text-right text-label">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="release in releases" :key="release.id" class="border-b border-border last:border-0">
                            <td class="px-3 py-2.5 font-medium text-text-primary">{{ release.version }}</td>
                            <td class="px-3 py-2.5 text-text-secondary">{{ release.title }}</td>
                            <td class="px-3 py-2.5">
                                <UiStatus :variant="Release.STATUS_VARIANT[release.status]" :label="Release.STATUS_LABEL[release.status]" />
                            </td>
                            <td class="px-3 py-2.5 text-text-secondary">{{ release.releaseDate ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-text-secondary">{{ release.changelogCount ?? 0 }}</td>
                            <td class="px-3 py-2.5 text-text-secondary">{{ release.createdBy?.name ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <router-link :to="`/releases/${release.id}`">
                                        <UiButton variant="ghost" size="sm">View</UiButton>
                                    </router-link>
                                    <UiButton v-if="auth.can('release_manage')" variant="ghost" size="sm" @click="openEditForm(release)">
                                        Edit
                                    </UiButton>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </UiCard>
    </div>

    <UiCard v-else>
        <h2 class="text-page-title">Access Restricted</h2>
        <p class="text-body mt-2 max-w-2xl">
            You don't have permission to view this page. Contact your administrator if you believe
            this is a mistake.
        </p>
        <router-link to="/" class="mt-4 inline-block">
            <UiButton variant="secondary">← Back to Home</UiButton>
        </router-link>
    </UiCard>
</template>
