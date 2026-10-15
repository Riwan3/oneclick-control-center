<script setup>
</script>

<template>
    <main class="min-w-0 flex-1 overflow-y-auto bg-background pb-[env(safe-area-inset-bottom)]">
        <div class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
            <router-view v-slot="{ Component, route }">
                <transition name="fade" mode="out-in">
                    <div :key="route.path">
                        <component :is="Component" />
                    </div>
                </transition>
            </router-view>
        </div>
    </main>
</template>
