<script setup lang="ts">
defineProps<{
    label: string;
    value: number;
    icon?: string;
}>();
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-600">{{ label }}</p>
                <span v-if="icon" class="text-gray-500">{{ icon }}</span>
            </div>
            <p class="mt-2 text-3xl font-semibold text-gray-800">{{ value }}</p>
        </div>
    </div>
</template>
