<script setup>
</script>

<template>
    <main class="flex-1 overflow-y-auto bg-slate-50">
        <div class="mx-auto max-w-7xl p-6 lg:p-8">
            <router-view v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </div>
    </main>
</template>
