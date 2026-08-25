<script setup>
import { useRoute } from 'vue-router';
import { useAuthStore } from './stores/auth';
import DefaultLayout from './layouts/DefaultLayout.vue';

const route = useRoute();
const auth = useAuthStore();
</script>

<template>
    <div v-if="!auth.initialized" class="flex min-h-screen items-center justify-center bg-background">
        <p class="text-body">Loading…</p>
    </div>
    <!-- Public routes (Login) render full-screen, without the app shell. -->
    <router-view v-else-if="route.meta.public" />
    <DefaultLayout v-else />
</template>
