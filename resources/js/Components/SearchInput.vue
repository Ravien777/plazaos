<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const localValue = ref(props.modelValue);
let debounceTimer: ReturnType<typeof setTimeout>;

watch(localValue, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        emit('update:modelValue', val);
    }, 300);
});

watch(() => props.modelValue, (val) => {
    localValue.value = val;
});
</script>

<template>
    <div class="relative">
        <input
            v-model="localValue"
            type="text"
            placeholder="Search..."
            class="w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
        />
    </div>
</template>
