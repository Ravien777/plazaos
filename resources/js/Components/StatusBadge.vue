<script setup lang="ts">
defineProps<{
    status?: string;
}>();
</script>

<template>
    <span
        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
        :class="{
            'bg-gray-100 text-gray-700': !status || status === 'new' || status === 'archived',
            'bg-blue-100 text-blue-700': status === 'qualified' || status === 'discovery',
            'bg-yellow-100 text-yellow-700': status === 'contacted' || status === 'inactive' || status === 'development',
            'bg-green-100 text-green-700': status === 'interested' || status === 'active' || status === 'completed',
            'bg-purple-100 text-purple-700': status === 'meeting_scheduled' || status === 'design',
            'bg-indigo-100 text-indigo-600': status === 'proposal_sent' || status === 'testing',
            'bg-emerald-100 text-emerald-700': status === 'won' || status === 'launch',
            'bg-red-100 text-red-700': status === 'lost',
        }"
    >
        <slot />
    </span>
</template>
