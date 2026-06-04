<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">
                {{ title }}
            </h2>
            <p v-if="description" class="mt-1 text-sm text-gray-600">
                {{ description }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
