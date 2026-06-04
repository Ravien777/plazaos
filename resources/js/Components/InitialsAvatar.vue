<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    name: string;
    size?: string;
}>();

const initials = computed(() => {
    const parts = props.name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return props.name.slice(0, 2).toUpperCase();
});

const gradients = [
    'from-pink-400 to-rose-500',
    'from-purple-400 to-indigo-500',
    'from-indigo-400 to-blue-500',
    'from-blue-400 to-cyan-500',
    'from-teal-400 to-emerald-500',
    'from-green-400 to-lime-500',
    'from-yellow-400 to-amber-500',
    'from-orange-400 to-red-500',
    'from-red-400 to-pink-500',
    'from-rose-400 to-purple-500',
];

const gradient = computed(() => {
    let hash = 0;
    for (let i = 0; i < props.name.length; i++) {
        hash = props.name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return gradients[Math.abs(hash) % gradients.length];
});
</script>

<template>
    <span
        class="inline-flex items-center justify-center rounded-full bg-gradient-to-br text-sm font-semibold text-white"
        :class="[gradient, size ?? 'h-8 w-8']"
    >
        {{ initials }}
    </span>
</template>
