<script setup>
defineProps({
    name: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="h-5 w-5"
    >
        <template v-if="name === 'home'">
            <path d="M3 11.5 12 4l9 7.5" />
            <path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9" />
        </template>

        <template v-else-if="name === 'dashboard'">
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.5" />
        </template>

        <template v-else-if="name === 'sites'">
            <rect x="3.5" y="4" width="17" height="6" rx="1.5" />
            <rect x="3.5" y="14" width="17" height="6" rx="1.5" />
            <path d="M7 7h.01M7 17h.01" />
        </template>

        <template v-else-if="name === 'monitoring'">
            <path d="M3 17.5 8.5 12l4 4L21 7" />
            <path d="M15 7h6v6" />
        </template>

        <template v-else-if="name === 'audit'">
            <rect x="5.5" y="3.5" width="13" height="17" rx="2" />
            <path d="M9 3.5V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v.5" />
            <path d="M8.5 10.5h7M8.5 14h7M8.5 17.5h4" />
        </template>

        <template v-else-if="name === 'menu'">
            <path d="M4 6h16M4 12h16M4 18h16" />
        </template>

        <template v-else-if="name === 'chevron-left'">
            <path d="M15 5l-7 7 7 7" />
        </template>

        <template v-else-if="name === 'chevron-down'">
            <path d="M6 9l6 6 6-6" />
        </template>

        <template v-else-if="name === 'bell'">
            <path d="M6 8a6 6 0 1 1 12 0c0 3 1 5 2 6H4c1-1 2-3 2-6Z" />
            <path d="M10 20a2 2 0 0 0 4 0" />
        </template>

        <template v-else-if="name === 'user'">
            <circle cx="12" cy="8" r="3.25" />
            <path d="M4.75 19.25a7.25 7.25 0 0 1 14.5 0" />
        </template>

        <template v-else-if="name === 'trend-up'">
            <path d="M4 16l5-5 4 4 7-8" />
            <path d="M15 7h5v5" />
        </template>

        <template v-else-if="name === 'trend-down'">
            <path d="M4 8l5 5 4-4 7 8" />
            <path d="M15 17h5v-5" />
        </template>

        <template v-else-if="name === 'trend-flat'">
            <path d="M4 12h16" />
        </template>

        <template v-else-if="name === 'briefcase'">
            <rect x="3.5" y="7.5" width="17" height="12" rx="2" />
            <path d="M8.5 7.5V6a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v1.5" />
            <path d="M3.5 12.5h17" />
        </template>

        <template v-else-if="name === 'truck'">
            <rect x="2.5" y="9" width="10" height="7" rx="1" />
            <path d="M12.5 11.5h4l3 3v1.5h-7z" />
            <circle cx="6" cy="18" r="1.5" />
            <circle cx="16.5" cy="18" r="1.5" />
        </template>

        <template v-else-if="name === 'folder'">
            <path d="M3.5 7a1.5 1.5 0 0 1 1.5-1.5h4l2 2h8A1.5 1.5 0 0 1 20.5 9v8A1.5 1.5 0 0 1 19 18.5H5A1.5 1.5 0 0 1 3.5 17V7Z" />
        </template>

        <template v-else-if="name === 'history'">
            <circle cx="12" cy="13" r="7.5" />
            <path d="M12 9v4l3 2" />
        </template>

        <template v-else-if="name === 'tag'">
            <path d="M3.5 11.5 12 3h6a1.5 1.5 0 0 1 1.5 1.5v6l-8.5 8.5a1.5 1.5 0 0 1-2.1 0L3.5 13.6a1.5 1.5 0 0 1 0-2.1Z" />
            <circle cx="15.5" cy="8.5" r="1.25" fill="currentColor" stroke="none" />
        </template>

        <template v-else-if="name === 'layers'">
            <path d="M12 3.5 3.5 8 12 12.5 20.5 8Z" />
            <path d="M3.5 12 12 16.5 20.5 12" />
            <path d="M3.5 16 12 20.5 20.5 16" />
        </template>
    </svg>
</template>
