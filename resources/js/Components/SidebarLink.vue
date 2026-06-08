<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
    collapsed?: boolean;
    title?: string;
}>();

const classes = computed(() =>
    props.active
        ? 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 transition-colors'
        : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors',
);

const collapsedClasses = computed(() =>
    props.active
        ? 'flex items-center justify-center rounded-lg p-2 text-indigo-600 bg-indigo-50 transition-colors'
        : 'flex items-center justify-center rounded-lg p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors',
);
</script>

<template>
    <Link
        v-if="!collapsed"
        :href="href"
        :class="classes"
    >
        <span class="shrink-0">
            <slot name="icon" />
        </span>
        <span class="truncate"><slot /></span>
    </Link>
    <Link
        v-else
        :href="href"
        :class="collapsedClasses"
        :title="title ?? ''"
    >
        <span>
            <slot name="icon" />
        </span>
    </Link>
</template>
