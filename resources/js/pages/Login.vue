<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const username = ref('');
const password = ref('');

const fieldClasses =
    'mt-1.5 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

async function handleSubmit() {
    const success = await auth.login(username.value, password.value);
    if (success) {
        const redirectTo = typeof route.query.redirect === 'string' ? route.query.redirect : '/';
        router.push(redirectTo);
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-background px-4">
        <UiCard class="w-full max-w-md" padding="lg">
            <div class="mb-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-maroon text-lg font-bold text-white">
                    OC
                </div>
                <h1 class="text-page-title mt-4">OneClick Control Center</h1>
                <p class="text-caption mt-1">PPA Group</p>
            </div>

            <form class="space-y-4" @submit.prevent="handleSubmit">
                <div>
                    <label class="text-label" for="username">Username / NRP</label>
                    <input
                        id="username"
                        v-model="username"
                        type="text"
                        autocomplete="username"
                        required
                        :class="fieldClasses"
                    />
                </div>

                <div>
                    <label class="text-label" for="password">Password</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        :class="fieldClasses"
                    />
                </div>

                <p v-if="auth.error" class="text-sm font-medium text-danger">{{ auth.error }}</p>

                <UiButton type="submit" variant="primary" class="w-full justify-center" :disabled="auth.loading">
                    {{ auth.loading ? 'Signing in…' : 'Sign In' }}
                </UiButton>
            </form>
        </UiCard>
    </div>
</template>
