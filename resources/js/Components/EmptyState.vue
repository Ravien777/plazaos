<script setup lang="ts">
defineProps<{
    icon?: string;
    title: string;
    message: string;
    actionLabel?: string;
    actionHref?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="mb-4 text-4xl">{{ icon ?? '📭' }}</div>
        <h3 class="text-lg font-semibold text-gray-700">{{ title }}</h3>
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ message }}</p>
        <a
            v-if="actionLabel && actionHref"
            :href="actionHref"
            class="mt-4 inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
        >
            {{ actionLabel }}
        </a>
    </div>
</template>
