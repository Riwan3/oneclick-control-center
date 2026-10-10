<script setup>
</script>

<template>
    <main class="flex-1 overflow-y-auto bg-background">
        <div class="mx-auto max-w-7xl p-6 lg:p-8">
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
