<script setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '../stores/auth';
import { profileService } from '../services/authService';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UserAvatar from '../components/layout/UserAvatar.vue';

const auth = useAuthStore();

const fieldClasses =
    'w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20';

function firstError(data, fallback) {
    return Object.values(data?.errors ?? {}).flat()[0] ?? data?.message ?? fallback;
}

function statusClass(status) {
    return status.type === 'ok' ? 'text-success' : 'text-danger';
}

// Each section keeps its own status line: { type: 'ok' | 'error', text }.
const name = ref(auth.user?.name ?? '');
watch(() => auth.user?.name, (value) => { name.value = value ?? ''; });
const nameStatus = ref(null);
const nameBusy = ref(false);

async function saveName() {
    nameBusy.value = true;
    nameStatus.value = null;
    const { ok, data } = await profileService.updateName(name.value.trim());
    if (ok) {
        auth.user = data.data;
        nameStatus.value = { type: 'ok', text: 'Name updated.' };
    } else {
        nameStatus.value = { type: 'error', text: firstError(data, 'Could not update name.') };
    }
    nameBusy.value = false;
}

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const passwordStatus = ref(null);
const passwordBusy = ref(false);

async function savePassword() {
    passwordBusy.value = true;
    passwordStatus.value = null;
    const { ok, data } = await profileService.updatePassword(
        currentPassword.value,
        newPassword.value,
        confirmPassword.value
    );
    if (ok) {
        currentPassword.value = newPassword.value = confirmPassword.value = '';
        passwordStatus.value = { type: 'ok', text: 'Password updated.' };
    } else {
        passwordStatus.value = { type: 'error', text: firstError(data, 'Could not update password.') };
    }
    passwordBusy.value = false;
}

const fileInput = ref(null);
const avatarStatus = ref(null);
const avatarBusy = ref(false);

async function changeAvatar(event) {
    const file = event.target.files?.[0];
    event.target.value = '';
    if (!file) return;
    avatarBusy.value = true;
    avatarStatus.value = null;
    const { ok, data } = await profileService.uploadAvatar(file);
    if (ok) {
        auth.user = data.data;
        avatarStatus.value = { type: 'ok', text: 'Profile photo updated.' };
    } else {
        avatarStatus.value = { type: 'error', text: firstError(data, 'Could not upload photo.') };
    }
    avatarBusy.value = false;
}

async function removeAvatar() {
    avatarBusy.value = true;
    avatarStatus.value = null;
    const { ok, data } = await profileService.removeAvatar();
    if (ok) {
        auth.user = data.data;
        avatarStatus.value = { type: 'ok', text: 'Profile photo removed.' };
    }
    avatarBusy.value = false;
}
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <UiCard>
            <div class="flex flex-wrap items-center gap-6">
                <UserAvatar :src="auth.user?.avatarUrl" size="h-24 w-24" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-section-title">{{ auth.user?.name }}</p>
                    <p class="text-caption mt-1">{{ auth.roleDefinition?.label }}</p>
                    <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-1 text-sm sm:grid-cols-2">
                        <div class="flex gap-2"><dt class="text-text-muted">NRP / Username</dt><dd class="font-medium">{{ auth.user?.username }}</dd></div>
                        <div class="flex gap-2"><dt class="text-text-muted">Email</dt><dd class="truncate font-medium">{{ auth.user?.email }}</dd></div>
                        <div class="flex gap-2"><dt class="text-text-muted">Site access</dt><dd class="font-medium">{{ auth.user?.siteAccessSummary }}</dd></div>
                        <div class="flex gap-2"><dt class="text-text-muted">Module access</dt><dd class="font-medium">{{ auth.user?.moduleAccessSummary }}</dd></div>
                    </dl>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-border pt-4">
                <input ref="fileInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="changeAvatar" />
                <UiButton size="sm" :disabled="avatarBusy" @click="fileInput.click()">Change photo</UiButton>
                <UiButton v-if="auth.user?.avatarUrl" size="sm" variant="secondary" :disabled="avatarBusy" @click="removeAvatar">Remove photo</UiButton>
                <span class="text-caption">JPG, PNG or WebP, max 2 MB.</span>
                <span v-if="avatarStatus" class="text-sm" :class="statusClass(avatarStatus)">{{ avatarStatus.text }}</span>
            </div>
        </UiCard>

        <UiCard>
            <h2 class="text-section-title mb-4">Display name</h2>
            <form class="space-y-3" @submit.prevent="saveName">
                <input v-model="name" type="text" required maxlength="255" autocomplete="name" :class="fieldClasses" />
                <div class="flex items-center gap-3">
                    <UiButton type="submit" size="sm" :disabled="nameBusy || !name.trim() || name.trim() === auth.user?.name">Save name</UiButton>
                    <span v-if="nameStatus" class="text-sm" :class="statusClass(nameStatus)">{{ nameStatus.text }}</span>
                </div>
            </form>
        </UiCard>

        <UiCard>
            <h2 class="text-section-title mb-4">Change password</h2>
            <form class="space-y-3" @submit.prevent="savePassword">
                <input v-model="currentPassword" type="password" required placeholder="Current password" autocomplete="current-password" :class="fieldClasses" />
                <input v-model="newPassword" type="password" required minlength="8" placeholder="New password (min. 8 characters)" autocomplete="new-password" :class="fieldClasses" />
                <input v-model="confirmPassword" type="password" required minlength="8" placeholder="Confirm new password" autocomplete="new-password" :class="fieldClasses" />
                <div class="flex items-center gap-3">
                    <UiButton type="submit" size="sm" :disabled="passwordBusy">Update password</UiButton>
                    <span v-if="passwordStatus" class="text-sm" :class="statusClass(passwordStatus)">{{ passwordStatus.text }}</span>
                </div>
            </form>
        </UiCard>
    </div>
</template>
