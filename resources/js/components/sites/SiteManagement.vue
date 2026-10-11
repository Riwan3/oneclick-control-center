<script setup>
import { ref, computed } from 'vue';
import { sites, loadSites } from '../../data/siteCatalogue';
import { siteManagementService } from '../../services/siteManagementService';
import UiCard from '../ui/UiCard.vue';
import UiButton from '../ui/UiButton.vue';
import UiBadge from '../ui/UiBadge.vue';

// Super-admin site registry: add / edit / delete sites and issue the API
// token a OneClick site uses to talk to this Control Center. Mounted only
// for users with the site_manage capability (the API enforces it too).

const fieldClasses =
    'mt-1.5 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

const modeMeta = {
    push: { variant: 'info', label: 'Push' },
    pull: { variant: 'info', label: 'Pull' },
    none: { variant: 'neutral', label: 'No token' },
};

const showAdd = ref(false);
const addForm = ref({ code: '', name: '', location: '' });
const addError = ref(null);
const addSubmitting = ref(false);

const editing = ref(null);
const editForm = ref({ name: '', location: '', baseUrl: '' });
const editError = ref(null);
const editSubmitting = ref(false);

const actionError = ref(null);
const busyId = ref(null);

// Shown exactly once, right after a token is issued.
const issued = ref(null);
const copied = ref(false);

const envSnippet = computed(() =>
    issued.value
        ? Object.entries(issued.value.env)
              .map(([key, value]) => `${key}=${value}`)
              .join('\n')
        : ''
);

function errorMessage(result) {
    return Object.values(result.data?.errors ?? {}).flat()[0] ?? result.data?.message ?? 'Request failed.';
}

async function handleAdd() {
    addError.value = null;
    addSubmitting.value = true;

    const result = await siteManagementService.createSite({
        code: addForm.value.code.trim(),
        name: addForm.value.name.trim(),
        location: addForm.value.location.trim() || null,
    });

    addSubmitting.value = false;
    if (!result.ok) {
        addError.value = errorMessage(result);
        return;
    }

    addForm.value = { code: '', name: '', location: '' };
    showAdd.value = false;
    await loadSites();
}

function startEdit(site) {
    editing.value = site;
    editForm.value = { name: site.name, location: site.location === '—' ? '' : site.location, baseUrl: site.baseUrl };
    editError.value = null;
}

async function handleEdit() {
    editError.value = null;
    editSubmitting.value = true;

    const result = await siteManagementService.updateSite(editing.value.dbId, {
        name: editForm.value.name.trim(),
        location: editForm.value.location.trim() || null,
        base_url: editForm.value.baseUrl.trim() || null,
    });

    editSubmitting.value = false;
    if (!result.ok) {
        editError.value = errorMessage(result);
        return;
    }

    editing.value = null;
    await loadSites();
}

async function handleDelete(site) {
    if (!window.confirm(`Delete ${site.code}? Its access grants, configuration and snapshots are deleted too. This cannot be undone.`)) return;

    actionError.value = null;
    busyId.value = site.dbId;
    const result = await siteManagementService.deleteSite(site.dbId);
    busyId.value = null;

    if (!result.ok) {
        actionError.value = errorMessage(result);
        return;
    }
    if (issued.value?.code === site.code) issued.value = null;
    await loadSites();
}

async function handleIssueToken(site) {
    const rotating = site.mode !== 'none';
    if (rotating && !window.confirm(`Issue a new token for ${site.code}? The current token stops working immediately.`)) return;

    actionError.value = null;
    busyId.value = site.dbId;
    const result = await siteManagementService.issueToken(site.dbId);
    busyId.value = null;

    if (!result.ok) {
        actionError.value = errorMessage(result);
        return;
    }

    issued.value = { code: site.code, env: result.data.data.env };
    copied.value = false;
    await loadSites();
}

async function copySnippet() {
    try {
        await navigator.clipboard.writeText(envSnippet.value);
        copied.value = true;
    } catch {
        copied.value = false;
    }
}
</script>

