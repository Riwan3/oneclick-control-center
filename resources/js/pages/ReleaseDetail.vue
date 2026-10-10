<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useReleaseDetail } from '../composables/useReleaseDetail';
import { Release, Changelog } from '../config/releaseOptions';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiStatus from '../components/ui/UiStatus.vue';
import UiBadge from '../components/ui/UiBadge.vue';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const auth = useAuthStore();
const { release, loading, error, notFound, fetchRelease, createChangelog, updateChangelog, deleteChangelog } =
    useReleaseDetail();

const fieldClasses =
    'mt-1.5 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

function blankChangelogForm() {
    return { category: 'new', title: '', description: '', position: 0 };
}

const showCreateForm = ref(false);
const formError = ref(null);
const submitting = ref(false);
const form = ref(blankChangelogForm());

const editingChangelog = ref(null);
const editFormError = ref(null);
const editSubmitting = ref(false);
const editForm = ref(blankChangelogForm());

const deletingId = ref(null);
const actionError = ref(null);

function openCreateForm() {
    editingChangelog.value = null;
    showCreateForm.value = !showCreateForm.value;
    if (showCreateForm.value) {
        form.value = blankChangelogForm();
        formError.value = null;
    }
}

async function handleCreate() {
    submitting.value = true;
    formError.value = null;

    const result = await createChangelog(props.id, form.value);
    if (result.success) {
        showCreateForm.value = false;
    } else {
        formError.value = Object.values(result.errors ?? {}).flat()[0] ?? result.message;
    }

    submitting.value = false;
}

function openEditForm(changelog) {
    showCreateForm.value = false;
    editingChangelog.value = changelog;
    editForm.value = {
        category: changelog.category,
        title: changelog.title,
        description: changelog.description ?? '',
        position: changelog.position ?? 0,
    };
    editFormError.value = null;
}

function closeEditForm() {
    editingChangelog.value = null;
}

async function handleUpdate() {
    editSubmitting.value = true;
    editFormError.value = null;

    const result = await updateChangelog(props.id, editingChangelog.value.id, editForm.value);
    if (result.success) {
        editingChangelog.value = null;
    } else {
        editFormError.value = Object.values(result.errors ?? {}).flat()[0] ?? result.message;
    }

    editSubmitting.value = false;
}

async function handleDelete(changelog) {
    if (!window.confirm(`Delete this changelog entry ("${changelog.title}")? This cannot be undone.`)) return;

    actionError.value = null;
    deletingId.value = changelog.id;

    const result = await deleteChangelog(props.id, changelog.id);
    if (!result.success) {
        actionError.value = result.message;
    }

    deletingId.value = null;
}

onMounted(() => fetchRelease(props.id));
</script>

<template>
    <div v-if="auth.can('version_view')" class="space-y-8">
        <router-link to="/releases" class="text-sm font-medium text-primary hover:text-primary-hover">
            ← Back to Releases
        </router-link>

        <p v-if="loading" class="text-caption">Loading release…</p>

        <UiCard v-else-if="notFound || (!release && error)">
            <h2 class="text-page-title">Release not found</h2>
            <p class="text-body mt-2 max-w-2xl">
                We couldn't find a release matching this id. It may have been removed or the link is
                incorrect.
            </p>
        </UiCard>

        <template v-else-if="release">
            <UiCard>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-label">Release</p>
                        <h1 class="text-page-title mt-1">{{ release.version }}</h1>
                        <p class="text-body mt-1">{{ release.title }}</p>
                    </div>
                    <UiStatus :variant="Release.STATUS_VARIANT[release.status]" :label="Release.STATUS_LABEL[release.status]" />
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-caption">Release Date</p>
                        <p class="mt-1 text-text-primary">{{ release.releaseDate ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-caption">Created By</p>
                        <p class="mt-1 text-text-primary">{{ release.createdBy?.name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-caption">Changelog Entries</p>
                        <p class="mt-1 text-text-primary">{{ release.changelogs?.length ?? 0 }}</p>
                    </div>
                </div>

                <div v-if="release.description" class="mt-6 border-t border-border pt-4">
                    <p class="text-caption mb-1">Description</p>
                    <p class="text-body">{{ release.description }}</p>
                </div>
            </UiCard>

            <div class="flex flex-wrap items-start justify-between gap-4">
                <h2 class="text-section-title">Changelog</h2>
                <UiButton v-if="auth.can('release_manage')" size="sm" @click="openCreateForm">
                    {{ showCreateForm ? 'Cancel' : 'Add Entry' }}
                </UiButton>
            </div>

            <UiCard v-if="showCreateForm">
                <h3 class="text-card-title mb-4">New Changelog Entry</h3>
                <form class="space-y-4" @submit.prevent="handleCreate">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-label" for="new-cl-category">Category</label>
                            <select id="new-cl-category" v-model="form.category" :class="fieldClasses">
                                <option v-for="c in Changelog.CATEGORIES" :key="c" :value="c">{{ Changelog.CATEGORY_LABEL[c] }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-label" for="new-cl-title">Title</label>
                            <input id="new-cl-title" v-model="form.title" type="text" required :class="fieldClasses" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-label" for="new-cl-description">Description</label>
                            <textarea id="new-cl-description" v-model="form.description" rows="2" :class="fieldClasses"></textarea>
                        </div>
                    </div>

                    <p v-if="formError" class="text-sm font-medium text-danger">{{ formError }}</p>

                    <UiButton type="submit" variant="primary" :disabled="submitting">
                        {{ submitting ? 'Adding…' : 'Add Entry' }}
                    </UiButton>
                </form>
            </UiCard>

            <UiCard v-if="editingChangelog">
                <h3 class="text-card-title mb-4">Edit Changelog Entry</h3>
                <form class="space-y-4" @submit.prevent="handleUpdate">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-label" for="edit-cl-category">Category</label>
                            <select id="edit-cl-category" v-model="editForm.category" :class="fieldClasses">
                                <option v-for="c in Changelog.CATEGORIES" :key="c" :value="c">{{ Changelog.CATEGORY_LABEL[c] }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-label" for="edit-cl-title">Title</label>
                            <input id="edit-cl-title" v-model="editForm.title" type="text" required :class="fieldClasses" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-label" for="edit-cl-description">Description</label>
                            <textarea id="edit-cl-description" v-model="editForm.description" rows="2" :class="fieldClasses"></textarea>
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
                <p v-if="actionError" class="mb-3 text-sm font-medium text-danger">{{ actionError }}</p>

                <p v-if="!release.changelogs?.length" class="py-6 text-center text-body">No changelog entries yet.</p>

                <ul v-else class="divide-y divide-border">
                    <li v-for="entry in release.changelogs" :key="entry.id" class="flex items-start justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <UiBadge :variant="Changelog.CATEGORY_VARIANT[entry.category]">
                                    {{ Changelog.CATEGORY_LABEL[entry.category] }}
                                </UiBadge>
                                <p class="font-medium text-text-primary">{{ entry.title }}</p>
                            </div>
                            <p v-if="entry.description" class="text-caption mt-1">{{ entry.description }}</p>
                        </div>

                        <div v-if="auth.can('release_manage')" class="flex shrink-0 items-center gap-2">
                            <UiButton variant="ghost" size="sm" @click="openEditForm(entry)">Edit</UiButton>
                            <UiButton
                                variant="danger"
                                size="sm"
                                :disabled="deletingId === entry.id"
                                @click="handleDelete(entry)"
                            >
                                {{ deletingId === entry.id ? 'Deleting…' : 'Delete' }}
                            </UiButton>
                        </div>
                    </li>
                </ul>
            </UiCard>
        </template>
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
