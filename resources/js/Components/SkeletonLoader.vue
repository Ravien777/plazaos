<script setup lang="ts">
defineProps<{
    count?: number;
    height?: string;
    width?: string;
}>();
</script>

<template>
    <div v-for="i in (count ?? 1)" :key="i" class="animate-pulse rounded bg-stone-200" :style="{ height: height ?? '1rem', width: width ?? '100%' }" />
</template>
