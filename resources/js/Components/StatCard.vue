<script setup lang="ts">
defineProps<{
    label: string;
    value: number;
    icon?: string;
    removable?: boolean;
}>();

defineEmits<{
    (e: 'remove'): void;
}>();
</script>

<template>
    <div class="group relative overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-600">{{ label }}</p>
                <span v-if="icon" class="text-gray-500">{{ icon }}</span>
            </div>
            <p class="mt-2 text-3xl font-semibold text-gray-800">{{ value }}</p>
        </div>
        <button
            v-if="removable"
            type="button"
            class="absolute right-2 top-2 text-gray-300 opacity-0 transition hover:text-red-500 group-hover:opacity-100"
            @click="$emit('remove')"
            title="Remove widget"
        >
            ✕
        </button>
    </div>
</template>
