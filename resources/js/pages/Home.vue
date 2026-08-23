<script setup>
</script>

<template>
    <div class="min-h-screen bg-blue-50 flex items-center justify-center">
        <div class="rounded-2xl bg-white p-10 shadow-xl border border-blue-100">
            <h1 class="text-4xl font-bold text-blue-700">
                OneClick Control Center
            </h1>

            <p class="mt-4 text-gray-600">
                Vue 3 + Tailwind CSS berhasil terintegrasi dengan Laravel.
            </p>

            <router-link
                to="/dashboard"
                class="mt-6 inline-block text-blue-600 underline"
            >
                Go to Dashboard
            </router-link>
        </div>
    </div>
</template>