<template>
    <UiCard>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-card-title">Manage Sites</h3>
                <p class="text-caption mt-1 max-w-2xl">
                    Add the sites you run and connect each one with an API token. A site reports to this Control
                    Center (push) or this Control Center calls the site (pull, when a URL is set).
                </p>
            </div>
            <UiButton @click="showAdd = !showAdd">{{ showAdd ? 'Cancel' : 'Add Site' }}</UiButton>
        </div>

        <form v-if="showAdd" class="mt-5 space-y-4 border-t border-border pt-5" @submit.prevent="handleAdd">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="text-label" for="site-code">Code</label>
                    <input id="site-code" v-model="addForm.code" type="text" required maxlength="20" placeholder="e.g. SKS" :class="fieldClasses" />
                </div>
                <div>
                    <label class="text-label" for="site-name">Name</label>
                    <input id="site-name" v-model="addForm.name" type="text" required :class="fieldClasses" />
                </div>
                <div>
                    <label class="text-label" for="site-location">Location</label>
                    <input id="site-location" v-model="addForm.location" type="text" :class="fieldClasses" />
                </div>
            </div>
            <p v-if="addError" class="text-sm font-medium text-danger">{{ addError }}</p>
            <UiButton type="submit" :disabled="addSubmitting">{{ addSubmitting ? 'Adding…' : 'Add Site' }}</UiButton>
        </form>

        <form v-if="editing" class="mt-5 space-y-4 border-t border-border pt-5" @submit.prevent="handleEdit">
            <p class="text-sm font-semibold text-text-primary">Edit {{ editing.code }}</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="text-label" for="edit-site-name">Name</label>
                    <input id="edit-site-name" v-model="editForm.name" type="text" required :class="fieldClasses" />
                </div>
                <div>
                    <label class="text-label" for="edit-site-location">Location</label>
                    <input id="edit-site-location" v-model="editForm.location" type="text" :class="fieldClasses" />
                </div>
                <div>
                    <label class="text-label" for="edit-site-url">Site URL (pull mode only)</label>
                    <input id="edit-site-url" v-model="editForm.baseUrl" type="url" placeholder="Leave empty for push mode" :class="fieldClasses" />
                </div>
            </div>
            <p v-if="editError" class="text-sm font-medium text-danger">{{ editError }}</p>
            <div class="flex gap-2">
                <UiButton type="submit" :disabled="editSubmitting">{{ editSubmitting ? 'Saving…' : 'Save' }}</UiButton>
                <UiButton variant="ghost" @click="editing = null">Cancel</UiButton>
            </div>
        </form>

        <div v-if="issued" class="mt-5 space-y-3 rounded-lg border border-warning/50 bg-warning-soft p-4">
            <p class="text-sm font-semibold text-text-primary">Token for {{ issued.code }} — shown only once</p>
            <p class="text-caption">
                Put these lines in the site's <span class="font-medium">.env</span>, then restart it and run
                <span class="font-medium">php artisan cc:push</span> once to check. Copy it now: it cannot be shown
                again, only replaced by a new token.
            </p>
            <pre class="overflow-x-auto rounded-md bg-surface p-3 text-xs text-text-primary">{{ envSnippet }}</pre>
            <div class="flex gap-2">
                <UiButton size="sm" @click="copySnippet">{{ copied ? 'Copied' : 'Copy' }}</UiButton>
                <UiButton size="sm" variant="ghost" @click="issued = null">I have saved it</UiButton>
            </div>
        </div>

        <p v-if="actionError" class="mt-4 text-sm font-medium text-danger">{{ actionError }}</p>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="px-3 py-2 text-left text-label">Site</th>
                        <th class="px-3 py-2 text-left text-label">Location</th>
                        <th class="px-3 py-2 text-left text-label">Connection</th>
                        <th class="px-3 py-2 text-right text-label">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="site in sites" :key="site.dbId" class="border-b border-border last:border-0">
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-text-primary">{{ site.code }}</p>
                            <p class="text-caption">{{ site.name }}</p>
                        </td>
                        <td class="px-3 py-2.5 text-text-secondary">{{ site.location }}</td>
                        <td class="px-3 py-2.5">
                            <UiBadge :variant="modeMeta[site.mode]?.variant">{{ modeMeta[site.mode]?.label }}</UiBadge>
                        </td>
                        <td class="px-3 py-2.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <UiButton variant="secondary" size="sm" :disabled="busyId === site.dbId" @click="handleIssueToken(site)">
                                    {{ site.mode === 'none' ? 'Connect' : 'New token' }}
                                </UiButton>
                                <UiButton variant="ghost" size="sm" @click="startEdit(site)">Edit</UiButton>
                                <UiButton variant="danger" size="sm" :disabled="busyId === site.dbId" @click="handleDelete(site)">
                                    Delete
                                </UiButton>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </UiCard>
</template>
