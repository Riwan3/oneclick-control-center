<script setup>
// Temporary preview for validating the STEP 3E design-system foundation.
// Not linked in sidebar navigation. Safe to remove once reviewed.
import UiButton from '../components/ui/UiButton.vue';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiStatus from '../components/ui/UiStatus.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import UiTooltip from '../components/ui/UiTooltip.vue';

const statusVariants = [
    { variant: 'success', label: 'Online' },
    { variant: 'warning', label: 'Degraded' },
    { variant: 'danger', label: 'Offline' },
    { variant: 'info', label: 'Scheduled' },
    { variant: 'neutral', label: 'Unknown' },
];

const colorSwatches = [
    { label: 'Primary', class: 'bg-primary' },
    { label: 'Primary Hover', class: 'bg-primary-hover' },
    { label: 'Primary Soft', class: 'bg-primary-soft' },
    { label: 'Maroon', class: 'bg-maroon' },
    { label: 'Maroon Hover', class: 'bg-maroon-hover' },
    { label: 'Maroon Soft', class: 'bg-maroon-soft' },
    { label: 'Success', class: 'bg-success' },
    { label: 'Warning', class: 'bg-warning' },
    { label: 'Danger', class: 'bg-danger' },
    { label: 'Info', class: 'bg-info' },
    { label: 'Surface', class: 'bg-surface border border-border' },
    { label: 'Background', class: 'bg-background border border-border' },
    { label: 'Border', class: 'bg-border' },
    { label: 'Text Primary', class: 'bg-text-primary' },
    { label: 'Text Secondary', class: 'bg-text-secondary' },
    { label: 'Text Muted', class: 'bg-text-muted' },
];

const kpiExamples = [
    {
        title: 'Active Sites',
        value: 24,
        unit: 'sites',
        icon: 'sites',
        trend: { direction: 'up', label: '+2 this month' },
        status: 'success',
        statusLabel: 'All healthy',
    },
    {
        title: 'Avg. Response Time',
        value: 182,
        unit: 'ms',
        icon: 'monitoring',
        trend: { direction: 'down', label: '-14ms vs last week' },
        status: 'success',
        statusLabel: 'Nominal',
    },
    {
        title: 'Open Incidents',
        value: 3,
        icon: 'audit',
        trend: { direction: 'flat', label: 'No change' },
        status: 'warning',
        statusLabel: 'Needs review',
    },
];
</script>

<template>
    <div class="space-y-8">
        <UiCard>
            <h2 class="text-page-title">Design System Preview</h2>
            <p class="text-body mt-2 max-w-2xl">
                Temporary page for validating STEP 3E design tokens and UI primitives. Not part of
                the application navigation.
            </p>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Typography</h3>
            <div class="space-y-2">
                <p class="text-page-title">Page Title</p>
                <p class="text-section-title">Section Title</p>
                <p class="text-card-title">Card Title</p>
                <p class="text-body">Body text — used for descriptions and general content.</p>
                <p class="text-caption">Caption text — supporting detail.</p>
                <p class="text-label">Label text</p>
                <p class="text-kpi-value">128<span class="text-base font-medium text-text-secondary"> ms</span></p>
                <p class="text-kpi-label">KPI Label</p>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Colors</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
                <div v-for="swatch in colorSwatches" :key="swatch.label" class="text-center">
                    <div class="h-12 w-full rounded-lg" :class="swatch.class"></div>
                    <p class="text-caption mt-1.5">{{ swatch.label }}</p>
                </div>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Buttons</h3>
            <div class="flex flex-wrap items-center gap-3">
                <UiButton variant="primary">Primary</UiButton>
                <UiButton variant="secondary">Secondary</UiButton>
                <UiButton variant="ghost">Ghost</UiButton>
                <UiButton variant="danger">Danger</UiButton>
                <UiButton variant="primary" disabled>Disabled</UiButton>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Badges</h3>
            <div class="flex flex-wrap items-center gap-2">
                <UiBadge variant="success">Success</UiBadge>
                <UiBadge variant="warning">Warning</UiBadge>
                <UiBadge variant="danger">Danger</UiBadge>
                <UiBadge variant="info">Info</UiBadge>
                <UiBadge variant="neutral">Neutral</UiBadge>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Status Indicators</h3>
            <div class="flex flex-wrap items-center gap-5">
                <UiStatus
                    v-for="s in statusVariants"
                    :key="s.variant"
                    :variant="s.variant"
                    :label="s.label"
                />
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Cards</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <UiCard padding="sm">
                    <p class="text-card-title">Surface card</p>
                    <p class="text-body mt-1">Default surface, subtle card shadow.</p>
                </UiCard>
                <UiCard padding="sm" elevated>
                    <p class="text-card-title">Elevated card</p>
                    <p class="text-body mt-1">Same surface, elevated shadow tier.</p>
                </UiCard>
                <UiCard padding="sm" variant="glass">
                    <p class="text-card-title">Glass card</p>
                    <p class="text-body mt-1">Translucent, blurred, restrained glass treatment.</p>
                </UiCard>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">KPI Cards</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <UiKpiCard v-for="kpi in kpiExamples" :key="kpi.title" v-bind="kpi" />
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Tooltip</h3>
            <div class="flex items-center gap-6">
                <UiTooltip content="Tooltip above" position="top">
                    <UiButton variant="secondary">Hover me (top)</UiButton>
                </UiTooltip>
                <UiTooltip content="Tooltip to the right" position="right">
                    <UiButton variant="secondary">Hover me (right)</UiButton>
                </UiTooltip>
            </div>
        </UiCard>
    </div>
</template>
